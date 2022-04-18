<?php

namespace App\Repository\Eloquent;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\EmployeeLeave;
use App\Repository\Interfaces\EmployeeLeaveRepositoryInterface;
use App\Repository\Interfaces\EmployeeRepositoryInterface;
use App\Repository\Interfaces\LeaveRepositoryInterface;
use App\Validators\InputsValidator;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use JetBrains\PhpStorm\ArrayShape;

class EmployeeLeaveRepository extends BaseRepository implements EmployeeLeaveRepositoryInterface
{
    //inputs validation object
    private InputsValidator $inputsValidator;

    //Employee and Leave Repo instances
    private EmployeeRepositoryInterface $employeeRepository;
    private LeaveRepositoryInterface $leaveRepository;

    /**
     * EmployeeLeaveRepository constructor.
     *
     * @param Employee $model
     * @param InputsValidator $inputsValidator
     * @param EmployeeRepositoryInterface $employeeRepository
     * @param LeaveRepositoryInterface $leaveRepository
     */
    public function __construct(
        EmployeeLeave $model,
        InputsValidator $inputsValidator,
        EmployeeRepositoryInterface $employeeRepository,
        LeaveRepositoryInterface $leaveRepository)
    {
        parent::__construct($model);
        $this->inputsValidator = $inputsValidator;
        $this->employeeRepository=$employeeRepository;
        $this->leaveRepository =$leaveRepository;
    }


    /*
     * Validates and creates an employee specific leave request
     * Accepts: Array of
     * 1- Employee ID (employee_id) int
     * 2- Leave ID (leave_id) int
     * 3- Starting date (starting_date) date
     * 4- Ending date (ending_date) date
     * 5- Duration int
     * Returns: none
     */
    public function requestLeave (array $attributes)
    {
        try{
            $validationFields = [
                'employee_id',
                'leave_id',
                'starting_date',
                'ending_date',
                'duration',
            ];

            //Validate input parameters
            $this->inputsValidator->validateUserInputs(
                $attributes = Arr::only($attributes,$validationFields),
                Arr::Only($this->allModelValidationCriteria,$validationFields)
            );

            $user = Auth::user();

            $employee = $this->employeeRepository->find($attributes['employee_id']);

            if(!(Auth::User()->id===$attributes['employee_id'] || Auth::User()->is_admin)){
                throw new \Exception('user has no permissions to apply for a leave for the selected employee');
            }

            $leave = $this->leaveRepository->find($attributes['leave_id']);

            if(is_null($employee) || is_null($leave)){
                throw new \Exception('employee or leave not found');
            }

            //Check if employee is eligible for selected leave
            self::checkLeaveEligibility($employee,$leave,$attributes['duration'],$this->leaveRepository);

            //create leave request for further action
            EmployeeLeave::create([
                'employee_id'=>$employee->id,
                'leave_id'=>$leave->id,
                'duration'=>$attributes['duration'],
                'starting_date'=>$attributes['starting_date'],
                'ending_date'=>$attributes['ending_date'],
                'pay_percentage'=>$leave->pay_percentage,
                'status'=> is_null($employee->reporting_to)? 'self_approved' : 'requested',
                'requested_by'=>Auth::user()->id,
                'actioned_by'=>null,
            ]);
        }catch (\Exception $ex){
            throw new \Exception($ex->getMessage());
        }
    }

    /*
     * Check if employee is eligible for a specific duration of a specific leave type
     * Accepts:
     * 1- Employee - employee Model
     * 2- Leave - leave Model
     * 3- Duration int
     * Returns: boolean (true if eligible)
     */
    public function checkLeaveEligibility(Employee $employee, Leave $leave, int $duration):bool
    {
        if($leave->getAttribute('fallback_leave')){
            return true;
        }

        if (!is_null($leave->isGenderStrict()) && $leave->getAttribute('gender_strict') != $employee->getAttribute('gender')){
            throw new \Exception('gender stricted leave - employee dose not meet leave criteria');
        }

        $balance = self::calculateLeaveBalance($employee,$leave);

        if($leave->getAttribute('days_allowed_after') > $balance['service_period_in_days']){
            throw new \Exception('employee did not meet the minimum service period required for leave eligibility');
        }

        if(!is_null($leave->getAttribute('leave_allowed_after'))){
            $prerequisiteLeave = $this->leaveRepository->getByID($leave->getAttribute('leave_allowed_after'));
            $prerequisiteLeaveBalance = self::calculateLeaveBalance($employee,$prerequisiteLeave);
            if($prerequisiteLeaveBalance['available_balance']> 0){
                throw new \Exception('prerequisite leave balance is not fully consumed');
            }
        }

        if($leave->accumulatable() || $leave->isDividable()){
            if($balance['available_balance']<=0 || $balance['available_balance'] < $duration){
                throw new \Exception('insufficient leave balance');
            }
        }else{
            if(count($balance['previous_leaves']) >= $leave->getAttribute('allowed_blocks_per_period')){
                throw new \Exception('all allowed leaves are already consumed');
            }
        }
        return true;
    }

