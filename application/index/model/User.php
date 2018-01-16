<?php

namespace app\index\model;

use think\Model;
use traits\model\SoftDelete;

class User extends Model
{
    //使用软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    //保存自动完成列表
    protected $auto = [
        'delete_time'   =>  NULL,
        'is_delete'     =>  0,
    ];
    //新增自动完成列表
    protected $insert = [
        'login_time'    =>  NULL,
        'login_count'   =>  0,
    ];
    //更新自动完成列表
    protected $update = [];
    //是否自动写入时间戳,如果设置为字符串,则表示时间字段的类型
    protected $autoWriteTimestamp = true;
    //创建时间字段
    protected $createTime = 'create_time';
    //更新时间字段
    protected $updateTime = 'update_time';
    //时间字段取出后默认时间格式
    protected $dateFormat = 'Y年m月d日';

    //角色字段获取器
    public function getRoleAttr($value)
    {
        $role = [
            0   =>  '超级管理员',
            1   =>  '管理员',
        ];
        return $role[$value];
    }

    //状态字段获取器
    public function getStatusAttr($value)
    {
        $status = [
            0   =>  '已停用',
            1   =>  '已启用'
        ];
        return $status[$value];
    }

    //密码修改器
    public function setPasswordAttr($value)
    {
        return md5($value);
    }

}
