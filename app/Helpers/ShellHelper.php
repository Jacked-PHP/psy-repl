<?php

namespace App\Helpers;

class ShellHelper
{
    public static function preparePHPCodeForTinker(string $code): string
    {
        $code = preg_replace('#/\*[^*]*\*+([^/][^*]*\*+)*/#', '', $code);

        // remove commented chunks = multi-line comments
        $code = explode("\n", $code);

        $code = self::processCode($code);

        $code = array_filter($code);
        $code = ' echo \'' . implode(' ', $code) . '\' ';

        return $code;
    }

    private static function processCode(array $code): array
    {
        // prepare code
        $PROTOCOL_PLACEHOLDER = 'PROTOCOL_PLACE_HOLDER';
        return array_map(function ($item) use ($PROTOCOL_PLACEHOLDER) {
            // avoid protocol problems
            $item = str_replace('://', $PROTOCOL_PLACEHOLDER, $item);

            // remove commented chunks - single line comments
            $item = explode('//', $item);

            // remove commented chunks = multi-line comments
            $item[0] = preg_replace('!/\*.*?\*/!s', '', $item[0]);

            // put back protocol
            $item[0] = str_replace($PROTOCOL_PLACEHOLDER, '://', $item[0]);

            // escape single quotes
            $item[0] = str_replace("'", "'\"'", $item[0]);

            // $item[0] = str_replace("\\", "'\\\\", $item[0]);
            $item[0] = str_replace("\\", "\\\\", $item[0]);

            return $item[0];
        }, $code);
    }
}
