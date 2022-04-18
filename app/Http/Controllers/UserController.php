<?php

namespace App\Http\Controllers;

use App\Repository\Interfaces\UserRepositoryInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserController extends Controller
{
    private UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->repository = $userRepository;
    }

    public function index(bool $onlyActive=true):JsonResponse
    {
        return parent::all($this->repository,$onlyActive);
    }

    public function show(int $id, $onlyActive=true):JsonResponse
    {
        return parent::get($this->repository,$id,$onlyActive);
    }

    /*
    login function/API
    to login users to app
    route not protected using Laravel Sanctum
    Accepts:
    1- user email and 2- password
    Returns:
    1- on success returns User json object, access token, and 200 status (OK)
    2- on failure to match login info it returns error message and 401 status (Unauthorized)
    3- if user is inactive or user's email is not verified, it returns error message and 403 status (Forbidden)
    4- on validation errors or other general errors, it returns error message and 400 status (Bad Request)
    */
    public function login(Request $request):JsonResponse
    {
        try{
            $loggedInUserInfo = $this->repository->logUser($request->all());

            //Return user info and access token
            return response()->json([
                'user' => $loggedInUserInfo['user'],
                'access_token' => $loggedInUserInfo['access_token']
            ], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    /*
    register function/API
    Register a user
    route not protected using Laravel Sanctum
    is to be used when the user register him self or when a user accepts a invitation to join an organization
    It sends an email notification to user with email verification request
    Accepts:
    1- email 2- password 3- name 4- employee_id (optional) 5- is_admin (optional)
    8- gender (optional)
    Returns:
    1- on success returns User json object, access token, and 200 status (OK)
    */
    public function register(Request $request):JsonResponse
    {
        try {
            //Create user
            $user = $this->repository->create($request->all());

            event(new Registered($user));

            //return success response with user info and access token
            return response()->json(['user' => $user], ResponseAlias::HTTP_CREATED);
        }catch (\Exception $ex){
            //general error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    /*
    update function/API
    Allows modifying user's profile
    route is protected using Laravel Sanctum
    Accepts:
    1- ID 2-name
    Returns:
    1- on success returns User json object and 200 status (OK)
    */
    public function update(Request $request):JsonResponse
    {
        return parent::modify($this->repository, $request);
    }

    /*
    getUserDetails function/API
    Gets current authenticated user's information
    route is protected using Laravel Sanctum
    Accepts:
    None
    Returns:
    1- on success returns User json object and 200 status (OK)
    */
    public function getUserDetails(): JsonResponse
    {
        try {
            //fetch and return current user info
            return response()->json(['user' => Auth::user()], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    /*
    logout function/API
    Logs out calling user and Deletes user's current token
    route is protected using Laravel Sanctum
    Accepts:
    None
    Returns:
    1- on success returns success message and 200 status (OK)
    2- on other general errors, it returns error message and 400 status (Bad Request)
    */
    public function logout(): JsonResponse
    {
        try {
            //validate there is a login
            if(Auth::check()){
                //delete token
                Auth::User()->tokens()->where('id',Auth::user()->currentAccessToken()->id)->delete();
                //return success response
                return response()->json(['message' => 'user successfully logged out'], ResponseAlias::HTTP_OK);
            }else {
                return response()->json(['message' => 'no logged in user!'], ResponseAlias::HTTP_BAD_REQUEST);
            }
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    /*
    changePassword function/API
    Allows user to change password
    route is protected using Laravel Sanctum
    Accepts:
    1- old password
    2- new password (with its confirmation)
    Returns:
    1- on success returns success message and 200 status (OK)
    2- if user record is not found active in DB, it returns error message and 403 status (Forbidden)
    3- if old password is not matching, it returns error message and 401 status (Unauthorized)
    4- on validation errors or other general errors, it returns error message and 400 status (Bad Request)
    */
    public function changePassword(Request $request):JsonResponse
    {
        try {
            //change password and return success response
            return response()->json(['message' => $this->repository->changePassword($request->all())], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //General error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    /*
    changeStatus function/API
    Change user status by ID (enable, disable, promote, demote, or logout)
    Only Admin users could perform this function
    Disabling a user to cancel all user's sessions
    route not protected using Laravel Sanctum
    Accepts:
    1- id: User ID
    2- status: Possible values are only: enable, disable, promote, demote, or logout
    Returns:
    1- on success returns success message and 200 status (OK)
    2- if no current logged-in user or user is already disabled, it returns error message and 400 status (Bad Request)
    */
    public function changeStatus(Request $request):JsonResponse
    {
        try{
            //change status and return success response
            return response()->json([
                'message' => '[' . $request->status . ' user] done successfully',
                'user'=> $this->repository->changeStatus($request->all()),
            ], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //general error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function verifyEmail(Request $request):JsonResponse
    {
        try{
            $this->repository->verifyEmail($request->route('id'));
            return response()->json(['message'=>'email verified'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //general error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    /*
    resendVerificationEmail function/API
    resends the user email address verification email to user's registered email
    route is not protected
    Accepts:
    1- user's registered email
    Returns:
    1- on success returns success message and 200 status (OK)
    2- on other general errors, it returns error message and 400 status (Bad Request)
    */
    public function resendVerificationEmail(Request $request):JsonResponse
    {
        try {
            $this->repository->resendVerificationEmail($request->only(['registered_email']));
            return response()->json(['message' => 'verification link sent'], ResponseAlias::HTTP_OK);
        }catch (\Exception $ex){
            //general error handler
            return response()->json(['message' => $ex->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
