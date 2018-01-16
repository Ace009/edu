<?php

namespace app\index\controller;

use app\index\model\Student as StuModel;
use think\Request;

class Student extends Base
{
    //学生列表
    public function studentList(Request $request)
    {
        $this->assign('title','学生列表');
        $grade_id = $request->param('grade_id');
        if($grade_id == 'all'){
            $map = '';
        }else if($grade_id == ''){
            $map = '';
        }else{
            $map['grade_id'] = $grade_id;
        }
        $list = StuModel::where($map)->paginate(2);
        $stuList = Array();
        foreach($list as $value){
            $data = $value->getData();
            $data['grade'] = isset($value->grade->name)?$value->grade->name:'<span style="color: red">未分配</span>';
            $stuList[] = $data;
        }

        $this->assign('data',$stuList);
        $this->assign('page',$list->render());
        $this->assign('gradeList',\app\index\model\Grade::all());
        $this->assign('grade_id',$grade_id);
        $this->assign('count',StuModel::where($map)->count());
        return view('student-list');
    }

    //删除列表
    public function delList(Request $request)
    {
        $this->assign('title','学生列表');
        $grade_id = $request->param('grade_id');
        if($grade_id == 'all'){
            $map = '';
        }else if($grade_id == ''){
            $map = '';
        }else{
            $map['grade_id'] = $grade_id;
        }
        $list = StuModel::onlyTrashed()->where($map)->paginate(2);
        $stuList = Array();
        foreach($list as $value){
            $data = $value->getData();
            $data['grade'] = isset($value->grade->name)?$value->grade->name:'<span style="color: red">未分配</span>';
            $stuList[] = $data;
        }

        $this->assign('data',$stuList);
        $this->assign('page',$list->render());
        $this->assign('gradeList',\app\index\model\Grade::all());
        $this->assign('grade_id',$grade_id);
        $this->assign('count',StuModel::onlyTrashed()->where($map)->count());
        return view('student-del');
    }

    //添加学生
    public function studentAdd(Request $request)
    {
        if($request->isAjax()){
            $data = $request->post();
            $status = 0;
            $result = '';

            $rules = [
                'name|姓名'        =>   'require',
                'mobile|手机号码'   =>   ['require','regex'=>'/^0?(13|14|15|17|18)[0-9]{9}$/'],
                'email|邮箱'       =>    'require|email',
                'age|年龄'         =>     'require|number|between:1,120',
                'start_time|入学时间'  =>   'date',
            ];
            $result = $this->validate($data,$rules);

            if($result === true){
                $data['start_time'] = empty($data['start_time']) ? time(): strtotime($data['start_time']);
                if(StuModel::create($data,true)){
                    $status = 1;
                    $result = '添加成功';
                }else{
                    $result = '添加失败';
                }
            }

            return ['status'=>$status,'message'=>$result];
        }
        $this->assign('title','添加学生');
        $this->assign('gradeList',\app\index\model\Grade::all());
        return view('student-add');
    }

    //编辑学生信息
    public function studentEdit(Request $request)
    {
        $stu_id = $request->param('id');
        $student = StuModel::get($stu_id);
        $this->assign('data',$student->getData());
        $this->assign('title','编辑学生信息');
        $this->assign('gradeList',\app\index\model\Grade::all());

        if($request->isPost()){
            $data = $request->post();
            $status = 0;
            $result = '';

            $rules = [
                'name|姓名'        =>   'require',
                'mobile|手机号码'   =>   ['require','regex'=>'/^0?(13|14|15|17|18)[0-9]{9}$/'],
                'email|邮箱'       =>    'require|email',
                'age|年龄'         =>     'require|number|between:1,120',
                'start_time|入学时间'  =>   'date',
            ];
            $result = $this->validate($data,$rules);

            if($result === true){
                $data['start_time'] = empty($data['start_time']) ? time(): strtotime($data['start_time']);
                if(StuModel::update($data,true)){
                    $status = 1;
                    $result = '编辑成功';
                }else{
                    $result = '无数据修改';
                }
            }
            return ['status'=>$status,'message'=>$result];
        }

        return view('student-edit');
    }

    //状态变更
    public function setStatus(Request $request)
    {
        $stu_id = $request->param('id');
        $student = StuModel::get($stu_id);
        if($student->getData('status') == 1){
            $student->save(['status'=>0]);
        }else{
            $student->save(['status'=>1]);
        }
    }

    //学生删除
    public function studentDel(Request $request)
    {
        $student_id = $request->param('id');
        $status = 0;
        $result = '';
        $student = StuModel::get($student_id);
        if($student){
            StuModel::destroy($student_id);
            $student->save(['is_delete'=>1]);
            $status = 1;
            $result = '删除成功';
        }else{
            if(StuModel::onlyTrashed()->find($student_id)){
                StuModel::destroy($student_id,true);
                $status = 1;
                $result = '永久删除成功';
            }else{
                $result = '删除失败';
            }
        }
        return ['status'=>$status,'message'=>$result];
    }

    //恢复指定id学生
    public function unDelete(Request $request)
    {
        $stu_id = $request->param('id');
        StuModel::onlyTrashed()->where('id',$stu_id)->update(['is_delete'=>0,'delete_time'=>null]);
    }

    //批量恢复
    public function unDeleteAll()
    {
        StuModel::onlyTrashed()->where('is_delete',1)->update(['is_delete'=>0,'delete_time'=>null]);
    }


    public function test()
    {
        $map['grade_id'] = 'null';
        $stu = StuModel::where($map['grade_id'] = '0')->find();
        dump($stu);
    }
}
