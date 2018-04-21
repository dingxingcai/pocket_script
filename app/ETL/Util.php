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
}