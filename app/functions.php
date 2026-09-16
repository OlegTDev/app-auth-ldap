<?php

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('concatenateUriWithJwt')) {
    function concatenateUriWithJwt(string $returnUrl, string $jwtToken): string
    {
        $separator = (parse_url($returnUrl, PHP_URL_QUERY) == NULL) ? '?' : '&';
        return "{$returnUrl}{$separator}token=$jwtToken";
    }
}