<?php
namespace App\Libiary\Context;

use App\Libiary\Context\Dimension\DimExecution;

class BlackList
{
    const ALL = -1;

    private $hiddenByMCA = array(
        self::ALL => array(

        ),
    );

    private $hiddenByURL = array(
        self::ALL => array(
            '/favicon.ico',
            '/zzpay/deal/payResult'
        )
    );

    private $hiddenByUserId = array(
    );

    public function discard(DimExecution $execution)
    {
        $appId = $execution->property(DimExecution::PROPERTY_APP_ID, null);

        return
            $this->checkMCA($appId, $execution->property(DimExecution::PROPERTY_MCA, null))
            || $this->checkUrl($appId, $execution->property(DimExecution::PROPERTY_URL, null))
            || $this->checkUserId($appId, $execution->property(DimExecution::PROPERTY_USER_ID, null))
            ;
    }

    /**
     * 按照MCA检查
     * @param $appId
     * @param $mca
     * @return bool
     */
    private function checkMCA($appId, $mca)
    {
        $blackList = $this->hiddenByMCA[self::ALL];

        if(!is_null($appId) && isset($this->hiddenByMCA[intval($appId)])){
            $blackList = array_merge($blackList, $this->hiddenByMCA[intval($appId)]);
        }

        return !is_null($mca) && in_array($mca, $blackList);
    }

    /**
     * 按照URL检查
     * @param $appId
     * @param $url
     * @return bool
     */
    private function checkUrl($appId, $url)
    {
        $blackList = $this->hiddenByURL[self::ALL];

        if(!is_null($appId) && isset($this->hiddenByURL[intval($appId)])){
            $blackList = array_merge($blackList, $this->hiddenByURL[intval($appId)]);
        }

        return !is_null($url) && in_array($url, $blackList);
    }

    private function checkUserId($appId, $userId)
    {
        $blackList = array();

        if(!is_null($appId) && isset($this->hiddenByUserId[intval($appId)])){
            $blackList = $this->hiddenByUserId[intval($appId)];
        }

        return !is_null($userId) && in_array($userId, $blackList);
    }
}