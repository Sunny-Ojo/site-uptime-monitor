<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user and return a token.
     */
    public function register(RegisterRequest $request)
    {
        $token = $this->authService->register($request->validated());

        return $this->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login user and return a token.
     */
    public function login(LoginRequest $request)
    {
        $token = $this->authService->login($request->validated());

        return $this->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Send password reset link.
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = $this->authService->forgotPassword($request->validated()['email']);

        return $status === Password::RESET_LINK_SENT
            ? $this->success(['message' => __($status)])
            : $this->error(__($status), 400);
    }

    /**
     * Reset password.
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = $this->authService->resetPassword(
            $request->only('email', 'password', 'password_confirmation', 'token')
        );

        return $status === Password::PASSWORD_RESET
            ? $this->success(['message' => __($status)])
            : $this->error(__($status), 400);
    }

}
