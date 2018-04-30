<?php
namespace App\ETL\Input;

use App\Services\YouZanClient;
use App\Services\YouZanService;

class YouZanApi implements IInput
{
    use Marker;

    protected $api = null;
    protected $version = '';
    protected $resultParser = null;

    public function __construct($api, $version, $resultParser, $offset=1)
    {
        $this->api = $api;
        $this->version = $version;
        $this->resultParser = $resultParser;
        $this->marker = $offset;
    }

    public function pull($limit, $params)
    {
        $params['page_no'] = $this->marker;
        $params['page_size'] = $limit;

        $rawRes = (new YouZanClient(YouZanService::accessToken()))->get($this->api, $this->version, $params);

        $result = call_user_func($this->resultParser, $rawRes);

        $this->marker += count($result);
        return $result;
    }
}