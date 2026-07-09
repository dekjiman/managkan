<?php

namespace Module\Auth\Services;

use Module\Auth\Models\UserModel;
use Module\Auth\Models\AccountModel;
use Module\Auth\Models\SessionModel;
use Module\Auth\Models\VerificationModel;
use Module\Email\Services\EmailService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    protected UserModel $userModel;
    protected AccountModel $accountModel;
    protected SessionModel $sessionModel;
    protected VerificationModel $verificationModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->accountModel = new AccountModel();
        $this->sessionModel = new SessionModel();
        $this->verificationModel = new VerificationModel();
    }

    private function uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }

    private static ?array $envCache = null;

    private static function readEnv(string $key, string $default = ''): string
    {
        if (self::$envCache === null) {
            self::$envCache = [];
            $envFile = ROOTPATH . '.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#') continue;
                    $parts = explode('=', $line, 2);
                    if (count($parts) === 2) {
                        self::$envCache[trim($parts[0])] = trim($parts[1]);
                    }
                }
            }
        }
        return self::$envCache[$key] ?? $default;
    }

    private function getJwtSecret(): string
    {
        $secret = self::readEnv('jwt.secret');
        if (!$secret) $secret = getenv('jwt.secret');
        return $secret ?: 'managpro-jwt-secret';
    }

    private function getAccessExpiry(): int
    {
        $val = self::readEnv('jwt.accessExpiry');
        if (!$val) $val = getenv('jwt.accessExpiry');
        return (int)($val ?: 3600);
    }

    private function getRefreshExpiry(): int
    {
        $val = self::readEnv('jwt.refreshExpiry');
        if (!$val) $val = getenv('jwt.refreshExpiry');
        return (int)($val ?: 1209600);
    }

    private function generateAccessToken(object $user): string
    {
        $now = time();
        $payload = [
            'sub'   => $user->id,
            'email' => $user->email,
            'name'  => $user->name,
            'image' => $user->image ?? null,
            'iat'   => $now,
            'exp'   => $now + $this->getAccessExpiry(),
        ];
        return JWT::encode($payload, $this->getJwtSecret(), 'HS256');
    }

    private function generateRefreshToken(object $user): string
    {
        $now = time();
        $payload = [
            'sub'  => $user->id,
            'type' => 'refresh',
            'iat'  => $now,
            'exp'  => $now + $this->getRefreshExpiry(),
        ];
        return JWT::encode($payload, $this->getJwtSecret(), 'HS256');
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userModel->where('email', $email)->first();
        if (!$user) {
            throw new \RuntimeException('Invalid email or password', 401);
        }

        $account = $this->accountModel
            ->where('userId', $user->id)
            ->where('providerId', 'credentials')
            ->first();

        if (!$account || !$account->password) {
            throw new \RuntimeException('Invalid email or password', 401);
        }

        if (!password_verify($password, $account->password)) {
            throw new \RuntimeException('Invalid email or password', 401);
        }

        $accessJwt = $this->generateAccessToken($user);
        $refreshJwt = $this->generateRefreshToken($user);
        $now = date('Y-m-d H:i:s');

        if (!$this->sessionModel->insert([
            'id'        => $this->uuid(),
            'userId'    => $user->id,
            'token'     => $refreshJwt,
            'expiresAt' => date('Y-m-d H:i:s', time() + $this->getRefreshExpiry()),
            'createdAt' => $now,
            'updatedAt' => $now,
        ])) {
            throw new \RuntimeException('Gagal membuat sesi', 500);
        }

        return [
            'user'         => $user,
            'accessToken'  => $accessJwt,
            'refreshToken' => $refreshJwt,
        ];
    }

    public function register(array $data): array
    {
        $existing = $this->userModel->where('email', $data['email'])->first();
        if ($existing) {
            throw new \RuntimeException('Email already registered', 409);
        }

        $now = date('Y-m-d H:i:s');
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $userId = $this->uuid();

        $db = \Config\Database::connect();
        $db->transStart();

        $inserted = $this->userModel->insert([
            'id'      => $userId,
            'name'    => $data['name'],
            'email'   => $data['email'],
            'image'   => $data['image'] ?? null,
            'emailVerified' => 0,
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);

        if (!$inserted) {
            $db->transComplete();
            throw new \RuntimeException('Failed to create user', 500);
        }

        if (!$this->accountModel->insert([
            'id'         => $this->uuid(),
            'accountId'  => $data['email'],
            'providerId' => 'credentials',
            'userId'     => $userId,
            'password'   => $hashedPassword,
            'createdAt'  => $now,
            'updatedAt'  => $now,
        ])) {
            $db->transComplete();
            throw new \RuntimeException('Failed to create account', 500);
        }

        $verificationToken = bin2hex(random_bytes(32));
        if (!$this->verificationModel->insert([
            'id'         => $this->uuid(),
            'identifier' => $data['email'],
            'value'      => $verificationToken,
            'expiresAt'  => date('Y-m-d H:i:s', time() + 86400),
            'createdAt'  => $now,
            'updatedAt'  => $now,
        ])) {
            $db->transComplete();
            throw new \RuntimeException('Failed to create verification token', 500);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \RuntimeException('Registration failed', 500);
        }

        $user = $this->userModel->find($userId);

        return [
            'user'              => $user,
            'verificationToken' => $verificationToken,
        ];
    }

    public function verifyEmail(string $token): void
    {
        $verification = $this->verificationModel
            ->where('value', $token)
            ->where('expiresAt >', date('Y-m-d H:i:s'))
            ->first();

        if (!$verification) {
            throw new \RuntimeException('Invalid or expired verification token', 400);
        }

        $user = $this->userModel->where('email', $verification->identifier)->first();
        if (!$user) {
            throw new \RuntimeException('User not found', 404);
        }

        $this->userModel->update($user->id, [
            'emailVerified' => 1,
            'updatedAt'     => date('Y-m-d H:i:s'),
        ]);

        $this->verificationModel->where('identifier', $verification->identifier)->delete();
    }

    public function sendVerificationEmail(string $email): void
    {
        $user = $this->userModel->where('email', $email)->first();
        if (!$user) {
            throw new \RuntimeException('User not found', 404);
        }

        if ((bool) $user->emailVerified) {
            throw new \RuntimeException('Email already verified', 400);
        }

        $this->verificationModel->where('identifier', $email)->delete();

        $token = bin2hex(random_bytes(32));
        $now = date('Y-m-d H:i:s');

        $this->verificationModel->insert([
            'id'         => $this->uuid(),
            'identifier' => $email,
            'value'      => $token,
            'expiresAt'  => date('Y-m-d H:i:s', time() + 86400),
            'createdAt'  => $now,
            'updatedAt'  => $now,
        ]);

        $emailService = new EmailService();
        try {
            $emailService->sendVerification($email, $token);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to send verification email: ' . $e->getMessage());
        }
    }

    public function forgotPassword(string $email): void
    {
        $user = $this->userModel->where('email', $email)->first();
        if (!$user) {
            throw new \RuntimeException('User not found', 404);
        }

        $token = bin2hex(random_bytes(32));
        $now = date('Y-m-d H:i:s');

        $this->verificationModel->insert([
            'id'         => $this->uuid(),
            'identifier' => $email,
            'value'      => $token,
            'expiresAt'  => date('Y-m-d H:i:s', time() + 3600),
            'createdAt'  => $now,
            'updatedAt'  => $now,
        ]);
    }

    public function resetPassword(string $token, string $password): void
    {
        $verification = $this->verificationModel
            ->where('value', $token)
            ->where('expiresAt >', date('Y-m-d H:i:s'))
            ->first();

        if (!$verification) {
            throw new \RuntimeException('Invalid or expired reset token', 400);
        }

        $account = $this->accountModel
            ->join('user', 'user.id = account.userId')
            ->where('user.email', $verification->identifier)
            ->where('account.providerId', 'credentials')
            ->first();

        if (!$account) {
            throw new \RuntimeException('Account not found', 404);
        }

        $this->accountModel->update($account->id, [
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'updatedAt' => date('Y-m-d H:i:s'),
        ]);

        $this->verificationModel->where('identifier', $verification->identifier)->delete();
    }

    public function refreshToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->getJwtSecret(), 'HS256'));
        } catch (\Exception $e) {
            throw new \RuntimeException('Invalid or expired refresh token', 401);
        }

        if (!isset($decoded->type) || $decoded->type !== 'refresh') {
            throw new \RuntimeException('Invalid token type', 401);
        }

        $session = $this->sessionModel
            ->where('token', $token)
            ->where('expiresAt >', date('Y-m-d H:i:s'))
            ->first();

        if (!$session) {
            throw new \RuntimeException('Session not found or expired', 401);
        }

        $user = $this->userModel->find($decoded->sub);
        if (!$user) {
            throw new \RuntimeException('User not found', 404);
        }

        $accessToken = $this->generateAccessToken($user);

        return ['accessToken' => $accessToken];
    }

    public function getProfile(string $userId): object
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            throw new \RuntimeException('User not found', 404);
        }
        return $user;
    }

    public function handleOAuthCallback(string $provider, string $code, string $redirectUri): array
    {
        $provider = strtolower($provider);
        $now = date('Y-m-d H:i:s');

        if ($provider === 'google') {
            $tokenUrl = 'https://oauth2.googleapis.com/token';
            $postData = http_build_query([
                'code'          => $code,
                'client_id'     => self::readEnv('google.clientId'),
                'client_secret' => self::readEnv('google.clientSecret'),
                'redirect_uri'  => $redirectUri,
                'grant_type'    => 'authorization_code',
            ]);
        } elseif ($provider === 'github') {
            $tokenUrl = 'https://github.com/login/oauth/access_token';
            $postData = http_build_query([
                'code'          => $code,
                'client_id'     => self::readEnv('github.clientId'),
                'client_secret' => self::readEnv('github.clientSecret'),
                'redirect_uri'  => $redirectUri,
            ]);
        } else {
            throw new \RuntimeException('Unsupported provider: ' . $provider, 400);
        }

        $ssl = ['verify_peer' => true, 'verify_peer_name' => true];
        $opts = ['http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postData,
            'timeout' => 30,
        ], 'ssl' => $ssl];
        $tokenResp = @file_get_contents($tokenUrl, false, stream_context_create($opts));
        if (!$tokenResp) {
            $error = error_get_last();
            throw new \RuntimeException('Failed to exchange authorization code: ' . ($error['message'] ?? 'unknown error'), 401);
        }

        $idToken = null;
        $accessToken = null;
        $json = json_decode($tokenResp, true);
        if ($json && isset($json['access_token'])) {
            $accessToken = $json['access_token'];
            $idToken = $json['id_token'] ?? null;
        } else {
            parse_str($tokenResp, $tokenData);
            $accessToken = $tokenData['access_token'] ?? null;
            $idToken = $tokenData['id_token'] ?? null;
        }
        if (!$accessToken) throw new \RuntimeException('Failed to get access token from provider response', 401);

        if ($provider === 'google') {
            // Use userinfo endpoint instead of id_token decode (Google uses RS256, not HS256)
            $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';
            $userInfoOpts = ['http' => [
                'header'  => "Authorization: Bearer $accessToken\r\n",
                'timeout' => 15,
            ], 'ssl' => $ssl];
            $response = @file_get_contents($userInfoUrl, false, stream_context_create($userInfoOpts));
            if (!$response) {
                // Fallback to tokeninfo endpoint
                $response = @file_get_contents('https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=' . urlencode($accessToken), false, stream_context_create(['ssl' => $ssl, 'http' => ['timeout' => 15]]));
            }
            if (!$response) throw new \RuntimeException('Failed to verify Google token', 401);
            $payload = json_decode($response);
            if (!isset($payload->sub)) throw new \RuntimeException('Invalid Google token', 401);

            $providerAccountId = $payload->sub;
            $email = $payload->email ?? '';
            $name = $payload->name ?? explode('@', $email)[0];
            $image = $payload->picture ?? null;
        } elseif ($provider === 'github') {
            $gitOpts = ['http' => ['header' => "Authorization: Bearer $accessToken\r\nUser-Agent: ManagPro\r\n", 'timeout' => 15], 'ssl' => $ssl];
            $ctx = stream_context_create($gitOpts);
            $response = @file_get_contents('https://api.github.com/user', false, $ctx);
            if (!$response) throw new \RuntimeException('Failed to verify GitHub token', 401);
            $payload = json_decode($response);
            if (!isset($payload->id)) throw new \RuntimeException('Invalid GitHub token', 401);

            $providerAccountId = (string)$payload->id;
            $email = $payload->email ?? '';
            $name = $payload->name ?? $payload->login ?? 'User';
            $image = $payload->avatar_url ?? null;

            if (!$email) {
                $emailResp = @file_get_contents('https://api.github.com/user/emails', false, $ctx);
                if ($emailResp) {
                    $emails = json_decode($emailResp);
                    foreach ($emails as $e) {
                        if ($e->primary && $e->verified) { $email = $e->email; break; }
                    }
                }
            }
            if (!$email) throw new \RuntimeException('Could not retrieve email from GitHub', 401);
        }

        $account = $this->accountModel
            ->where('providerId', $provider)
            ->where('accountId', $providerAccountId)
            ->first();

        if ($account) {
            $user = $this->userModel->find($account->userId);
            if (!$user) throw new \RuntimeException('User not found', 404);
            $this->accountModel->update($account->id, ['accessToken' => $accessToken, 'updatedAt' => $now]);
        } else {
            $user = $this->userModel->where('email', $email)->first();
            if (!$user) {
                $userId = $this->uuid();
                if (!$this->userModel->insert([
                    'id'            => $userId,
                    'name'          => $name,
                    'email'         => $email,
                    'image'         => $image,
                    'emailVerified' => 1,
                    'createdAt'     => $now,
                    'updatedAt'     => $now,
                ])) {
                    throw new \RuntimeException('Gagal membuat pengguna baru', 500);
                }
                $user = $this->userModel->find($userId);
                if (!$user) throw new \RuntimeException('Pengguna tidak ditemukan setelah dibuat', 500);
            }
            if (!$this->accountModel->insert([
                'id'           => $this->uuid(),
                'accountId'    => $providerAccountId,
                'providerId'   => $provider,
                'userId'       => $user->id,
                'accessToken'  => $accessToken,
                'createdAt'    => $now,
                'updatedAt'    => $now,
            ])) {
                throw new \RuntimeException('Gagal membuat akun OAuth', 500);
            }
        }

        $accessJwt = $this->generateAccessToken($user);
        $refreshJwt = $this->generateRefreshToken($user);

        $this->sessionModel->insert([
            'id'        => $this->uuid(),
            'userId'    => $user->id,
            'token'     => $refreshJwt,
            'expiresAt' => date('Y-m-d H:i:s', time() + $this->getRefreshExpiry()),
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);

        return [
            'user'         => $user,
            'accessToken'  => $accessJwt,
            'refreshToken' => $refreshJwt,
        ];
    }

    public function socialLogin(string $provider, string $accessToken): array
    {
        $provider = strtolower($provider);

        if ($provider === 'google') {
            $response = @file_get_contents('https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=' . urlencode($accessToken));
            if (!$response) throw new \RuntimeException('Failed to verify Google token', 401);
            $payload = json_decode($response);
            if (!isset($payload->email)) throw new \RuntimeException('Invalid Google token', 401);

            $providerAccountId = $payload->sub;
            $email = $payload->email;
            $name = $payload->name ?? explode('@', $email)[0];
            $image = $payload->picture ?? null;
        } elseif ($provider === 'github') {
            $opts = ['http' => ['header' => "Authorization: Bearer $accessToken\r\nUser-Agent: ManagPro\r\n"]];
            $ctx = stream_context_create($opts);
            $response = @file_get_contents('https://api.github.com/user', false, $ctx);
            if (!$response) throw new \RuntimeException('Failed to verify GitHub token', 401);
            $payload = json_decode($response);
            if (!isset($payload->id)) throw new \RuntimeException('Invalid GitHub token', 401);

            $providerAccountId = (string)$payload->id;
            $email = $payload->email ?? '';
            $name = $payload->name ?? $payload->login ?? 'User';
            $image = $payload->avatar_url ?? null;

            if (!$email) {
                $emailResp = @file_get_contents('https://api.github.com/user/emails', false, $ctx);
                if ($emailResp) {
                    $emails = json_decode($emailResp);
                    foreach ($emails as $e) {
                        if ($e->primary && $e->verified) { $email = $e->email; break; }
                    }
                }
            }
            if (!$email) throw new \RuntimeException('Could not retrieve email from GitHub', 401);
        } else {
            throw new \RuntimeException('Unsupported provider: ' . $provider, 400);
        }

        $now = date('Y-m-d H:i:s');

        $account = $this->accountModel
            ->where('providerId', $provider)
            ->where('accountId', $providerAccountId)
            ->first();

        if ($account) {
            $user = $this->userModel->find($account->userId);
            if (!$user) throw new \RuntimeException('User not found', 404);

            $this->accountModel->update($account->id, ['accessToken' => $accessToken, 'updatedAt' => $now]);
        } else {
            $user = $this->userModel->where('email', $email)->first();

            if (!$user) {
                $userId = $this->uuid();
                $this->userModel->insert([
                    'id'            => $userId,
                    'name'          => $name,
                    'email'         => $email,
                    'image'         => $image,
                    'emailVerified' => 1,
                    'createdAt'     => $now,
                    'updatedAt'     => $now,
                ]);
                $user = $this->userModel->find($userId);
            }

            $this->accountModel->insert([
                'id'           => $this->uuid(),
                'accountId'    => $providerAccountId,
                'providerId'   => $provider,
                'userId'       => $user->id,
                'accessToken'  => $accessToken,
                'createdAt'    => $now,
                'updatedAt'    => $now,
            ]);
        }

        $accessJwt = $this->generateAccessToken($user);
        $refreshJwt = $this->generateRefreshToken($user);

        $this->sessionModel->insert([
            'id'        => $this->uuid(),
            'userId'    => $user->id,
            'token'     => $refreshJwt,
            'expiresAt' => date('Y-m-d H:i:s', time() + $this->getRefreshExpiry()),
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);

        return [
            'user'         => $user,
            'accessToken'  => $accessJwt,
            'refreshToken' => $refreshJwt,
        ];
    }

    public function updateProfile(string $userId, array $data): object
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            throw new \RuntimeException('User not found', 404);
        }

        $allowed = ['name', 'image'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            throw new \RuntimeException('No valid fields to update', 400);
        }

        $updateData['updatedAt'] = date('Y-m-d H:i:s');
        $this->userModel->update($userId, $updateData);

        return $this->userModel->find($userId);
    }
}
