<?php
/**
 * Created by PhpStorm.
 * User: win7
 * Date: 2015/12/7
 * Time: 18:09
 */
namespace App\ETL;

class Util {
    public static function format2DArrayToHtml($ary2D)
    {
        $formatted = '';

        if (is_array($ary2D) && count($ary2D) > 0) {
            $formatted .= '<table cellspacing="1" cellpadding="0" bgcolor="gray">' . PHP_EOL;

            $rowInFormatted = 0;
            foreach ($ary2D as $row) {
                if ($rowInFormatted == 0) {
                    // header
                    $formatted .= '<tr>' . PHP_EOL;
                    foreach (array_keys($row) as $k) {
                        $formatted .= '<th bgcolor="white">' . $k . '</th>' . PHP_EOL;
                    }
                    $formatted .= '</tr>' . PHP_EOL;
                }
                $formatted .= '<tr>' . PHP_EOL;
                foreach ($row as $v) {
                    $formatted .= '<td bgcolor="white">' . $v . '</td>' . PHP_EOL;
                }
                $formatted .= '</tr>' . PHP_EOL;
                $rowInFormatted++;
            }

            $formatted .= '</table>' . PHP_EOL;
        } else {
            $formatted .= '<无数据>' . PHP_EOL;
        }

        return $formatted;
    }

    public static function removeEmoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                if (preg_match('/[\b]/u', $match[0])) {
                    return '';
                }
                //debug
//                if(strlen($match[0])==1&&!preg_match('/[0-9a-zA-Z-_\:\.\?\@ ]/u',$match[0])){
//                    echo sprintf("%s|%s",$match[0],urlencode($match[0]));
//                }
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }
}