<?php

if (!function_exists('generatePublicId')) {
    function generatePublicId(int $length = 12): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charsLen = strlen($chars) - 1;
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $chars[random_int(0, $charsLen)];
        }
        return $id;
    }
}
