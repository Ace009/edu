<?php

namespace app\index\model;

use think\Model;
use traits\model\SoftDelete;

class Grade extends Model
{
    //引用软删除方法集
    use SoftDelete;

    //设置删除时间字段,记录删除时间
    protected $deleteTime = 'delete_time';

    //设置当前表默认日期时间格式
    protected $dateFormat = 'Y/m/d';

    //开启自动写入时间戳
    protected $autoWriteTimestamp = true;

    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    //定义自动完成属性
    protected $insert = ['status'=>1];

    //状态获取器
    public function getStatusAttr($value)
    {
        $status = [0=>'已停用',1=>'已启用'];
        return $status[$value];
    }

    //定义关联方法
    public function teacher()
    {
        //班级表与教师表示1对1关联
        return $this->hasOne('Teacher');
    }

    //定义关联方法
    public function student()
    {
        return $this->hasMany('Student','grade_id');
    }
}
