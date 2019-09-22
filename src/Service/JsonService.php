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

        // Find any empty arrays and indent them like GM does - GM likes to format them with empty newlines and spaces
        // in it. If we do this, we can keep the diffs to a minimum.
        $pos = strpos($str, "[]");
        while ($pos !== false) {
            $searchNewlinePos = $pos;
            // Walk back until we find a newline, and count the spaces
            $curToken = substr($str, $searchNewlinePos, 1);
            $spaceCount = 0;
            while ($curToken != "\n") {
                if ($curToken == ' ') { //Increase the spacecount if we find a space
                    $spaceCount ++;
                } else { //Reset it
                    $spaceCount = 0;
                }

                $searchNewlinePos--;
                $curToken = substr($str, $searchNewlinePos, 1);
            }

            $oldstr = $str;
            $newString = "[\n"
                . str_repeat(' ', $spaceCount + 4)
                . "\n"
                . str_repeat(' ', $spaceCount) . ']';
            $str = substr($oldstr, 0, $pos) . $newString . substr($oldstr, $pos+2);

            $pos = strpos($str, "[]");
        }

        if ($isWindows) {
            $str = str_replace("\n", "\r\n", $str);
        }

        return $str;
    }
}