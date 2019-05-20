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
        return str_replace(
            "\n",
            "\r\n",
            json_encode(
                $data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );
    }
}