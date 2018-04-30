<?php

namespace App\Services;

use App\Libiary\Utility\CurlWrapper;

class FacePlusPlusService
{
    const DEFAULT_OUTER_ID = 1;

    public static function detect($imageUrl)
    {
        $res = self::query('detect', ['image_url' => $imageUrl]);

        return $res;
    }

    public static function facesetCreate($faceTokens, $outerId = self::DEFAULT_OUTER_ID)
    {
        $res = self::query('faceset/create', [
            'display_name' => '集合1',
            'outer_id' => $outerId,
            'face_tokens' => $faceTokens,
            'force_merge' => 1
        ]);

        return $res;
    }

    public static function facesetAddface($faceTokens, $outerId = self::DEFAULT_OUTER_ID)
    {
        $res = self::query('faceset/addface',[
            'outer_id' => $outerId,
            'face_tokens' => $faceTokens
        ]);

        return $res;
    }

    public static function facesetRemoveface($faceTokens, $outerId = self::DEFAULT_OUTER_ID)
    {
        $res = self::query('faceset/removeface', [
            'outer_id' => $outerId,
            'face_tokens' => $faceTokens
        ]);

        return $res;
    }

    public static function facesetGetDetail($outerId)
    {
        $res = self::query('faceset/getdetail', [
            'outer_id' => $outerId,
        ]);

        return $res;
    }

    public static function facesetSearchByToken($faceToken, $outerId = self::DEFAULT_OUTER_ID)
    {
        $res = self::query('search', [
            'face_token' => $faceToken,
            'outer_id' => $outerId,
        ]);

        return $res;
    }

    public static function faceSetuserid($faceToken, $userId)
    {
        $res = self::query('face/setuserid', [
            'face_token' => $faceToken,
            'user_id' => $userId
        ]);
    }

    /**
     * 查找指定头像是否存在，如果存在则返回相似度
     * @param $faceToken
     * @return array|mixed
     */
    public static function searchPeopleExist($faceToken)
    {
        try{
            $res = self::facesetSearchByToken($faceToken);
            if(!empty($res['results']) && count($res['results']) > 0){
                $maxConfidence = current($res['results']);
                foreach ($res['results'] as $result){
                    if($result['confidence'] > $maxConfidence['confidence']){
                        $maxConfidence = $result;
                    }
                }

                $same = null;
                if($maxConfidence['confidence'] >= $res['thresholds']['1e-5']){
                    $same = '1e-5';
                }elseif($maxConfidence['confidence'] >= $res['thresholds']['1e-4']){
                    $same = '1e-4';
                }elseif ($maxConfidence['confidence'] >= $res['thresholds']['1e-3']){
                    $same = '1e-3';
                }

                $maxConfidence['same'] = $same;

                return $maxConfidence;
            }
        }catch (\Exception $e){
            if($e->getMessage() <> 'Face++Exception:INVALID_OUTER_ID'){
                throw $e;
            }
        }

        return [];
    }

    public static function facesetSearchByImageUrl($imageUrl, $outerId = self::DEFAULT_OUTER_ID)
    {
        $res = self::query('search', [
            'image_url' => $imageUrl,
            'outer_id' => $outerId,
        ]);

        return $res;
    }

    /**
     * 检测是否只有一张人脸
     * @param $imageUrl
     * @return mixed
     * @throws \Exception
     */
    public static function detectOnlyOneFace($imageUrl)
    {
        $res = self::detect($imageUrl);

        $count = count($res['faces']);
        if($count < 1){
            throw new \Exception("ImageHasNoFace");
        }
        if($count > 1){
            throw new \Exception("ImageHasMoreFaceWith({$count})");
        }

        return current($res['faces']);
    }

    private static function query($path, $data)
    {
        $data = array_merge([
            'api_key' => env('FACEPLUSPLUS_KEY'),
            'api_secret' => env('FACEPLUSPLUS_SECRET'),
        ], $data);

        $resJson = CurlWrapper::post($data, 'https://api-cn.faceplusplus.com/facepp/v3/' . $path);

        $res = json_decode($resJson, true);
        if(empty($res)){
            throw new \Exception("ResponseNotJsonFormatFace++");
        }

        if(!empty($res['error_message'])){
            throw new \Exception("Face++Exception:{$res['error_message']}");
        }

        return $res;
    }

}