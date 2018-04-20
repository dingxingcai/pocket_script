<?php
namespace App\ETL\Output;


class Csv implements IOutput
{
    private $hasWriterHeader = false;
    private $filenameCsv = null;

    public function __construct($filename)
    {
        $this->filenameCsv = str_replace('.xlsx', '.csv', $filename);
    }

    public function push($aData)
    {
        if (!$this->hasWriterHeader) {
            $firstData = current($aData);
            $header = array();
            foreach ($firstData as $sKey => $sValue) {
                $header[$sKey] = is_numeric($sValue) ? 'integer' : 'string';
            }
            $this->writeCsvHeader($header);
        }

        foreach ($aData as $data) {
            $datatmp = array();
            foreach ($data as $k => $v) {
                $datatmp[$k] = $this->filterEmoji($v);
                if ($datatmp[$k] != $v) {
                    print_r(array($datatmp[$k], $v));
                }
            }
            $this->writeCsvRow($datatmp);
        }
    }

    public function writeCsvHeader($header)
    {
        $dir = dirname($this->filenameCsv);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($this->filenameCsv, sprintf("\"%s\"\n", implode('","', array_keys($header))), FILE_APPEND);
    }

    public function writeCsvRow($data)
    {
        file_put_contents($this->filenameCsv, sprintf("\"%s\"\n", implode('","', array_values($data))), FILE_APPEND);
    }

    function filterEmoji($str)
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