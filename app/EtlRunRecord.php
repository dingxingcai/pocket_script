<?php

namespace App;

use App\ETL\ETL;
use App\ETL\Input\Marker;
use Illuminate\Database\Eloquent\Model;

/**
 * App\EtlRunRecord
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string|null $identity etl名称标识
 * @property string|null $marker 比较字段
 * @property string|null $state 状态值
 * @property string|null $etl_snapshot 快照的序列化后数据etl配置
 * @property string|null $params 参数值
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property string $ts_updated 最后一次更新时间
 * @property string $ts_created 创建时间
 * @property int $is_cleaned 是否清理
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EtlRunRecord whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EtlRunRecord whereEtlSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EtlRunRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EtlRunRecord whereIdentity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EtlRunRecord whereMarker($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EtlRunRecord whereParams($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EtlRunRecord whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EtlRunRecord whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EtlRunRecord whereTsCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EtlRunRecord whereTsUpdated($value)
 * @property string|null $stage 临时存储
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EtlRunRecord whereStage($value)
 * @property int|null $total_loaded 全部加载数量
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EtlRunRecord whereTotalLoaded($value)
 */
class EtlRunRecord extends Model
{
    const STATE_RUNNING = 'running';
    const STATE_END = 'end';
    const STATE_FAIL = 'fail';
    const STATE_MERGED = 'merged';
    const STATE_CANCEL = 'cancel';
    const STATE_CLEAN = 'clean';

    public $timestamps = false;
    protected $table = 'etl_run_record';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getParamsAttribute($value)
    {
        return \Opis\Closure\unserialize($value);
    }

    public function setParamsAttribute($value)
    {
        $this->attributes['params'] = \Opis\Closure\serialize($value);
    }

    public function getStageAttribute($value)
    {
        return \Opis\Closure\unserialize($value);
    }

    public function setStageAttribute($value)
    {
        $this->attributes['stage'] = \Opis\Closure\serialize($value);
    }

    public function getEtlSnapshotAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setEtlSnapshotAttribute($value)
    {
        $this->attributes['etl_snapshot'] = json_encode($value);
    }

    public static function isRunning($identity)
    {
        return EtlRunRecord::whereState(EtlRunRecord::STATE_RUNNING)->whereIdentity($identity)->exists();
    }

    public static function fetchOneEnd($identity)
    {
        return EtlRunRecord::whereState(EtlRunRecord::STATE_END)->whereIdentity($identity)->orderBy('id', 'ASC')->first();
    }

    public static function createOrWake($identity, ETL $etl, $createCallback, $wakeCallback)
    {
        if(EtlRunRecord::whereState(EtlRunRecord::STATE_FAIL)->whereIdentity($identity)->exists()) {
            throw new \Exception("存在失败的处理过程，请处理完毕后再执行！");
        }

        $record = EtlRunRecord::whereState(EtlRunRecord::STATE_RUNNING)->whereIdentity($identity)->first();

        if (empty($record) || $record->state == EtlRunRecord::STATE_END) {

            $lastRecord = EtlRunRecord::whereIn('state',[EtlRunRecord::STATE_END, EtlRunRecord::STATE_MERGED])->whereIdentity($identity)->orderBy('id', 'desc')->first();

            $record = new EtlRunRecord();
            $record->identity = $identity;
            $record->state = EtlRunRecord::STATE_RUNNING;
            $record->marker = null;
            $record->start_time = date("Y-m-d H:i:s");
            $record->total_loaded = 0;

            !is_null($createCallback) && call_user_func($createCallback, $record, $lastRecord);

            $record->save();
        }

        /** @var Marker $input */
        $input = $etl->getInput();
        $input->setMarker($record->marker);
        $etl->params = $record->params;

        !is_null($wakeCallback) && call_user_func($wakeCallback, $record);
    }

    public static function endOrSleep($identity, ETL $etl, $endCallback)
    {
        $record = EtlRunRecord::whereState(EtlRunRecord::STATE_RUNNING)->whereIdentity($identity)->first();;

        $record->total_loaded += $etl->loaded;

        /** @var Marker $input */
        $input = $etl->getInput();
        $record->marker = $input->getMarker();

        $record->etl_snapshot = [
            'limit' => $etl->limit,
            'upper' => $etl->upper,
            'loaded' => $etl->loaded,
            'lastLoaded' => $etl->lastLoaded
        ];

        if ($etl->hungry()) {
            $record->state = EtlRunRecord::STATE_END;
            $record->end_time = date("Y-m-d H:i:s");
            call_user_func($endCallback, $record);
        }

        $record->save();
    }

    public static function fail($identity, ETL $etl)
    {
        $record = EtlRunRecord::whereState(EtlRunRecord::STATE_RUNNING)->whereIdentity($identity)->first();;
        if(!empty($record)){
            $record->state = self::STATE_FAIL;
            $record->etl_snapshot = $etl;
            $record->save();
        }
    }
}
