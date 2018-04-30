<?php

namespace App\Library\Utility;

class EtlHelper
{
    public static function cleanDatetimeForArray(&$src, $keys)
    {
        !is_array($keys) && $keys = [$keys];
        foreach ($keys as $key){
            $src[$key] = self::cleanDatetime(array_get($src, $key));
        }
    }

    public static function cleanDatetime($date, $default = null)
    {
        is_null($default) && $default = '2000-01-01';
        return date('Y-m-d H:i:s', max(strtotime($date), strtotime($default)));
    }

    public static function cleanJsonForArray(&$src, $keys)
    {
        !is_array($keys) && $keys = [$keys];
        foreach ($keys as $key){
            $src[$key] = json_encode(array_get($src, $key, []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }
}