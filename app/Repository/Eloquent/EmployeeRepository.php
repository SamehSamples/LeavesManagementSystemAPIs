<?php

namespace App\Repository\Eloquent;

use App\Http\Helpers\GeneralHelper;
use App\Http\Resources\EmployeeLeaveRequestResource;
use App\Models\Employee;
use App\Models\EmployeeTransactionLog;
use App\Notifications\UserPasswordNotification;
use App\Repository\Interfaces\EmployeeRepositoryInterface;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Validators\InputsValidator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface
{
    private InputsValidator $inputsValidator;
    protected $model;
    private UserRepositoryInterface $userRepository;
    private GeneralHelper $helper;

    /**
     * UserRepository constructor.
     *
     * @param Employee $model
     * @param InputsValidator $inputsValidator
     * @param UserRepositoryInterface $userRepository
     * @param GeneralHelper $helper
     */
    public function __construct(Employee $model,
                                InputsValidator $inputsValidator,
                                UserRepositoryInterface $userRepository,
                                GeneralHelper $helper)
    {
        parent::__construct($model);
        $this->inputsValidator = $inputsValidator;
        $this->userRepository=$userRepository;
        $this->model=$model;
        $this->helper= $helper;
    }


    public function index(bool $activeOnly= true): Collection
    {
        $query =  $this->model::orderBy('id');
        if($activeOnly){
            $query = $query->whereNull('last_working_date');
        }
        return $query->get();
    }

    public function getByID(int $id, bool $activeOnly = true):?Employee
    {
        $query =  $this->model::where('id', $id);
        if($activeOnly){
            $query = $query->where('last_working_date');
        }
        return $query->first();
    }

    public function create(array $attributes):Employee
    {
        $validationFields = [
            'name',
            'country_code',
            'mobile',
            'job_title',
            'country_id',
            'gender',
            'password',
            'email',
            'is_active',
            'joining_date',
            'department_id',
            'reporting_to',
            'salary',
            'avatar',
            'self_service',
            'self_service_email',
            'is_admin'
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        DB::beginTransaction();

        $employee =$this->model->create($attributes);

        self::logTransaction([
            'transaction_type'=>'create_employee',
            'by_user_id'=>Auth::user()->id,
            'employee_id'=>$employee->id,
            'transaction_details'=> self::getLogTransactionDetails($employee, [], $attributes),
        ]);

        //check if self services is to be setup for the created employee to create a user associated with employee
        if($this->helper->checkArrayKeyExists($attributes, 'self_service')){
            //create self-service user
            $user = self::createSelfServiceUser($attributes,$employee);

            //include user object in created employee user object
            $employee->user_info = $user;
        }

        DB::commit();

        //return created employee
        return $employee;
    }

    public function update(array $attributes):Employee
    {
        $validationFields = [
            'id',
            'name',
            'country_code',
            'mobile',
            'country_id',
            'gender',
            'email',
            'reporting_to',
            'avatar',
        ];

        //change email validation preset validation rule
        $updateValidationCriteria = $this->allModelValidationCriteria;
        $updateValidationCriteria['email']=['sometimes','required','email'];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($updateValidationCriteria,$validationFields)
        );

        //get employee model
        $this->model = self::getByID($attributes['id']);
        if(is_null($this->model)){
            throw new \Exception('employee not found or is not active');
        }

        //set model attributes(record fields) values
        foreach ($attributes as $key=>$value){
            $this->model->setAttribute($key,$this->helper->checkArrayKeyExists($attributes,$key) && !is_null($value) ? $value : $this->model->getAttribute($key));
        }

        DB::beginTransaction();

        //log employee transaction
        self::logTransaction([
            'transaction_type'=>'update_employee',
            'by_user_id'=>Auth::user()->id,
            'employee_id'=>$attributes['id'],
            'transaction_details'=> self::getLogTransactionDetails($this->model,$validationFields),
        ]);

        //save and preserve model
        $this->model->save();
        DB::commit();

        return self::getByID($attributes['id']);
    }

    /*
     * Moves an employee to another department
     * Accepts: Array of
     * 1- Employee ID (id) - int
     * 2- Department ID (department_id) int
     * 3- New Job title after movement (job_title) String
     * 4- New manager ID (reporting_to) int Optional
     * Returns: none
     */
    public function moveToDepartment(array $attributes)
    {
        try{
            $validationFields = [
                'id',
                'department_id',
                'job_title',
                'reporting_to'
            ];

            //Validate input parameters
            $this->inputsValidator->validateUserInputs(
                $attributes = Arr::only($attributes,$validationFields),
                Arr::Only($this->allModelValidationCriteria,$validationFields)
            );

            $this->model = self::getByID($attributes['id']);
            if(is_null($this->model)){
                throw new \Exception('employee not found or is not active');
            }

            if(!is_null($this->model->last_working_date)){
                throw new \Exception('employee services already terminated');
            }

            //set model attributes(record fields) values
            foreach ($attributes as $key=>$value){
                $this->model->setAttribute($key,$this->helper->checkArrayKeyExists($attributes,$key) && !is_null($value) ? $value : $this->model->getAttribute($key));
            }

            DB::beginTransaction();

            //log employee transaction
            self::logTransaction([
                'transaction_type'=>'move_employee_to_department',
                'by_user_id'=>Auth::user()->id,
                'employee_id'=>$attributes['id'],
                'transaction_details'=>self::getLogTransactionDetails($this->model,$validationFields),
            ]);

            $this->model->save();

            DB::commit();
        }catch (\Exception $ex){
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
    }

    /*
     * Increments an employee's salary
     * Accepts: Array of
     * 1- Employee ID (id) - int
     * 2- New salary (salary) - int
     * 3- Change Effective Date (effective_date) - Date
     * Returns: none
     */
    public function incrementSalary(array $attributes)
    {
        try{
            $validationFields = [
                'id',
                'salary',
                'effective_date',
            ];
            //Validate input parameters
            $this->inputsValidator->validateUserInputs(
                $attributes = Arr::only($attributes,$validationFields),
                Arr::Only($this->allModelValidationCriteria,$validationFields)
            );

            $this->model = self::getByID($attributes['id']);
            if(is_null($this->model)){
                throw new \Exception('employee not found or is not active');
            }

            if(!is_null($this->model->last_working_date)){
                throw new \Exception('employee services already terminated');
            }

            DB::beginTransaction();
            $this->model->salary=$attributes['salary'];

            //log employee transaction
            self::logTransaction([
                'transaction_type'=>'increment_employee_salary',
                'by_user_id'=>Auth::user()->id,
                'employee_id'=>$attributes['id'],
                'transaction_details'=> self::getLogTransactionDetails($this->model,$validationFields,['effective_date'=>$attributes['effective_date']]),
            ]);

            $this->model->save();

            DB::commit();
        }catch (\Exception $ex){
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
    }

    /*
     * Change an employee's job title
     * Accepts: Array of
     * 1- Employee ID (id) - int
     * 2- Job Title (job_title) - string
     * 3- Change Effective Date (effective_date) - Date
     * Returns: none
     */
    public function changeJobTitle(array $attributes)
    {
        try{
            $validationFields = [
                'id',
                'job_title',
                'effective_date',
            ];
            //Validate input parameters
            $this->inputsValidator->validateUserInputs(
                $attributes = Arr::only($attributes,$validationFields),
                Arr::Only($this->allModelValidationCriteria,$validationFields)
            );

            $this->model = self::getByID($attributes['id']);
            if(is_null($this->model)){
                throw new \Exception('employee not found or is not active');
            }

            if(!is_null($this->model->last_working_date)){
                throw new \Exception('employee services already terminated');
            }

            DB::beginTransaction();

            $this->model->job_title=$attributes['job_title'];

            self::logTransaction([
                'transaction_type'=>'change_employee_job_title',
                'by_user_id'=>Auth::user()->id,
                'employee_id'=>$attributes['id'],
                'transaction_details'=> self::getLogTransactionDetails($this->model,$validationFields,['effective_date'=>$attributes['effective_date']]),
            ]);

            $this->model->save();

            DB::commit();
        }catch (\Exception $ex){
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
    }

    /*
     * Set Employees Last working date field indicating that employee's services is terminated on that date
     * Accepts: Array of
     * 1- Employee ID (id) - int
     * 2- Reason (Reason) - string - Possible values: resignation,termination,immediate_termination'
     * 3- Last Working Date (last_working_date) - Date
     * Returns: none
     */
    public function terminateServices(array $attributes)
    {
        try{
            $validationFields = [
                'id',
                'last_working_date',
                'reason',
            ];
            //Validate input parameters
            $this->inputsValidator->validateUserInputs(
                $attributes = Arr::only($attributes,$validationFields),
                Arr::Only($this->allModelValidationCriteria,$validationFields)
            );

            $this->model = self::getByID($attributes['id']);

            if(is_null($this->model)){
                throw new \Exception('employee not found or is not active');
            }

            if(!is_null($this->model->last_working_date)){
                throw new \Exception('employee services already terminated');
            }

            DB::beginTransaction();

            self::disableSelfServices($attributes['id']);

            $this->model->last_working_date=Carbon::parse($attributes['last_working_date']);

            self::logTransaction([
                'transaction_type'=>'terminate_employee_services',
                'by_user_id'=>Auth::user()->id,
                'employee_id'=>$attributes['id'],
                'transaction_details'=> self::getLogTransactionDetails(
                    $this->model,
                    [],
                    [
                        'last_working_date'=>Carbon::parse($attributes['last_working_date']),
                        'reason'=>$attributes['reason']
                    ]
                ),
            ]);
            $this->model->save();


            DB::commit();
        }catch (\Exception $ex){
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
    }

    /*
     * Inactivates employee's user, preventing employee from accessing the portal.
     * Accepts: Array of
     * 1- Employee ID (id) - int
     * Returns: none
     */
    public function disableSelfServices(int $id)
    {
        try{
            $validationFields = [
                'id',
            ];
            //Validate input parameters
            $this->inputsValidator->validateUserInputs(
                $attributes = ['id'=> $id],
                Arr::Only($this->allModelValidationCriteria,$validationFields)
            );

            $this->model = self::getByID($attributes['id']);
            if(is_null($this->model)){
                throw new \Exception('employee not found or is not active');
            }

            if(!is_null($this->model->last_working_date)){
                throw new \Exception('employee services already terminated');
            }

            DB::beginTransaction();

            $this->model->self_service=false;
            self::logTransaction([
                'transaction_type'=>'disable_employee_self_service_user',
                'by_user_id'=>Auth::user()->id,
                'employee_id'=>$attributes['id'],
                'transaction_details'=> self::getLogTransactionDetails(
                    $this->model,
                    ['self_service'],
                    [
                        'self_service'=>false
                    ]
                ),
            ]);
            $this->model->save();


            $this->model->user->is_active=false;
            $this->model->user->save();

            DB::commit();
        }catch (\Exception $ex){
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
    }

    /*
     * Activates employee's user, allowing employee to access the portal.
     * Accepts: Array of
     * 1- Employee ID (id) - int
     * Returns: none
     */
    public function enableSelfServices(int $id)
    {
        try{
            $validationFields = [
                'id',
            ];
            //Validate input parameters
            $this->inputsValidator->validateUserInputs(
                $attributes = ['id'=> $id],
                Arr::Only($this->allModelValidationCriteria,$validationFields)
            );

            $this->model = self::getByID($id);
            if(is_null($this->model)){
                throw new \Exception('employee not found or is not active');
            }

            if(!is_null($this->model->last_working_date)){
                throw new \Exception('employee services already terminated');
            }

            DB::beginTransaction();

            if(is_null($this->model->user)){
                self::createSelfServiceUser([], $this->model);
            }else{
                if(!$this->model->user->is_active) {
                    $this->model->user->is_active = true;
                    $this->model->user->save();
                }
            }

            $this->model->self_service=true;
            self::logTransaction([
                'transaction_type'=>'enable_employee_self_service_user',
                'by_user_id'=>Auth::user()->id,
                'employee_id'=>$attributes['id'],
                'transaction_details'=> self::getLogTransactionDetails(
                    $this->model,
                    ['self_service'],
                    [
                        'self_service'=>true,
                        'user'=>$this->model->user->toArray()
                    ]
                ),
            ]);
            $this->model->save();


            DB::commit();
        }catch (\Exception $ex){
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
    }

    public function getEmployeeLeavesList(array $attributes)
    {
        $validationFields = [
            'id',
            'status',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $employee = self::getByID($attributes['id']);

        $query = $employee->leaves();
        if($attributes['status'] != 'all'){
            $query= $query->where('status',$attributes['status']);
        }

        return EmployeeLeaveRequestResource::collection($query->get());
    }

    public function getEmployeeTransactionLogs(array $attributes)
    {
        $validationFields = [
            'id',
            'transaction_type',
            'data_range_type',
            'custom_date_range_start',
            'custom_date_range_end',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );
        $query=EmployeeTransactionLog::where('employee_id',$attributes['id']);

        if($attributes['transaction_type']!='all'){
            $query=$query->where('transaction_type',$attributes['transaction_type']);
        }


        if($attributes['data_range_type']!='all'){
            $customRangeDateStart = $this->helper->checkArrayKeyExists($attributes, 'custom_date_range_start') ? $attributes['custom_date_range_start'] : null;
            $customRangeDateEnd = $this->helper->checkArrayKeyExists($attributes, 'custom_date_range_end') ? $attributes['custom_date_range_end'] : null;
            $dateRange = self::getDateRange($attributes['data_range_type'], $customRangeDateStart,$customRangeDateEnd);
            $query=$query->whereBetween('created_at',$dateRange);
        }
        return $query->get();
    }

    private function getDateRange(string $rangeType, $customRangeDateStart=null, $customRangeDateEnd=null):array
    {
        //all,today,week,month,3month,6month,year
        switch($rangeType){
            case 'custom':
            case 'day':
                return [Carbon::parse($customRangeDateStart)->startOfDay(), Carbon::parse($customRangeDateEnd)->endOfDay()];
            case 'week':
                return [Carbon::today()->subWeek()->endOfDay(), Carbon::today()->endOfDay()];
            case 'month':
                return [Carbon::today()->subMonth()->endOfDay(), Carbon::today()->endOfDay()];
            case '3month':
                return [Carbon::today()->subMonths(3)->endOfDay(), Carbon::today()->endOfDay()];
            case '6month':
                return [Carbon::today()->subMonths(6)->endOfDay(), Carbon::today()->endOfDay()];
            case 'year':
                return [Carbon::today()->subYear()->endOfDay(), Carbon::today()->endOfDay()];
            default: //Today
                return [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()];
        }
    }

    private function createSelfServiceUser(array $inputs,Employee $employee):Model
    {
        $userEmail = $this->helper->checkArrayKeyExists($inputs, 'self_service_email') ? $inputs['self_service_email'] : $inputs['email'];
        $userIsAdmin = $this->helper->checkArrayKeyExists($inputs, 'is_admin') ? $inputs['is_admin'] : false;
        $userPassword = $this->helper->checkArrayKeyExists($inputs, 'password') ? $inputs['password'] : str::random(16);

        $user = $this->userRepository->create([
            'employee_id'=>$employee->id,
            'name' => $employee->name,
            'email'=> $userEmail,
            'is_admin'=>$userIsAdmin,
            'password'=>$userPassword,
            'email_verified_at'=> Carbon::now(),
        ]);

        //send email to employee with the assigned password
        $user->notify(new UserPasswordNotification($userPassword));

        self::logTransaction([
            'transaction_type'=>'create_employee_self_service_user',
            'by_user_id'=>Auth::user()->id,
            'employee_id'=>$employee->id,
            'transaction_details'=> json_encode([
                'new_values'=>[
                    'self_service'=>true,
                    'user'=>$user->toArray(),
                ],
            ]),
        ]);

        return $user;
    }

    private function logTransaction(array $log_inputs)
    {
        EmployeeTransactionLog::create($log_inputs);
    }

    private function getLogTransactionDetails(Model $model, array $attributes, array $extraInfo = null):string
    {
        $transactionDetails=[];
        foreach ($attributes as $attribute){
            if(Arr::has($model->getAttributes(),$attribute) && $attribute != 'id'){
                $transactionDetails['previous_values'][$attribute]=$model->getOriginal($attribute);
                $transactionDetails['new_values'][$attribute]=$model->getAttribute($attribute);
            }
        }

        if(isset($extraInfo)){
            foreach ($extraInfo as $key => $value)
                $transactionDetails['new_values'][$key]=$value;
        }
        return json_encode($transactionDetails);
    }

    private array $allModelValidationCriteria= [
        'name' => ['required','string','min:3','max:255'],
        'country_code'=>['required_with:mobile','string','max:4'],
        'mobile' => 'required|string|max:16',
        'job_title' => 'required|string|max:100',
        'country_id'=> 'sometimes|integer',
        'gender'=> 'sometimes|boolean',
        'email' =>['required','email','unique:employees,email'],
        'is_active'=>'prohibited',
        'joining_date'=>'required|date',
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
        'leave_id' => 'required|integer|exists:leaves,id',
        'starting_date' => 'required|date|after_or_equal:today',
        'ending_date' => 'required|date|after:start_date',
        'duration'=> 'required|integer|min:0',
        'employee_leave_id' => 'required|integer|exists:employee_leaves,id',
        'action' => 'required|string|in:approved,rejected',
        'last_working_date' => 'required|date',
        'reason' => 'required|string|in:resignation,termination,immediate_termination',
        'effective_date'=> 'required|date',
        'transaction_type'=>'required|string|in:all,create_employee,update_employee,move_employee_to_department,increment_employee_salary,change_employee_job_title,terminate_employee_services,disable_employee_self_service_user,enable_employee_self_service_user,create_employee_self_service_user',
        'data_range_type'=> 'required|string|in:all,day,custom,today,week,month,3month,6month,year',
        'custom_date_range_start'=>'nullable|required_if:data_range_type,day,custom|date',
        'custom_date_range_end'=>'nullable|required_if:data_range_type,day,custom|date|after_or_equal:custom_date_range_start',
        'currentPeriod.*.period_start' => 'required|date',
        'currentPeriod.*.period_end' => 'required|date|after_or_equal:start_date',
    ];
}
