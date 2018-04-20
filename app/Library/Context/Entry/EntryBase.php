<?php
namespace App\Libiary\Context\Entry;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use App\Libiary\Context\Log;

abstract class EntryBase
{
    /**
     * 实例名称
     */
    abstract public function name();

    /**
     * 获取唯一标识,在下层的log中用于合并数据
     * @return null
     */
    abstract public function getIdentity();

    protected function currentTime($format="Y-m-d H:i:s")
    {
        return date($format);
    }

    protected $rid = null;
    public function rid()
    {
        return $this->rid;
    }

    protected function record($data)
    {
        $this->hiddenPasswordAndEnsureDataIsOneDimension($data);
        Log::instance()->recordEntry($this, $data);
    }

    private function hiddenPasswordAndEnsureDataIsOneDimension(&$data)
    {
        foreach($data as $key => &$value){
            if(is_null($value)){
                $value = '';
            }elseif(!is_scalar($value)){
                $value = 'hidden_while_its_not_a_scalar';
            }else{
                // 如果key或内容有password,则需要隐藏掉
                if(strpos(strtolower($key), "password") !== false || strpos(strtolower($value), "password=") !== false){
                    $value = 'password_was_hidden_while_log';
                }
            }
        }
    }
}