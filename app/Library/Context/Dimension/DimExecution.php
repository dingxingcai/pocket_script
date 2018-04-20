<?php
namespace App\Libiary\Context\Dimension;

use App\Libiary\Context\Log;

class DimExecution extends DimBase
{
    private static $self = null;

    /**
     * @return null|DimExecution
     */
    public static function instance()
    {
        if(is_null(self::$self)){
            self::$self = new self();
        }

        return self::$self;
    }

    const PROPERTY_APP_ID = 'app_id';
    const PROPERTY_MCA = 'mca';
    const PROPERTY_URL = 'url';
    const PROPERTY_USER_ID = 'user_id';
    const PROPERTY_IP = 'ip';
    const PROPERTY_USER_AGENT = 'user_agent';


    /**
     * 获取名称
     * @return string
     */
    public function name()
    {
        return "dim_execution";
    }

    protected $microtimeBegin = null;
    protected $microtimeEnd = null;

    /**
     * 一次执行的开始
     * @throws \Exception
     */
    public function recordBegin()
    {
        $this->microtimeBegin = microtime(true);

//        判断是否是脚本执行还是浏览器执行
        if(php_sapi_name() == 'cli' || php_sapi_name() == 'phpdbg'){
            $data = [
                'process_id' => getmypid(),
                'begin_time' => $this->currentTime(),
                'mid' => Log::instance()->mid(),
                'hostname' => gethostname(),
            ];
            if(!empty($_SERVER['argv'])){
                $data['url'] = $_SERVER['argv'][0];
                if(count($_SERVER['argv']) > 1){
                    $data['mca'] = $_SERVER['argv'][1];
                }
                $data['argv'] = json_encode($_SERVER['argv'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }else{
            $data = array(
                self::PROPERTY_IP => $this->getClientIp(),
                self::PROPERTY_URL => $this->getData($_SERVER, 'REQUEST_URI', 'unknown'),
                'http_method' => $this->getData($_SERVER, 'REQUEST_METHOD', 'unknown'),
                'server' => $this->getData($_SERVER, 'SERVER_NAME', 'unknown'),
                'referrer' => $this->getData($_SERVER, 'HTTP_REFERER', 'unknown'),
                'process_id' => getmypid(),
                'begin_time' => $this->currentTime(),
                'mid' => Log::instance()->mid(),
                'hostname' => gethostname(),
                self::PROPERTY_USER_AGENT => $this->getData($_SERVER, 'HTTP_USER_AGENT', 'unknown'),
                '_peid' => $this->getData($_REQUEST, '_peid', 0),
                '_pmca' => $this->getData($_REQUEST, '_pmca', 'unknown'),
                '_papp_id' => $this->getData($_REQUEST, '_papp_id', 0),
            );

            if($data['http_method'] == 'POST'){
                $rawPostData = file_get_contents("php://input");
                $data['post_raw_data'] = strlen($rawPostData) <= 1024 ? $rawPostData : substr($rawPostData, 0, 1024);
            }
        }

        $this->record($data);

        return $this;
    }

    public function recordApp($app)
    {
        $this->record(array(self::PROPERTY_APP_ID => $app));

        return $this;
    }

    /**
     * 一次执行的结束
     * @throws \Exception
     */
    public function recordEnd()
    {
        $this->microtimeEnd = microtime(true);

        $this->record(
            array(
                'end_time' => $this->currentTime(),
                'interval' => round($this->microtimeEnd - $this->microtimeBegin, 3) * 1000,
                'session_id' =>session_id()
            )
        );
    }

    public function recordMCA($module,$controller,$action)
    {
        $this->record(
            array(
                self::PROPERTY_MCA => $module . '/' .$controller. '/' . $action
            )
        );

        return $this;
    }

    public function recordResponse($response, $contentType, $code = 200)
    {
        $this->record(
            array(
                'response' => strlen($response) <= 1024 ? $response : substr($response, 0, 1024),
                'code' => $code,
                'response_content_type' => $contentType
            )
        );

        return $this;
    }


    public function recordLoginUser($userId, $userIdentity)
    {
        $this->record(
            array(
                'user_id' => $userId,
                'user_identity' => $userIdentity,
            )
        );
    }

    private function getClientIp()
    {
        if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
            $ip = getenv ( "HTTP_CLIENT_IP" );
        else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
            $ip = getenv ( "HTTP_X_FORWARDED_FOR" );
        else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
            $ip = getenv ( "REMOTE_ADDR" );
        else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
            $ip = $_SERVER ['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return ($ip);
    }
}