    /*
     * Change the status of an Employee's requested or an approved leave to withdrawn
     * Accepts:
     * 1- employee_leave_id (employee_leave_id) - int
     * Returns: boolean (true if withdrawn)
     */
    public function withdrawLeave (array $attributes):bool
    {
        $validationFields = [
            'employee_leave_id',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $query = EmployeeLeave::where('id',$attributes['employee_leave_id']);
        if(!Auth::user()->is_admin) { //Only Admin users or the employee who is applying himself
            $query->where('employee_id', Auth::user()->id);
        }
        $affectedRowsCount = $query->whereIn('status',['requested','approved'])
            ->update([
            'status'=>'withdrawn',
            'actioned_by'=>Auth::user()->id
        ]);

        if($affectedRowsCount!=1){
            throw new \Exception('no leave to withdraw');
        }
        return true;
    }

    public function getManagerLeaveRequests(array $attributes)
    {
        $validationFields = [
            'manager_id',
            'status',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $employees = Employee::select('id')
            ->where('reporting_to',$attributes['manager_id'])
            ->get();

        $query = EmployeeLeave::whereIn('employee_id',$employees->pluck('id'));
        if($attributes['status'] != 'all'){
            $query = $query->where('status',$attributes['status']);
        }
        return $query->get();
    }

    /*
     * Change the status of an Employee's requested leave by the direct manager to approved or rejected
     * Accepts: Array of
     * 1- Employee's Requested Leave ID (employee_leave_id) - int
     * 2- action string - possible values: approved or rejected
     * Returns: boolean (true if status changed)
     */
    public function actionLeave (array $attributes):bool
    {
        $validationFields = [
            'employee_leave_id',
            'action',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );
        $employeeLeave = EmployeeLeave::where('id',$attributes['employee_leave_id'])
            ->where('status','requested')
            ->first();

        if(!$employeeLeave){
            throw new \Exception('leave not found');
        }

        $employee = $this->employeeRepository->getByID($employeeLeave->employee_id);

        if($employee->getAttribute('reporting_to') != Auth::user()->id){
            throw new \Exception('no enough privileges to action leave');
        }

        $employeeLeave->status=$attributes['action'];
        $employeeLeave->actioned_by=Auth::user()->id;
        $employeeLeave->save();

        return true;
    }

    /*
     * Build an array with information of a specific employee's balance of a specific leave type
     * Accepts: Array of
     * 1- Employee ID (employee_id) - int
     * 2- Leave ID (leave_id) int
     * Returns: array
     */
    public function checkLeaveBalance(array $attributes):array
    {

        $validationFields = [
            'employee_id',
            'leave_id'
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $inputs = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $employee = $this->employeeRepository->getByID($attributes['employee_id']);

        $leave = $this->leaveRepository->getByID($attributes['leave_id']);

        return self::calculateLeaveBalance($employee,$leave);

    }


    public function updateLeaveStatus()
    {
         return $this->model->where('starting_date', '<=', Carbon::now()->startOfDay())
            ->whereIn('status',['approved','self_approved'])
            ->update(['status'=>'consumed']);
    }

    private function calculateLeaveBalance(Employee $employee, Leave $leave):array
    {
        //check if leave is gender strict, and if yes, is applying employee's gender matching
        if (!is_null($leave->isGenderStrict()) && $leave->getAttribute('gender_strict') != $employee->getAttribute('gender')){
            throw new \Exception('gender strict leave - employee dose not meet leave criteria');
        }

        //get leave calculation period start and end as per the leave type definition
        $LeaveCurrentCalculationPeriod = self::getLeaveCalculationPeriod($employee,$leave);

        //calculate earned balance
        $earnedBalance =self::calculateEarnedBalance($employee, $leave, $LeaveCurrentCalculationPeriod['duration']);

        //calculate this leave type consumed balance
        $previous_leaves = self::getLeaveConsumedRecordsAndBalance($employee->getAttribute('id'), $leave->getAttribute('id'), $LeaveCurrentCalculationPeriod);


        //return the employee/leave balance details Object
        return [
            'employee_id'=> $employee->getAttribute('id'),
            'employee_name'=> $employee->getAttribute('name'),
            'job_title'=>$employee->getAttribute('job_title'),
            'department_id'=> $employee->getAttribute('department_id'),
            'service_period_in_days'=> self::getFullServiceDuration($employee),
            'leave_id'=> $leave->getAttribute('id'),
            'leave_name'=>$leave->getAttribute('name'),
            'consumed_balance'=> $previous_leaves['totalConsumedBalance'],
            'earned_balance'=> $earnedBalance,
            'available_balance'=> ($earnedBalance - $previous_leaves['totalConsumedBalance']) >= 0 ? $earnedBalance - $previous_leaves['totalConsumedBalance'] : 0,
            'previous_leaves'=> $previous_leaves['leavesRecords']
        ];
    }

    #[ArrayShape(['leavesRecords' => "Illuminate\\Support\\Collection", 'totalConsumedBalance' => "int"])]
    private function getLeaveConsumedRecordsAndBalance(int $employee_id, int $leave_id, array $currentPeriod):array
    {
        $totalConsumedBalance = 0; // initiate
        $leaves = EmployeeLeave::where('employee_id',$employee_id)
            ->where('leave_id',$leave_id)
            ->where('starting_date', '>=',Carbon::parse($currentPeriod['period_start'])->toDateString())
            ->where('ending_date', '<=',Carbon::parse($currentPeriod['period_end'])->toDateString())
            ->whereIn('status',['approved','consumed'])
            ->get();
        foreach($leaves as $leave){
            $totalConsumedBalance += $leave->duration;
        }
        return ['leavesRecords'=>$leaves,'totalConsumedBalance'=>$totalConsumedBalance];
    }

    #[ArrayShape(['period_start' => "\Carbon\Carbon", 'period_end' => "\Carbon\Carbon", 'duration' => "int"])]
    private function getLeaveCalculationPeriod(Employee $employee,Leave $leave):array
    {
        //get leave calculation period start and end as per the leave type definition
        if($leave->accumulatable() || is_null($leave->getAttribute('calculation_period')))
        {
            $currentPeriod = self::getCurrentPeriod($employee, null);
        }else{
            $currentPeriod = self::getCurrentPeriod($employee, $leave->getAttribute('calculation_period'));
        }
        return $currentPeriod;
    }

    private function calculateEarnedBalance(Employee $employee, Leave $leave, int $period):int
    {
        if(self::getFullServiceDuration($employee) >= $leave->getAttribute('days_allowed_after')){
            if($leave->accumulatable()){
                $balance = ($leave->getAttribute('default_block_duration_in_days') / $leave->getAttribute('calculation_period')) * $period;
                return floor($balance);
            }else{
                return $leave->duration();
            }
        }else{
            return 0;
        }
    }

    #[ArrayShape(['period_start' => "\Carbon\Carbon", 'period_end' => "\Carbon\Carbon", 'duration' => "int"])]
    private function getCurrentPeriod($employee,$calculationPeriodInDays):array
    {
        $joiningDate = Carbon::parse($employee->joining_date);
        $now = Carbon::now();

        if(is_null($calculationPeriodInDays)){
            $periodStart = $joiningDate;
            $periodEnd = is_null($employee->last_working_date) ? $now :  Carbon::parse($employee->last_working_date);
        }else{
            $diff = $joiningDate->diffInDays($now);
            $fullPeriodsOfService = floor($diff / $calculationPeriodInDays);
            $periodStart = $joiningDate->addDays($fullPeriodsOfService * $calculationPeriodInDays);
            $periodEnd = Carbon::parse($periodStart->toDateString())->addDays($calculationPeriodInDays);
        }
        return ['period_start' => $periodStart, 'period_end' => $periodEnd, 'duration' => $periodStart->diffInDays($periodEnd)];
    }

    private function getFullServiceDuration(Employee $employee):int
    {
        $toDate= is_null($employee->last_working_date) ? Carbon::now() : Carbon::parse($employee->last_working_date);
        return Carbon::parse($employee->joining_date)->diffInDays($toDate);
    }

    private array $allModelValidationCriteria= [
        'name' => 'required|string|min:3|max:255',
        'country_code'=>'required_with:mobile|string|max:4',
        'mobile' => 'required|string|max:16',
        'job_title' => 'required|string|max:100',
        'country_id'=> 'sometimes|integer',
        'gender'=> 'sometimes|boolean',
        'email'=>'required|email|unique:employees',
        'is_active'=>'prohibited',
        'joining_date'=>'required|date',
        'last_working_date'=>'nullable|date|after:joining_date',
        'department_id'=>'required|integer|exists:departments,id',
        'reporting_to'=>'nullable|integer|exists:employees,id',
        'salary'=>'required|numeric',
        'leaves_history'=>'nullable|json',
        'avatar'=>'nullable|string',
        'self_service'=>'sometimes|boolean',
        'self_service_email'=>'nullable|email',
        'is_admin'=>'sometimes|boolean',
        'id'=>'required|integer|exists:employees,id',
        'employee_id' => 'required|integer|exists:employees,id',
        'new_department_id' => 'required|integer|exists:departments,id',
        'leave_id' => 'required|integer|exists:leaves,id',
        'starting_date' => 'required|date|after_or_equal:today',
        'ending_date' => 'required|date|after:start_date',
        'duration'=> 'required|integer|min:0',
        'employee_leave_id' => 'required|integer|exists:employee_leaves,id',
        'action' => 'required|string|in:approved,rejected',
        'reason' => 'required|string|in:resignation,termination,immediate_termination',
        'new_salary' => 'required|numeric',
        'status'=>'required|in:all,requested,approved,rejected,withdrawn,consumed,self_approved',
        'currentPeriod.*.period_start' => 'required|date',
        'currentPeriod.*.period_end' => 'required|date|after_or_equal:start_date',
    ];
}
