<?php
namespace App\ETL\Output;

class XlsxMultiSheet implements IOutput
{
    private $filename = null;
    private $writer = null;
    public function __construct($filename)
    {
        $this->writer = new \XLSXWriter();
        $this->filename = $filename;
    }

    public function push($aDatas)
    {
        foreach($aDatas as $key => $aData){
//            writeHeader
            $firstData = current($aData);
            $header = array();
            foreach($firstData as $sKey=>$sValue){
                $header[$sKey] = is_numeric($sValue) ? 'integer' : 'string';
            }
            $this->writer->writeSheetHeader($key, $header);

            foreach($aData as $data){
                $datatmp = array();
                foreach ($data as $k => $v) {
                    $datatmp[$k] = (string)\Snape\Utility\EmojiHelper::removeEmoji($v);
                }
                $this->writer->writeSheetRow($key, $datatmp);
            }
        }

        $this->flush();
    }

    public function flush()
    {
        $dir = dirname($this->filename);
        if(!file_exists($dir)){
            mkdir($dir, 0777, true);
        }
        $this->writer->writeToFile($this->filename);
    }
}