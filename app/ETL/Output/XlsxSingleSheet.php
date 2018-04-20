<?php
namespace App\ETL\Output;

class XlsxSingleSheet implements IOutput
{
    private $hasWriterHeader = false;
    private $filename = null;
    private $writer = null;
    private $sheet = 'data';

    public function __construct($filename)
    {
        $this->writer = new \XLSXWriter();
        $this->filename = $filename;
    }

    public function push($aData)
    {
        if (!$this->hasWriterHeader) {
            $firstData = current($aData);
            $header = array();
            foreach ($firstData as $sKey => $sValue) {
                $header[$sKey] = is_numeric($sValue) ? 'integer' : 'string';
            }
            $this->writer->writeSheetHeader($this->sheet, $header);
        }

        foreach ($aData as $data) {
            $datatmp = array();
            foreach ($data as $k => $v) {
                $datatmp[$k] = $this->filterEmoji($v);
                if ($datatmp[$k] != $v) {
                    print_r(array($datatmp[$k], $v));
                }
            }
            $this->writer->writeSheetRow($this->sheet, $datatmp);
        }

        $this->flush();
    }

    public function flush()
    {
        $dir = dirname($this->filename);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->writer->writeToFile($this->filename);
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