<?php

namespace Sites\Endpoint\Controllers\Auth;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Auth\Services\AuthService;

class OAuthController extends BaseApiController
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }
    public function google()
    {
        try {
            $clientId = getenv('google.clientId') ?: env('google.clientId');
            $redirectUri = getenv('google.redirectUri') ?: (env('google.redirectUri') ?: rtrim(getenv('app.baseURL') ?: env('app.baseURL'), '/') . '/api/auth/callback/google');
            $scope = 'openid email profile';

            $params = http_build_query([
                'client_id'     => $clientId,
                'redirect_uri'  => $redirectUri,
                'response_type' => 'code',
                'scope'         => $scope,
                'access_type'   => 'offline',
                'prompt'        => 'consent',
            ]);

            $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
            return $this->response->setStatusCode(200)->setJSON(['url' => $url, 'message' => 'Redirect to Google']);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function github()
    {
        try {
            $clientId = getenv('github.clientId') ?: env('github.clientId');
            $redirectUri = getenv('github.redirectUri') ?: (env('github.redirectUri') ?: rtrim(getenv('app.baseURL') ?: env('app.baseURL'), '/') . '/api/auth/callback/github');

            $params = http_build_query([
                'client_id'    => $clientId,
                'redirect_uri' => $redirectUri,
                'scope'        => 'read:user user:email',
                'allow_signup' => 'true',
            ]);

            $url = 'https://github.com/login/oauth/authorize?' . $params;
            return $this->response->setStatusCode(200)->setJSON(['url' => $url, 'message' => 'Redirect to GitHub']);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function callback(string $provider)
    {
        log_message('debug', 'OAuth callback: provider=' . $provider . ', code=' . substr($this->request->getGet('code') ?? '', 0, 10) . '..., state=' . substr($this->request->getGet('state') ?? '', 0, 50));
        try {
            $code = $this->request->getGet('code');
            $state = $this->request->getGet('state');

            if (empty($code)) {
                $frontendUrl = rtrim(getenv('frontend.url') ?: env('frontend.url'), '/') . '/login?error=' . urlencode('Kode otorisasi tidak ditemukan');
                return $this->response->redirect($frontendUrl);
            }

            $callbackURL = $state ?: rtrim(getenv('frontend.url') ?: env('frontend.url'), '/') . '/dashboard';
            $redirectUri = rtrim(getenv('app.baseURL') ?: env('app.baseURL'), '/') . '/api/auth/callback/' . $provider;

            $result = $this->authService->handleOAuthCallback($provider, $code, $redirectUri);

            $params = http_build_query([
                'accessToken'  => $result['accessToken'],
                'refreshToken' => $result['refreshToken'],
                'userId'       => $result['user']->id,
                'name'         => $result['user']->name,
                'email'        => $result['user']->email,
                'image'        => $result['user']->image ?? '',
            ]);

            return $this->response->redirect($callbackURL . '?' . $params);
        } catch (\RuntimeException $e) {
            log_message('error', 'OAuth RuntimeException: ' . $e->getMessage());
            $callbackURL = $this->request->getGet('state') ?: rtrim(getenv('frontend.url') ?: env('frontend.url'), '/') . '/login';
            return $this->response->redirect($callbackURL . '?error=' . urlencode($e->getMessage()));
        } catch (\Throwable $e) {
            log_message('error', 'OAuth Throwable: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $callbackURL = $this->request->getGet('state') ?: rtrim(getenv('frontend.url') ?: env('frontend.url'), '/') . '/login';
            $message = 'Terjadi kesalahan: ' . $e->getMessage();
            return $this->response->redirect($callbackURL . '?error=' . urlencode($message));
        }
    }
}
