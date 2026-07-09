<?php

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

if (!function_exists('isS3Active')) {
    function isS3Active(): bool
    {
        $endpoint = getenv('s3.endpoint') ?: '';
        $key = getenv('s3.accessKeyId') ?: '';
        return !empty($endpoint) && !empty($key);
    }
}

if (!function_exists('getS3Client')) {
    function getS3Client(): ?S3Client
    {
        if (!isS3Active()) return null;

        return new S3Client([
            'version'             => 'latest',
            'region'              => getenv('s3.region') ?: 'auto',
            'endpoint'            => getenv('s3.endpoint'),
            'credentials'         => [
                'key'    => getenv('s3.accessKeyId'),
                'secret' => getenv('s3.secretAccessKey'),
            ],
            'use_path_style_endpoint' => true,
        ]);
    }
}

if (!function_exists('uploadToStorage')) {
    function uploadToStorage(string $buffer, string $filename, string $contentType): string
    {
        $s3 = getS3Client();
        $bucket = getenv('s3.bucket') ?: 'managpro';

        if ($s3) {
            $s3->putObject([
                'Bucket'      => $bucket,
                'Key'         => $filename,
                'Body'        => $buffer,
                'ContentType' => $contentType,
            ]);

            $publicUrl = getenv('s3.publicUrl') ?: '';
            if (!empty($publicUrl)) {
                return rtrim($publicUrl, '/') . '/' . $filename;
            }
            return $filename;
        }

        // Local storage fallback
        $uploadDir = FCPATH . 'uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . '/' . $filename;
        file_put_contents($filePath, $buffer);

        return 'uploads/' . $filename;
    }
}

if (!function_exists('deleteFromStorage')) {
    function deleteFromStorage(string $path): void
    {
        $s3 = getS3Client();
        $bucket = getenv('s3.bucket') ?: 'managpro';

        if ($s3) {
            try {
                $s3->deleteObject([
                    'Bucket' => $bucket,
                    'Key'    => basename($path),
                ]);
            } catch (AwsException $e) {
                log_message('error', 'S3 delete failed: ' . $e->getMessage());
            }
            return;
        }

        // Local file delete
        $fullPath = FCPATH . $path;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}

if (!function_exists('getStoragePath')) {
    function getStoragePath(string $path): string
    {
        return FCPATH . $path;
    }
}
