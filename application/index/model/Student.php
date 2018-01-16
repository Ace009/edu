<?php

namespace app\index\model;

use think\Model;
use traits\model\SoftDelete;

class Student extends Model
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

    //性别获取器
    public function getSexAttr($value)
    {
        $sex = [
            0 => '女',
            1 => '男'
        ];
        return $sex[$value];
    }

    //设置与 grade 表的反关联
    public function grade()
    {
        return $this->belongsTo('Grade');
    }
}
