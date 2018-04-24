<?php
namespace App\ETL\Output;

use App\ETL\Util;

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
                $datatmp[$k] = Util::removeEmoji($v);
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
}