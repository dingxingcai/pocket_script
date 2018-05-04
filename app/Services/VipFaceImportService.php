<?php

namespace App\Services;

use App\Jobs\TianShu\SyncVip;
use App\VipKoalaFaceppMap;

class VipFaceImportService
{
    /**
     * 从文件识别人脸并查询是否绑定了用户id
     * @param $file
     * @return mixed
     * @throws \Exception
     */
    public static function detectFormFile($file)
    {
        $res = KoaLaService::subjectPhoto($file);

        if($res['quality'] <= 0.5){
            throw new \Exception("人脸图片质量不够好，请重新拍摄！");
        }

        //检测是否是人脸
        $token = FacePlusPlusService::detectOnlyOneFace($res['url']);

        //检查用户是否存在
        $searchResult = FacePlusPlusService::searchPeopleExist($token['face_token']);
        if(!empty($searchResult['same'])){
            $faceToken = $searchResult['face_token'];
            /** @var VipKoalaFaceppMap $map */
            $map = VipKoalaFaceppMap::where('face_token', '=', $faceToken)->first();
            !empty($map) && $res['mobile'] = $map->mobile;
        }

        return $res;
    }

    /**
     * 绑定用户人脸
     * @param $koalaPhotoId
     * @param $mobile
     */
    public static function bindVipFace($koalaPhotoId, $mobile)
    {
        $subject = KoaLaService::subjectGetByName($mobile);
        if (empty($subject)) {
            $subject = KoaLaService::subjectPost(['subject_type' => 0, 'name' => $mobile]);

            /**
             * 记录 koalaId和mobile的对应关系
             */
        }
        $map = VipKoalaFaceppMap::find($mobile);
        if(empty($map)){
            $map = new VipKoalaFaceppMap();
            $map->mobile = $mobile;
            $map->koala_id = $subject['id'];
            $map->save();

            $map = VipKoalaFaceppMap::find($mobile);
        }

        /** 不相等 */
        if(empty($subject['photos']) || $subject['photos']['0']['id'] <> $koalaPhotoId){
            $photoIds = [];
//        这个地方一定要转换成整形，不然会被face++认为是空
            $photoIds[$koalaPhotoId] = intval($koalaPhotoId);
//        更新Koala系统的用户信息
            $subject = KoaLaService::subjectPut($subject['id'], ['photo_ids' => array_values($photoIds)]);

            $koalaPhoto = $subject['photos'][0];
            $map->koala_photo_id = $koalaPhoto['id'];
            $map->face_url = $koalaPhoto['url'];
            $map->save();

            dispatch(new \App\Jobs\FacePlusPlus\SyncVip($mobile))->onConnection('sync');
        }

//        dispatch(new SyncVip($mobile))->onConnection('sync');

        return $subject;
    }
}

