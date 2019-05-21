<?php

namespace Catalyst\Service;

use Catalyst\Exception\MalformedJsonException;

class JsonService
{
    public static function decode(string $json):\stdClass {
        $output = json_decode($json);
        if (null === $output) {
            throw new MalformedJsonException(
                'Invalid JSON: ' . json_last_error_msg() . ' ('.json_last_error().')'
            );
        }
        return $output;
    }

    public static function encode($data): string
    {
        $isWindows = strcasecmp(substr(PHP_OS, 0, 3), 'WIN') === 0;

        $str = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
        if ($isWindows) {
            $str = str_replace("\n", "\r\n", $str);
        }

        return $str;
    }
}