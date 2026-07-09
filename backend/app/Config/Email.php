<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = '';
    public string $fromName   = '';
    public string $recipients = '';

    public string $userAgent = 'CodeIgniter';

    public string $protocol = 'smtp';

    public string $mailPath = '/usr/sbin/sendmail';

    public string $SMTPHost = '';

    public string $SMTPAuthMethod = 'login';

    public string $SMTPUser = '';

    public string $SMTPPass = '';

    public int $SMTPPort = 587;

    public int $SMTPTimeout = 10;

    public bool $SMTPKeepAlive = false;

    public string $SMTPCrypto = 'tls';

    public bool $wordWrap = true;

    public int $wrapChars = 76;

    public string $mailType = 'html';

    public string $charset = 'UTF-8';

    public bool $validate = true;

    public int $priority = 3;

    public string $CRLF = "\r\n";

    public string $newline = "\r\n";

    public bool $BCCBatchMode = false;

    public int $BCCBatchSize = 200;

    public bool $DSN = false;

    public function __construct()
    {
        parent::__construct();

        // Read .env directly as fallback (getenv/env may not work on all hosts)
        $env = [];
        $envFile = ROOTPATH . '.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') continue;
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $env[trim($parts[0])] = trim($parts[1]);
                }
            }
        }

        $get = fn(string $key, string $default = '') => $env[$key] ?? getenv($key) ?: env($key) ?: $default;

        $this->fromEmail = $get('mail.fromEmail', 'noreply@managpro.com');
        $this->fromName  = $get('mail.fromName', 'ManagPro');
        $this->SMTPHost  = $get('smtp.host', 'smtp.gmail.com');
        $this->SMTPUser  = $get('smtp.user', '');
        $this->SMTPPass  = $get('smtp.pass', '');
        $this->SMTPPort  = (int) $get('smtp.port', '587');
        $this->SMTPCrypto = $get('smtp.crypto', 'tls');

        // Debug log
        log_message('debug', 'Email Config: host=' . $this->SMTPHost . ' port=' . $this->SMTPPort . ' crypto=' . $this->SMTPCrypto . ' user=' . $this->SMTPUser);
    }
}
