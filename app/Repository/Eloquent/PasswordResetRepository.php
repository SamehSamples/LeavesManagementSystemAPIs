<?php

namespace App\Repository\Eloquent;

use App\Models\PasswordReset;
use App\Notifications\PasswordResetRequestNotification;
use App\Notifications\PasswordResetSuccessNotification;
use App\Repository\Interfaces\PasswordResetRepositoryInterface;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Validators\InputsValidator;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PasswordResetRepository extends BaseRepository implements PasswordResetRepositoryInterface
{

    private InputsValidator $inputsValidator;
    protected $model;
    private UserRepositoryInterface $userRepository;

    public function __construct(PasswordReset $model,
                                InputsValidator $inputsValidator,
                                UserRepositoryInterface $userRepository)
    {
        parent::__construct($model);
        $this->inputsValidator = $inputsValidator;
        $this->model=$model;
        $this->userRepository=$userRepository;
    }

    public function requestPasswordReset(array $attributes)
    {
            $validationFields = [
                'email',
                'callback_url',
            ];

            //Validate input parameters
            $this->inputsValidator->validateUserInputs(
                $attributes = Arr::only($attributes,$validationFields),
                Arr::Only($this->allModelValidationCriteria,$validationFields)
            );

            //Validate User
            $user = $this->userRepository->getByEmail($attributes['email']);

            if (!$user){
                throw new \Exception('e-mail address not found');
            }

            //Validate user is active or fail
            if (!$user->isActive()) {
                throw new \Exception('deactivated user');
            }

            //Validate user email is verified or fail
            if (!$user->hasVerifiedEmail()) {
                throw new \Exception('email not verified');
            }

            //Create or Update exiting PasswordReset record
            $passwordReset = $this->model->updateOrCreate(
                ['email' => $attributes['email']],
                [
                    'email' => $attributes['email'],
                    'token' => str::random(60)
                ]
            );

            //send password recovery email with token
            if ($passwordReset)
                $user->notify(
                    new PasswordResetRequestNotification(['token'=> $passwordReset->token, 'url'=>$attributes['callback_url']])
                );
    }

    public function findPasswordResetToken(array $attributes):array
    {
        $validationFields = [
            'token',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        $token=$attributes['token'];
        //Validates the existence of the password recovery token
        $passwordReset = $this->model->where('token', $token)->first();
        if (!$passwordReset)
            throw new \Exception('password reset token not found');

        //Validates the password recovery token is not expired (12 hours validity)
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            throw new \Exception('password reset token has expired');
        }

        //Validate user
        $user = $this->userRepository->getByEmail($passwordReset->email);
        if(is_null($user)){
            throw new \Exception('user not found or is inactivated');
        }

        return [
            'status'=>'success',
            'email'=> $passwordReset->email,
            'name'=>$user->name
        ];
    }

    public function resetPassword(array $attributes)
    {
        $validationFields = [
            'email',
            'password',
            'password_confirmation',
            'token',
        ];

        //Validate input parameters
        $this->inputsValidator->validateUserInputs(
            $attributes = Arr::only($attributes,$validationFields),
            Arr::Only($this->allModelValidationCriteria,$validationFields)
        );

        //validate and retrieve related password reset record
        $passwordReset = $this->model->where('token', $attributes['token'])
            ->where('email', $attributes['email'])
            ->first();

        if (!$passwordReset)
            throw new \Exception('Password reset token is invalid');

        //validate and retrieve related user's record
        $user = $this->userRepository->getByEmail($attributes['email']);
        if (!$user)
            throw new \Exception('user not found');

        //Change User's password
        $user->password=$attributes['password'];
        $user->save();

        //delete used Password Reset record
        $passwordReset->delete();

        //Send Password change notification to verified email
        $user->notify(new PasswordResetSuccessNotification($passwordReset));
    }

    private array $allModelValidationCriteria= [
        'email' => 'required|string|email',
        'callback_url' => 'required|url',
        'password' => 'required|string',
        'password_confirmation'=>'required|same:password',
        'token' => 'required|string'
    ];
}
