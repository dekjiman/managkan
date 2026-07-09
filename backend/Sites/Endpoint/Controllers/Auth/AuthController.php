<?php

namespace Sites\Endpoint\Controllers\Auth;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Auth\Services\AuthService;

class AuthController extends BaseApiController
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    private function formatUser(object $user): array
    {
        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'emailVerified'  => (bool) $user->emailVerified,
            'image'          => $user->image ?? null,
            'stripeCustomerId' => $user->stripeCustomerId ?? null,
            'createdAt'      => $user->createdAt,
            'updatedAt'      => $user->updatedAt,
        ];
    }

    public function login()
    {
        try {
            $input = $this->getJsonInput();
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';

            if (empty($email) || empty($password)) {
                return $this->fail('Email and password are required', 400);
            }

            $result = $this->authService->login($email, $password);
            return $this->response->setStatusCode(200)->setJSON([
                'user'         => $this->formatUser($result['user']),
                'accessToken'  => $result['accessToken'],
                'refreshToken' => $result['refreshToken'],
            ]);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function register()
    {
        try {
            $input = $this->getJsonInput();
            $name = $input['name'] ?? '';
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';

            if (empty($name) || empty($email) || empty($password)) {
                return $this->fail('Name, email, and password are required', 400);
            }

            $result = $this->authService->register($input);
            return $this->response->setStatusCode(201)->setJSON([
                'user'              => $this->formatUser($result['user']),
                'verificationToken' => $result['verificationToken'],
            ]);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function verifyEmail()
    {
        try {
            $input = $this->getJsonInput();
            $token = $input['token'] ?? '';

            if (empty($token)) {
                return $this->fail('Verification token is required', 400);
            }

            $this->authService->verifyEmail($token);
            return $this->respond(null, 200, 'Email verified successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function sendVerificationEmail()
    {
        try {
            $input = $this->getJsonInput();
            $email = $input['email'] ?? '';

            if (empty($email)) {
                return $this->fail('Email is required', 400);
            }

            $this->authService->sendVerificationEmail($email);
            return $this->respond(null, 200, 'Verification email sent');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function forgotPassword()
    {
        try {
            $input = $this->getJsonInput();
            $email = $input['email'] ?? '';

            if (empty($email)) {
                return $this->fail('Email is required', 400);
            }

            $this->authService->forgotPassword($email);
            return $this->respond(null, 200, 'If the email exists, a reset link has been sent');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function resetPassword()
    {
        try {
            $input = $this->getJsonInput();
            $token = $input['token'] ?? '';
            $password = $input['password'] ?? '';

            if (empty($token) || empty($password)) {
                return $this->fail('Token and new password are required', 400);
            }

            $this->authService->resetPassword($token, $password);
            return $this->respond(null, 200, 'Password reset successful');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function signInSocial()
    {
        try {
            $input = $this->getJsonInput();
            $provider = $input['provider'] ?? '';
            $callbackURL = $input['callbackURL'] ?? '';

            log_message('debug', 'signInSocial: provider=' . $provider . ', callbackURL=' . $callbackURL);
            log_message('debug', 'signInSocial: google.clientId=' . (getenv('google.clientId') ?: 'EMPTY'));

            if (empty($provider)) {
                return $this->fail('Provider is required', 400);
            }

            if ($provider === 'google') {
                $clientId = getenv('google.clientId') ?: env('google.clientId');
                $redirectUri = getenv('google.redirectUri') ?: (env('google.redirectUri') ?: rtrim(getenv('app.baseURL') ?: env('app.baseURL'), '/') . '/api/auth/callback/google');
                $params = http_build_query([
                    'client_id'     => $clientId,
                    'redirect_uri'  => $redirectUri,
                    'response_type' => 'code',
                    'scope'         => 'openid email profile',
                    'access_type'   => 'offline',
                    'prompt'        => 'consent',
                    'state'         => $callbackURL,
                ]);
                $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
            } elseif ($provider === 'github') {
                $clientId = getenv('github.clientId') ?: env('github.clientId');
                $redirectUri = getenv('github.redirectUri') ?: (env('github.redirectUri') ?: rtrim(getenv('app.baseURL') ?: env('app.baseURL'), '/') . '/api/auth/callback/github');
                $params = http_build_query([
                    'client_id'    => $clientId,
                    'redirect_uri' => $redirectUri,
                    'scope'        => 'read:user user:email',
                    'allow_signup' => 'true',
                    'state'        => $callbackURL,
                ]);
                $url = 'https://github.com/login/oauth/authorize?' . $params;
            } else {
                return $this->fail('Unsupported provider: ' . $provider, 400);
            }

            return $this->response->setStatusCode(200)->setJSON(['url' => $url, 'message' => 'OAuth URL generated']);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function refresh()
    {
        try {
            $input = $this->getJsonInput();
            $refreshToken = $input['refreshToken'] ?? '';

            if (empty($refreshToken)) {
                return $this->fail('Refresh token is required', 400);
            }

            $result = $this->authService->refreshToken($refreshToken);
            return $this->respond($result, 200, 'Token refreshed');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function me()
    {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                return $this->fail('Unauthorized', 401);
            }

            $user = $this->authService->getProfile($userId);
            return $this->response->setStatusCode(200)->setJSON([
                'data' => $this->formatUser($user),
            ]);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function getSession()
    {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                return $this->response->setStatusCode(200)->setJSON(['user' => null]);
            }

            $user = $this->authService->getProfile($userId);
            $userData = [
                'id'             => $user->id,
                'publicId'       => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'emailVerified'  => (bool) $user->emailVerified,
                'image'          => $user->image ?? null,
                'createdAt'      => $user->createdAt,
                'updatedAt'      => $user->updatedAt,
            ];

            return $this->response->setStatusCode(200)->setJSON(['user' => $userData]);
        } catch (\RuntimeException $e) {
            return $this->response->setStatusCode(200)->setJSON(['user' => null]);
        }
    }

    public function logout()
    {
        try {
            $userId = $this->getUserId();
            return $this->respond(null, 200, 'Logged out successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
