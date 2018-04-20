<?php
namespace App\Libiary\Context\Fact;

use App\Libiary\Context\Dimension\DimExecution;

class FactError extends FactBase
{
    const MAX_DEPTH = 50;
    const LOG_ARGS = 2;     // 0: 不记录函数参数；  1：记录所有函数参数； 2：仅记录顶部栈帧的函数参数。

    protected $traceMode = null;
    protected $entrySep = null;

    const SPLIT_MARK = ' ';

    private static $self = null;
    public function __construct()
    {
        $this->traceMode = ~DEBUG_BACKTRACE_PROVIDE_OBJECT & DEBUG_BACKTRACE_IGNORE_ARGS;
        $this->entrySep = self::SPLIT_MARK . self::SPLIT_MARK;
    }

    /**
     * @return null|FactError
     */
    public static function instance()
    {
        if(is_null(self::$self)){
            self::$self = new self();
        }

        return self::$self;
    }

    public function name()
    {
        return "fact_error";
    }

    public function checkAndRecordErrorCatchByShutdown()
    {
        $iRecordError = E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT;

        $aError = error_get_last();
        if(!empty($aError) && isset($aError['type']) && ($aError['type'] & $iRecordError)){
            $dimExecution = DimExecution::instance();
            $this->record(
                array(
                    'source' => 'shutdown',
                    'msg' => json_encode($aError),
                    'type' => $aError['type'],
                    'mca' => $dimExecution->property(DimExecution::PROPERTY_MCA, ''),
                    'user_id' => $dimExecution->property(DimExecution::PROPERTY_USER_ID, ''),
                    'ip' => $dimExecution->property(DimExecution::PROPERTY_IP, ''),
                    'url' => $dimExecution->property(DimExecution::PROPERTY_URL, ''),
                    'user_agent' => $dimExecution->property(DimExecution::PROPERTY_USER_AGENT, ''),
                    'app_id' => $dimExecution->property(DimExecution::PROPERTY_APP_ID, '')
                )
            );
        }
    }

    /**
     * @param $code
     * @param $message
     * @throws \Exception
     */
    public function recordErrorCatchByErrorHandler($code, $message)
    {
        $this->record(array(
            'source' => 'error', 'msg' => $this->formatContent($this->fetchCallStack(), $message)));
    }

    /**
     * @param \Exception $exception
     * @throws \Exception
     */
    public function recordException(\Exception $exception)
    {
        $dimExecution = DimExecution::instance();
        $this->record(
            array(
                'source' => 'exception',
                'msg' => $this->formatContent($exception->getTrace(), $exception->getMessage()),
                'type' => $exception->getCode(),
                'mca' => $dimExecution->property(DimExecution::PROPERTY_MCA, ''),
                'user_id' => $dimExecution->property(DimExecution::PROPERTY_USER_ID, ''),
                'ip' => $dimExecution->property(DimExecution::PROPERTY_IP, ''),
                'url' => $dimExecution->property(DimExecution::PROPERTY_URL, ''),
                'user_agent' => $dimExecution->property(DimExecution::PROPERTY_USER_AGENT, ''),
                'app_id' => $dimExecution->property(DimExecution::PROPERTY_APP_ID, '')
            )
        );
    }

    /**
     * 注册捕捉函数
     */
    public function registerErrorAndExceptionHandler()
    {
        $self = $this;

        /**
         * 记录错误日志
         */
        set_error_handler(function($code, $message) use ($self){
            $self->recordErrorCatchByErrorHandler($code, $message);
        },
            E_ERROR | E_RECOVERABLE_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR
        );

        /**
         * 记录异常
         */
        set_exception_handler(function($exception) use ($self) {
            $self->recordExceptionCatchByExceptionHandler($exception);
        });
    }

    /**
     * 获取栈信息
     * @param int $extraSkip
     * @return array
     */
    protected function fetchCallStack($extraSkip = 0)
    {
        if (!function_exists('debug_backtrace')) {
            return array();
        }
        if (PHP_VERSION_ID >= 50400) {
            $stack = debug_backtrace($this->traceMode, static::MAX_DEPTH);
        }
        elseif (PHP_VERSION_ID >= 50306) {
            $stack = debug_backtrace($this->traceMode);
        }
        else {
            $stack = debug_backtrace();
        }

        for ($i = 0, $skip = 2 + $extraSkip; $i < $skip; ++$i) {
            array_shift($stack);
        }
        $offset = count($stack) - static::MAX_DEPTH;
        return $offset > 0 ? array_slice($stack, $offset, static::MAX_DEPTH) : $stack;
    }

    /**
     * @param array $stack
     * @param $message
     * @return string
     */
    protected function formatContent(array $stack, $message)
    {
        $frameList = array();
        $logArgs = self::LOG_ARGS !== 0;
        foreach ($stack as $frame) {
            $frameList[] = $this->formatFrame($frame, $logArgs);
            $logArgs = self::LOG_ARGS === 1;
        }
        return $message . ':' . self::SPLIT_MARK
        . implode(self::SPLIT_MARK, $frameList) . self::SPLIT_MARK;
    }

    /**
     * @param $frame
     * @param bool|false $logArgs
     * @return string
     */
    protected function formatFrame($frame, $logArgs = false)
    {
        $fileLine = empty($frame['file']) ? '{native-code}' : "{$frame['file']}:{$frame['line']}";
        $type = empty($frame['type']) ? '' : $frame['type'];
        $call = $type ? "{$frame['class']}{$type}{$frame['function']}" : $frame['function'];
        $argStr = '';
        if ($logArgs) {
            if (!empty($frame['args'])) {
                $argStr = json_encode($frame['args']);
                $argStr = substr($argStr, 1, strlen($argStr) - 2);
            } else {
                $argStr = '<no-args>';
            }
        }
        return "{$fileLine} {$call}({$argStr})";
    }
}