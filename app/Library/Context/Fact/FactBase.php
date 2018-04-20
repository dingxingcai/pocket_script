<?php
namespace App\Libiary\Context\Fact;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use App\Libiary\Context\Entry\EntryBase;
use App\Libiary\Context\Log;

/**
 * 事实表基类
 * 在一次执行中允许记录多次,但一次记录即为一个实例
 * 标明发生的事实
 * Class FactBase
 * @package App\Libiary\Context\Fact
 */
abstract class FactBase extends EntryBase
{
    public function getIdentity()
    {
        return $this->rid;
    }

    protected function record($data)
    {
        $this->rid = Log::instance()->generateRid();

        parent::record($data);
    }
}