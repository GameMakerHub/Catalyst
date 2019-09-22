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

        /*
         * @todo - find out manually how far this is indentend, and then add the spaces accordingly.
         * Looks like right now its only in 1 spot so the one liner with fixed indentation should work

        $pos = strpos($str, "[]");
        while ($pos !== false) {
            $oldstr = $str;
            $str = substr($oldstr, 0, $pos) . "[FOUNDHERE]" . substr($oldstr, $pos+2);
            $pos = strpos($str, "[]");
        }

        */
        ///@todo make sure identation is correct based on spaces in beginning of line
        $str = str_replace("[]", "[\n        \n    ]", $str);

        if ($isWindows) {
            $str = str_replace("\n", "\r\n", $str);
        }

        return $str;
    }
}