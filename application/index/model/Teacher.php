<?php

namespace app\index\model;

use think\Model;
use traits\model\SoftDelete;

class Teacher extends Model
{
    //引用软删除方法集
    use SoftDelete;

    //定义表中删除时间字段 记录删除时间
    protected $deleteTime = 'delete_time';

    //设置当前默认日期时间显示格式
    protected $dateFormat = 'Y/m/d';

    //开启自动写入时间戳
    protected $autoWriteTimestamp = true;

    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    protected $type = [
        'start_time' => 'timestamp'
    ];

    //定义自动完成属性
    protected $insert = [];

    //设置与grade表的反关联
    public function grade()
    {
        return $this->belongsTo('Grade');
    }


}
