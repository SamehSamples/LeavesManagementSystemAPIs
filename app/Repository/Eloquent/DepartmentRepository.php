<?php

namespace App\Repository\Eloquent;

use App\Http\Helpers\GeneralHelper;
use App\Models\Department;
use App\Validators\InputsValidator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Repository\Interfaces\DepartmentRepositoryInterface;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    //inputs validation object
    private InputsValidator $inputsValidator;
    protected $model;

    /**
     * UserRepository constructor.
     *
     * @param Department $model
     * @param InputsValidator $inputsValidator
     */
    public function __construct(Department $model, InputsValidator $inputsValidator)
    {
        parent::__construct($model);
        $this->inputsValidator = $inputsValidator;
        $this->model = $model;
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

    public function getByID(int $id, bool $activeOnly = true):?Department
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

    public function create(array $attributes):Department
    {
        $validationFields = [
            'name',
            'manager_id',
            'is_active',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );
        if(!Auth::user()->isAdmin()){
            throw new \Exception('user not authorized');
        }
        return $this->model->create($attributes);
    }

    public function update(array $attributes):Department
    {
        if(!Auth::user()->isAdmin()){
            throw new \Exception('user not authorized');
        }

        $validationFields = [
            'id',
            'name',
            'manager_id',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $department= self::getByID($attributes['id']);
        if(is_null($department)){
            throw new \Exception('department not found or is not active');
        }

        $helper = new GeneralHelper();
        foreach ($attributes as $key=>$value){
            $department->setAttribute($key,$helper->checkArrayKeyExists($attributes,$key) && !is_null($value) ? $value : $department->getAttribute($key));
        }

        $department->save();

        return $department;
    }

    /*
     * Assign a department manager
     * Accepts: Array of
     * 1- Department ID (id) - int
     * 2- Manager ID (manager_id) int
     * Returns: none
     */
    public function assignManager(array $attributes)
    {
        $validationFields = [
            'id',
            'manager_id',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $department= self::getByID($attributes['id']);
        if(is_null($department)){
            throw new \Exception('department not found or is not active');
        }

        if(!Auth::user()->isAdmin()){
            throw new \Exception('user not authorized');
        }

        $department->manager_id=$attributes['manager_id'];
        $department->save();
    }

    /*
     * Activates department
     * Accepts: Array of
     * 1- Department ID (id) - int
     * Returns: none
     */
    public function makeActive(array $attributes)
    {
        $validationFields = [
            'id',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $department= self::getByID($attributes['id'],false);

        if(!Auth::user()->isAdmin()){
            throw new \Exception('user not authorized');
        }

        $department->is_active=true;
        $department->save();
    }

    /*
     * Deactivates department
     * Accepts: Array of
     * 1- Department ID (id) - int
     * Returns: none
     */
    public function makeInactive(array $attributes)
    {
        $validationFields = [
            'id',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $department= self::getByID($attributes['id'],false);

        if(!Auth::user()->isAdmin()){
            throw new \Exception('user not authorized');
        }

        $department->is_active=false;
        $department->save();
    }

    private array $allModelValidationCriteria= [
        'id'=>['required','integer','exists:departments,id'],
        'name' => ['required','string','min:3','max:255'],
        'manager_id'=>['sometimes','required','exists:employees,id'],
        'is_active'=>['sometimes','boolean'],
    ];
}
