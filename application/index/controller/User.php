<?php

namespace app\index\controller;

use app\index\model\User as UserModel;
use think\Db;
use think\Request;

class user extends Base
{
    //管理员列表
    public function adminList()
    {
        //获取登录用户名
        $user_name = session('user_info.name');
        $this->assign('title','管理员列表');
        if($user_name == 'admin'){
            $data = UserModel::all();
        }else{
            $data = UserModel::all(['name'=>$user_name]);
        }
        $this->assign('data',$data);
        return view('admin-list');
    }

    //删除列表
    public function delList()
    {
        $this->assign('title','删除列表');
        //获取登录用户名
        $user_name = session('user_info.name');
        if($user_name == 'admin'){
            $data = UserModel::onlyTrashed()->select();
        }
        $this->assign('data',$data);
        return view('delete-list');
    }

    //添加管理员
    public function addAdmin(Request $request)
    {
        $this->assign('titile',"添加管理员");
        if($request->isPost()){
            $status = 0;
            $result ='';
            $data = $request->param();

            //验证规则
            $rules = [
                'name|用户名' => 'require|chsAlphaNum',
                'password|密码' => 'require',
                'email|邮箱' => 'require|email',
            ];

            $result = $this->validate($data,$rules);

            if($result === true){
                UserModel::create($data,true);
                $status = 1;
                $result = '添加成功!';
            }

            return ['status'=>$status,'message'=>$result,'data'=>$data];
        }
        return view('admin-add');
    }

    //编辑管理员
    public function adminEdit(Request $request)
    {
        $this->assign('title','编辑管理员信息');

        $user_id = $request->param('id');
        $user = UserModel::get($user_id);
        $this->assign('data',$user->getData());

        if($request->isAjax()){
            //初始返回数据
            $status = 0;
            $result = '';
            $data = $request->param();
            //更新数据
            $map['id'] = $data['id'];
            if(session('user_info.name') == 'admin'){
                $map['status'] = $data['status'];
                $map['role'] = $data['role'];
            }

            //判断用户名是否存在
            $map['name'] = !empty($data['name'])?$data['name']:$user->getData('name');
            if($map['name'] != $user->getData('name')){
                if(UserModel::get(['name'=>$map['name']])){
                    $result = '用户名已存在!';
                }
            }
            //判断邮箱是否存在
            if($data['email'] != $user->email){
                $map['email']  = $data['email'];
                if(UserModel::get(['email'=>$map['email']])){
                    $result = '邮箱已存在!';
                }
            }else{
                $map['email'] = $user->email;
            }
            //判断密码是否修改
            if($data['password'] != $user->password){
                $map['password'] = $data['password'];
                if(strlen($map['password'])<6){
                    $result = '密码长度不能少于6位!';
                }else{
                    $map['password'] = md5($map['password']);
                }
            }else{
                $map['password'] = $user->password;
            }

            if($result == ''){
                $rules = [
                    'name|用户名' => 'require|chsAlphaNum',
                    'email|邮箱' => 'require|email',
                ];

                $result = $this->validate($map,$rules);

                if($result === true){
                    $up = Db('User')->update($map);
                    if($up){
                        $status = 1;
                        $result = '修改成功!';
                    }else{
                        $result = '无修改任何数据!';
                    }
                }
            }


            return ['status'=>$status,'message'=>$result];
        }

        return view('admin-edit');

    }

    //管理员状态变更
    public function setStatus(Request $request)
    {
        $user_id = $request->param('id');
        $user =  UserModel::get($user_id);
        if($user->status == '已启用'){
            $user->save(['status'=>0]);
        }else{
            $user->save(['status'=>1]);
        }
    }

    //管理员软删除 可恢复
    public function deleteAdmin(Request $request)
    {
        //初始化返回数据
        $user_id =  $request->param('id');
        $status = 0;
        $result = '';
        $user = UserModel::get($user_id);
        if($user){
            if($user->name != 'admin'){
                //软删除
                UserModel::destroy($user_id);
                //修改删除状态
                UserModel::onlyTrashed()->where('id',$user_id)->update(['is_delete'=>1]);
                $status = 1;
                $result = '删除成功!';
            }else{
                $result = '无法删除超级管理员!';
            }
        }else{
            //真实删除 无法恢复
            $user = UserModel::onlyTrashed()->find($user_id);
            if($user){
                $user->delete(true);
                $status = 1;
                $result = '删除成功!';
            }
        }
        return ['status'=>$status,'message'=>$result];
    }

    //恢复指定ID管理员
    public function unDelete(Request $request)
    {
        $user_id = $request->param('id');
        UserModel::onlyTrashed()->where('id',$user_id)->update(['delete_time'=>NULL,'is_delete'=>0]);
    }

    //批量恢复管理员
    public function unDeleteALL()
    {
        $user = UserModel::onlyTrashed()->select();
        foreach ($user as $vo){
            //dump($vo->getData());
            UserModel::onlyTrashed()->where('id',$vo->id)->update(['delete_time'=>NULL,'is_delete'=>0]);
        }
    }

    //退出登录
    public function logout(){
        session('user_id',null);
        session('user_info',null);
        $this->success('退出成功,正在跳转!','login/index');
    }

    //判断用名是否重复
    public function checkName(Request $request)
    {
        //初始返回数据
        $status = 0;
        $result = '';
        $name = trim($request->param('name'));
        $user = UserModel::get(['name'=>$name]);
        if($user){
            $result = '用户名已存在!';
        }else{
            $status = 1;
            $result = '用户名可用!';
        }
        return ['status'=>$status,'message'=>$result];
    }

    //判断邮箱是否重复
    public function checkEmail(Request $request)
    {
        $status = 0;
        $result = '';
        $email = $request->param('email');
        $user = UserModel::get(['email'=>$email]);
        if ($user){
            $result = '该邮箱已存在!';
        }else{
            $status = 1;
        }
        return ['status'=>$status,'message'=>$result];
    }

    //测试
    public function test()
    {
        $user_id = 40;
        $user = UserModel::get($user_id);
        $map = $user->getData();
        $map['password'] = '123456';
        Db('User')->update($map);
        dump($map);

    }
}
