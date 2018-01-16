<?php
namespace app\index\controller;

class Index extends Base
{
    public function index()
    {
        $this->assign('title','教务管理系统');
        return view();
    }

    public function test()
    {
        dump(session('user_info'));
    }
}
