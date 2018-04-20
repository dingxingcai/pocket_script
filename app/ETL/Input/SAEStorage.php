<?php
namespace App\ETL\Input;

use sinacloud\sae\Storage;

class SAEStorage implements IInput
{
    use Marker;

    private $bucket = null;
    private $prefix = null;
    private $step = null;
    private $filter = null;

    public function __construct($accessKey, $secretKey, $bucket, $prefix, $step, \Closure $filter=null, $marker=null)
    {
        $this->bucket = $bucket;
        $this->prefix = $prefix;
        $this->step = $step;
        $this->filter = $filter;

        $this->setMarker($marker);
        Storage::setAuth($accessKey, $secretKey);
    }

    public function pull($limit, $params)
    {
        $result = [];

        while (count($result) < $limit){

            /** 轮询请求 */
            $tries = 12;
            $existE = null;
            do{
                $tries--;

                try{
                    $objects = Storage::getBucket($this->bucket, $this->prefix, $this->getMarker(), $this->step);
                }catch (\Exception $e){
                    $existE = $e;

                    sleep(5);
                    if($tries <= 0){
                        throw $e;
                    }
                }
            }while(!is_null($existE));


            $count = count($objects);
            if($count > 0){
                $this->setMarker(last($objects)['name']);

                if(!is_null($this->filter)){
                    $result = array_merge($result, call_user_func($this->filter, $objects, $params));
                }else{
                    $result = array_merge($result, $objects);
                }
            }

            if($count < $this->step){
                break;
            }
        }

        return $result;
    }
}