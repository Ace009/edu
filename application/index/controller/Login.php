<?php

namespace app\index\controller;

use app\index\model\User;
use think\Controller;
use think\Request;

class Login extends Controller
{
    //显示登录页面
    public function index()
    {
        $this->repeatLogin();
        return view('login');
    }

    //登录验证
    public function checkLoing(Request $request)
    {
        //初始返回数据
        $data = $request->param();
        $result =  '';
        $status =   0;

        //定义验证规则
        $rules = [
            'name|用户名'      =>  'require|min:3',
            'password|密码'  =>  'require|min:6',
            'verify|验证码'    =>  'require|captcha'
        ];
        //进行验证
        $result = $this->validate($data,$rules);

        if($result === true){
            //构造查询条件
            $map = [
                'name'  =>  $data['name'],
                'password'  =>  md5($data['password']),
            ];

            $user = User::get($map);
            if(empty($user)){
                $result = '用户名或密码错误';
            }else{
                if($user->status == '已启用'){
                    $status = 1;
                    $result = '登录成功,正在跳转!';
                    $user->where('id',$user->id)->setInc('login_count');
                    $user->where('id',$user->id)->update(['login_time'=>time()]);
                    session('user_id',$user->id);
                    session('user_info',$user->getData());
                }else{
                    $result = '该用户未启用,请联系超级管理员!';
                }
            }
        }

        return ['status'=>$status,'message'=>$result,'data'=>$data];
    }

    //防止重复登录
    public function repeatLogin(){
        if(session('user_id')){
           $this->error('已经登录,勿重复登录!','index/index');
        }
    }

}
