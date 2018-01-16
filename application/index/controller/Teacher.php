<?php

namespace app\index\controller;

use app\index\model\Teacher as TeacherModel;
use think\Request;

class Teacher extends Base
{
    //教师列表
    public function teacherList()
    {
        $this->assign('title','教师列表');

        $list = TeacherModel::all();

        foreach ($list as $value){
            $data = $value->getData();
            $data['grade'] = isset($value->grade->name)?$value->grade->name:'<span style="color: red">未分配</span>';
            $teacherList[] = $data;
        }

        $this->assign('data',$teacherList);

        return view('teacher-list');
    }

    //删除列表
    public function delList()
    {
        $this->assign('title','删除列表');

        $list = TeacherModel::onlyTrashed()->select();
        foreach ($list as $value){
            $data = $value->getData();
            $data['grade'] = isset($value->grade->name)?$value->grade->name:'<span style="color: red">未分配</span>';
            $gradeList[] = $data;
        }
        $this->assign('data',$gradeList);
        return view('teacher-del');
    }

    //添加教师
    public function teacherAdd(Request $request)
    {
        if($request->post()){
            $data = $request->post();
            $status = 0;
            $result = '';

            $rules = [
                'name|姓名'        =>   'require',
                'degree|学历'      =>   'require',
                'mobile|手机号码'   =>   ['require','regex'=>'/^0?(13|14|15|17|18)[0-9]{9}$/'],
                'hiredate|入职时间' =>   'require',
                'school|毕业学校'   =>   'require',
            ];
            $result = $this->validate($data,$rules);

            if($result === true){
                $data['hiredate'] = strtotime($data['hiredate']);
                if(TeacherModel::create($data)){
                    $status = 1;
                    $result = '添加成功';
                }else{
                    $result = '添加失败';
                }
            }

            return ['status'=>$status,'message'=>$result];
        }

        $this->assign('gradeList',\app\index\model\Grade::all());
        return view('teacher-add');
    }

    //编辑教师信息
    public function teacherEdit(Request $request)
    {
        $this->assign('title','教师信息编辑');
        $teacher_id = $request->param('id');
        $teacher = TeacherModel::get($teacher_id);
        $this->assign('data',$teacher->getData());
        $this->assign('gradeList',\app\index\model\Grade::all());

        if($request->post()){
            $data = $request->post();
            $status = 0;
            $result = '';

            $rules = [
                'name|姓名'        =>   'require',
                'degree|学历'      =>   'require',
                'mobile|手机号码'   =>   ['require','regex'=>'/^0?(13|14|15|17|18)[0-9]{9}$/'],
                'hiredate|入职时间' =>   'require',
                'school|毕业学校'   =>   'require',
            ];
            $result = $this->validate($data,$rules);

            if($result === true){
                $data['hiredate'] = strtotime($data['hiredate']);
                if(TeacherModel::update($data,true)){
                    $status = 1;
                    $result = '修改成功';
                }else{
                    $result = '无数据修改';
                }
            }

            return ['status'=>$status,'message'=>$result];
        }

        return view('teacher-edit');
    }

    //状态修改
    public function setStatus(Request $request)
    {
        $teacher_id = $request->param('id');
        $teacher = TeacherModel::get($teacher_id);
        if($teacher->getData('status') == 1){
            $teacher->save(['status'=>0]);
        }else{
            $teacher->save(['status'=>1]);
        }
    }

    //教师删除
    public function teacherDel(Request $request)
    {
        $teacher_id = $request->param('id');
        $status = 0;
        $result = '';
        $teacher = TeacherModel::get($teacher_id);
        if($teacher){
            TeacherModel::destroy($teacher_id);
            $teacher->save(['is_delete'=>1]);
            $status = 1;
            $result = '删除成功';
        }else{
            if(TeacherModel::onlyTrashed()->find($teacher_id)){
                TeacherModel::destroy($teacher_id,true);
                $status = 1;
                $result = '永久删除成功';
            }else{
                $result = '删除失败';
            }
        }
        return ['status'=>$status,'message'=>$result];
    }


    //恢复指定id班级
    public function unDelete(Request $request)
    {
        $teacher_id = $request->param('id');
        TeacherModel::onlyTrashed()->where('id',$teacher_id)->update(['is_delete'=>0,'delete_time'=>null]);
    }

    //批量恢复
    public function unDeleteAll()
    {
        TeacherModel::onlyTrashed()->where('is_delete',1)->update(['is_delete'=>0,'delete_time'=>null]);
    }


    public function test()
    {
        $list = TeacherModel::all();


    }
}
