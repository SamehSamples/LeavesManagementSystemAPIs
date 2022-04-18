<?php

namespace App\Repository\Eloquent;

use App\Models\User;
use App\Validators\InputsValidator;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repository\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use JetBrains\PhpStorm\ArrayShape;


class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    //inputs validation object
    private InputsValidator $inputsValidator;
    protected $model;

    /**
     * UserRepository constructor.
     *
     * @param User $model
     * @param InputsValidator $inputsValidator
     */
    public function __construct(User $model, InputsValidator $inputsValidator)
    {
        parent::__construct($model);
        $this->inputsValidator = $inputsValidator;
        $this->model = $model;
    }

    /*
     * get all users list
     * Accepts: none
     * Returns: collection of Users Models
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    public function index(bool $activeOnly= true): Collection
    {
        $query =  $this->model::orderBy('id');
        if($activeOnly){
            $query = $query->where('is_active',$activeOnly);
        }
        return $query->get();
    }

    /*
     * get a user by ID
     * Accepts:
     * 1- user ID
     * 2- a flag to search only active users (optional, default true-only active users)
     * Returns: User Model
     */
    public function getByID(int $userID, bool $activeOnlyUser = true):?User
    {
        $query =  $this->model->where('id', $userID);
        if($activeOnlyUser){
            $query = $query->where('is_active', $activeOnlyUser);
        }
        $user = $query->first();
        return is_null($user) ? null : $user;
    }

    /*
     * get a user by email
     * Accepts:
     * 1- user email
     * Returns: User Model
     */
    public function getByEmail(string $email):?Model
    {
        return $this->model->where('email', $email)->first();
    }

    /*
     * creates and save a User Model
     * Accepts: Array of
     * 1- name string
     * 2- email string
     * 3- password string
     * 4- password_confirmation string
     * 5- is_admin bool (optional)
     * Returns: User Model
     */
    public function create(array $attributes):Model
    {
        $validationFields = [
            'name',
            'is_admin',
            'password',
            'password_confirmation',
            'email',
            'employee_id'
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        return $this->model->create($attributes);
    }

    /*
     * verify user identity and creates access token
     * Accepts: Array of
     * 1- registered_email string
     * 2- password string
     * Returns:
     * 1- User Model
     * 2- Access token
     */
    #[ArrayShape(['user' => "\Illuminate\Database\Eloquent\Model", 'access_token' => "mixed"])]
    public function logUser(array $inputs):array
    {
        //get and validate inputs
        $validationFields = [
            'registered_email',
            'password',
        ];
        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $inputs = Arr::only($inputs,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        //Fetch User Record by login email
        $user= self::getByEmail($inputs['registered_email']);

        //Validate user existence and confirm correct password provided or fail
        if (!$user || !$user->matchPassword($inputs['password'])) {
            throw new \Exception( 'credentials not found');
        }

        //Validate user is active or fail
        if (!$user->isActive()) {
            throw new \Exception('deactivated user');
        }

        //Validate user email is verified or fail
        if (!$user->hasVerifiedEmail()) {
            throw new \Exception( 'email not verified');
        }

        //Create access token
        $accessToken = $user->createToken(env('APP_NAME','hr-hub'))->plainTextToken;

        return [
            'user' => $user,
            'access_token' => $accessToken
        ];
    }

    /*
     * allows modifying names of none-employee users
     * Accepts: Array of
     * 1- User ID (id) int
     * 2- name string
     * Returns: User Model
     */
    public function update(array $inputs):Model
    {
        $validationFields = [
            'id',
            'name',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $inputs = Arr::only($inputs,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $user= self::getByID($inputs['id']);

        if(!(Auth::user()->isAdmin() || Auth::user()->id === $inputs['id'])){
            throw new \Exception('user not authorized');
        }

        $user->name=$inputs['name'];
        $user->save();

        return $user;
    }

    /*
     * change active users password
     * Accepts: Array of
     * 1- old_password string
     * 2- password string
     * 3- password_confirmation
     * Returns:
     * 1- success message string
     */
    public function changePassword(array $inputs):string
    {
        //Validate input parameters
        $validationFields = [
            'old_password',
            'password',
            'password_confirmation',
        ];
        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $inputs = Arr::only($inputs,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        //Fetch and Validate user
        $user = Auth::User();

        if (is_null($user) || !$user->isActive()) {
            throw new \Exception('user not found');
        }

        //Confirm old password
        if (!$user->matchPassword($inputs['old_password'])) {
            throw new \Exception('incorrect old password');
        }

        //Set new password
        $user->password = $inputs['password'];
        $user->save();

        return 'password successfully change';
    }

    /*
     * change status of a selected user
     * Accepts: Array of
     * 1- user ID (id) int
     * 2- status string (possible values: enable, disable, promote, demote, or logout)
     * 3- password_confirmation
     * Returns:
     * 1- User Model
     */
    public function changeStatus(array $inputs):Model
    {
        $validationFields = [
            'id',
            'status',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $inputs = Arr::only($inputs,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        if(!Auth::user()->isAdmin()){
            throw new \Exception('no enough privileges');
        }

        $user = self::getByID($inputs['id'],false);

        if(!$user->isActive() && $inputs['status'] != 'enable'){
            throw new \Exception('user is inactive');
        }

        switch ($inputs['status']) {
            case "promote":
                $user->makeAdmin();
                break;
            case "demote":
                $user->makeUser();
                break;
            case "enable":
                $user->makeActive();
                break;
            case "disable":
                $user->makeInactive();
            case "logout":
                $user->tokens()->delete();
        }
        return $user;
    }

    /*
     * receives email verification request and process it verifying email
     * Accepts:
     * 1- user ID (id) int
     * Returns: none
     */
    public function verifyEmail(int $id){

        $user = self::getByID($id);

        if (!$user) {
            throw new \Exception('user not found');
        }

        if ($user->hasVerifiedEmail()) {
            throw new \Exception('email already verified');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
    }
    /*
     * resends verification email to an unverified user email address
     * Accepts: Array of
     * 1- registered_email string
     * Returns:none
     */
    public function resendVerificationEmail(array $inputs)
    {
        $validationFields = [
            'registered_email'
        ];
        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $inputs = Arr::only($inputs,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $user = self::getByEmail($inputs['registered_email']);

        if (!$user || !$user->isActive()) {
            throw new \Exception('user not found or is not active');
        }


        if($user->hasVerifiedEmail()){
            throw new \Exception('email already verified');
        }
        $user->sendEmailVerificationNotification();
    }

    //all possible User Model validation rules
    private array $allModelValidationCriteria= [
        'name' => ['required', 'string', 'min:3', 'max:255'],
        'employee_id' => ['nullable','int', 'exists:employees,id'],
        'is_admin'=> ['sometimes','boolean'],
        'password' => ['required','min:3'],
        'password_confirmation' => ['required_without:employee_id','same:password'],
        'registered_email'=>['required','email'],
        'id' => ['required','integer','exists:users,id'],
        'old_password' => ['required','min:3'],
        'email'=>['required','email','unique:users'],
    ];
}
