<?php
namespace App\Libiary\Context\Handler;

use Monolog\Handler\RotatingFileHandler;

class EntryRecordFileHandler extends RotatingFileHandler
{
    /**
     * @param array $records
     */
    public function handleBatch(array $records)
    {
        $bulk = array();
        $bulk['datetime'] = new \DateTime();

        $format = $this->getFormatter();
        $bulk['formatted'] = $format->formatBatch($records) . "\n";

        $this->write($bulk);

        return false === $this->bubble;
    }
}
