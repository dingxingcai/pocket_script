<?php
namespace App\Libiary\Context\Dimension;

use App\Libiary\Context\Entry\EntryBase;
use App\Libiary\Context\Log;

/**
 * 在一次执行中,仅存在一个实例
 * 允许多次record,但最终会在执行结束时,合并多次record结果(实现效果如同array_merge)
 * Class DimensionBase
 * @package App\Libiary\Context\Dimension
 */
abstract class DimBase extends EntryBase
{
    private $dataLatest = array();

    /**
     * 对于维度表,需要在构造的时候生成rid
     * DimensionBase constructor.
     * @param $uniqueValue - 实例唯一标识
     */
    protected function __construct()
    {
        $this->rid = Log::instance()->generateRid();
    }

    /**
     * 因为一次执行仅允许存在一次实例, 故identity为entry名称
     * @return string
     */
    public function getIdentity()
    {
        return $this->name();
    }

    protected function record($data)
    {
        $this->dataLatest = array_merge($this->dataLatest, $data);

        parent::record($data);
    }

    public function property($key, $default)
    {
        return isset($this->dataLatest[$key]) ? $this->dataLatest[$key] : $default;
    }

    protected function getData(&$array, $key, $default)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}