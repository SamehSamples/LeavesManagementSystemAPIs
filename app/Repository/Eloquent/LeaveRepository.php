<?php

namespace App\Repository\Eloquent;

use App\Http\Helpers\GeneralHelper;
use App\Models\Leave;
use App\Validators\InputsValidator;
use App\Repository\Interfaces\LeaveRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class LeaveRepository extends BaseRepository implements LeaveRepositoryInterface
{
    //inputs validation object
    private InputsValidator $inputsValidator;

    /**
     * UserRepository constructor.
     *
     * @param Leave $model
     * @param InputsValidator $inputsValidator
     */
    public function __construct(Leave $model, InputsValidator $inputsValidator)
    {
        parent::__construct($model);
        $this->inputsValidator = $inputsValidator;
    }

    public function index(bool $activeOnly= true): Collection
    {
        if(!Auth::user()->isAdmin()){
            throw new \Exception('user not authorized');
        }
        $query =  $this->model::orderBy('id');
        if($activeOnly){
            $query = $query->where('is_active', $activeOnly);
        }
        return $query->get();
    }

    public function getByID(int $id, bool $activeOnly = true):?Leave
    {
        if(!Auth::user()->isAdmin()){
            throw new \Exception('user not authorized');
        }
        $query =  $this->model::where('id', $id);
        if($activeOnly){
            $query = $query->where('is_active', $activeOnly);
        }
        return $query->first();
    }

    public function create(array $attributes):Leave
    {
        if(!Auth::user()->isAdmin()){
            throw new \Exception('user not authorized');
        }

        $validationFields = [
            'name',
            'pay_percentage',
            'default_block_duration_in_days',
            'calculation_period',
            'allowed_blocks_per_period',
            'days_allowed_after',
            'leave_allowed_after',
            'dividable',
            'balance_is_accumulated',
            'grade_sensitive',
            'gender_strict',
            'fallback_leave',
            'is_active',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        return $this->model->create($attributes);
    }

    public function update(array $attributes):Leave
    {
        if(!Auth::user()->isAdmin()){
            throw new \Exception('user not authorized');
        }

        $validationFields = [
            'id',
            'name',
        ];
        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $leave= self::getByID($attributes['id']);
        if(is_null($leave)){
            throw new \Exception('leave not found or is not active');
        }

        $helper = new GeneralHelper();
        foreach ($attributes as $key=>$value){
            $leave->setAttribute($key,$helper->checkArrayKeyExists($attributes,$key) && !is_null($value) ? $value : $leave->getAttribute($key));
        }

        $leave->save();

        return $leave;
    }

    /*
     * Activates Leave Type
     * Accepts: Array of
     * 1- Leave ID (id) - int
     * Returns: none
     */
    public function makeActive(array $attributes)
    {
        if(!Auth::user()->isAdmin()){
            throw new \Exception('user not authorized');
        }

        $validationFields = [
            'id',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $inputs = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );
        Leave::where('id',$attributes['id'])
            ->update(['is_active'=>true]);
    }

    /*
     * Deactivates Leave Type
     * Accepts: Array of
     * 1- Department ID (id) - int
     * Returns: none
     */
    public function makeInactive(array $attributes)
    {
        if(!Auth::user()->isAdmin()){
            throw new \Exception('user not authorized');
        }

        $validationFields = [
            'id',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $inputs = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );
        Leave::where('id',$attributes['id'])
            ->update(['is_active'=>false]);
    }

    private array $allModelValidationCriteria= [
        'id'=>['required','integer','exists:leaves,id'],
        'name' => ['required','string','min:3','max:255'],
        'pay_percentage'=>['required','integer','min:0','max:100'],
        'default_block_duration_in_days'=>['required','integer','min:1','max:365'],
        'calculation_period'=>['nullable','integer','min:1','max:365'],
        'allowed_blocks_per_period'=>['required','integer','min:1','max:365'],
        'days_allowed_after'=>['required','integer','min:1','max:365'],
        'leave_allowed_after'=>['nullable','integer','exists:leaves,id'],
        'dividable'=>['sometimes','boolean'],
        'balance_is_accumulated'=>['sometimes','boolean'],
        'grade_sensitive'=>['sometimes','boolean'],
        'gender_strict'=>['sometimes','boolean'],
        'fallback_leave'=>['sometimes','boolean'],
        'is_active'=>['sometimes','boolean'],
    ];
}
