<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        // validate request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|max:255'
        ]);

        // create user
        $user = $this->authService->register($request);

        // create access token
        $token = $user->createToken('auth_token')->plainTextToken;

        // return
        return response([
            'message' => __('app.registration_success_verify'),
            'results' => [
                'user' => new UserResource($user),
                'token' => $token
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        // validate request
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|max:255'
        ]);

        // login user
        $user = $this->authService->login($request);

        // check if user exists
        if (!$user) {
            return response([
                'message' => __('auth.failed')
            ], 401);
        }

        // create access token
        $token = $user->createToken('auth_token')->plainTextToken;

        // return
        return response([
            'message' => __('app.login_success' . ($user->email_verified_at ? '' : '_verify')),
            'results' => [
                'user' => new UserResource($user),
                'token' => $token
            ]
        ]);
    }

    public function otp(Request $request)
    {
        // get the user
        $user = auth()->user();

        // generate otp
        $otp = $this->authService->otp($user);

        // return
        return response([
            'message' => __('app.otp_sent_success'),
        ]);
    }

    public function verify(Request $request)
    {
        // validate the request
        $request->validate([
            'otp' => 'required|numeric'
        ]);

        // get the user
        $user = auth()->user();

        // verify the otp
        $user = $this->authService->verify($user, $request);

        // return
        return response([
            'message' => __('app.verification_success'),
            'results' => [
                'user' => new UserResource($user)
            ]
        ]);
    }
}
