<?php

namespace app\index\controller;

use app\index\model\Grade as GradeModel;
use think\Request;

class Grade extends Base
{
    //显示班级列表
    public function gradeList()
    {
        $this->assign('title','班级列表');

        $grade = GradeModel::all();

        //遍历grade表
        foreach ($grade as $value){
            $data = [
                'id' => $value->id,
                'name' => $value->name,
                'length' => $value->length,
                'price' => $value->price,
                'status' => $value->status,
                'start_time' => $value->start_time,
                'teacher' => isset($value->teacher->name) ? $value->teacher->name : '<span style="color: red;">未分配</span>'
            ];
            $gradeList[] = $data;
        }


        $this->assign('data',$gradeList);
        return view('grade-list');
    }

    //添加班级
    public function gradeAdd(Request $request)
    {
        $this->assign('title','添加班级');
        if($request->isPost()){
            $status = 0;
            $result = '';
            $data = $request->param();

            $rules = [
                'name|班级名称'      =>  'require',
                'length|学制'    =>  'require',
                'price|学费'     =>  'require'
            ];
            $result = $this->validate($data,$rules);

            if($result === true){
                $data['start_time'] = strtotime($data['start_time']);
                if(GradeModel::create($data,true)){
                    $status = 1;
                    $result = '添加成功';
                }else{
                    $result = '添加失败';
                }
            }

            return ['status'=>$status,'message'=>$result];
        }

        return view('grade-add');
    }

    //编辑班级信息
    public function gradeEdit(Request $request)
    {
        $this->assign('title','班级信息修改');
        $grade_id = $request->param('id');
        $grade = GradeModel::get($grade_id);
        $this->assign('data',$grade->getData());
        if($request->isPost()){
            $data = $request->post();
            $result = '';
            $status = 0;

            $rules = [
                'name|班级名称'    =>  'require',
                'length|学制'     =>  'require',
                'price|学费'      =>  'require'
            ];
            $result = $this->validate($data,$rules);

            if($result === true){
                $data['start_time'] = strtotime($data['start_time']);
                if(GradeModel::update($data,true)){
                    $status = 1;
                    $result = '修改成功';
                }else{
                    $result = '无修改任何数据';
                }

            }
            return ['status'=>$status,'message'=>$result];
        }


        return view('grade-edit');
    }


    //班级状态变更
    public function setStatus(Request $request)
    {
        $grade_id = $request->param('id');
        $grade = GradeModel::get($grade_id);

        if($grade->getData('status') == 1){
            $grade->save(['status'=>0]);
        }else{
            $grade->save(['status'=>1]);
        }
    }

    //班级删除
    public function gradeDel(Request $request)
    {
        $grade_id = $request->param('id');
        $status = 0;
        $result = '';
        $grade = GradeModel::get($grade_id);
        if($grade){
            $grade->delete();
            GradeModel::onlyTrashed()->where('id',$grade_id)->update(['is_delete'=>1]);
            $status = 1;
            $result = '删除成功';
        }else{
            $grade = GradeModel::onlyTrashed()->find($grade_id);
            if($grade){
                $grade->delete(true);
                $status = 1;
                $result = '永久删除成功!';
            }else{
                $result = '删除失败';
            }
        }
        return ['status'=>$status,'message'=>$result];
    }

    //班级删除列表
    public function delList()
    {
        $this->assign('title','班级删除列表');
        $grade = GradeModel::onlyTrashed()->select();
        $gradeList = Array();
        foreach ($grade as $value){
            $data = [
                'id' => $value->id,
                'name' => $value->name,
                'length' => $value->length,
                'price' => $value->price,
                'status' => $value->status,
                'start_time' => $value->start_time,
                'teacher' => isset($value->teacher->name) ? $value->teacher->name : '<span style="color: red;">未分配</span>'
            ];
            $gradeList[] = $data;
        }
        $this->assign('data',$gradeList);
        return view('grade-del');
    }

    //恢复指定id班级
    public function unDelete(Request $request)
    {
        $grade_id = $request->param('id');
        GradeModel::onlyTrashed()->where('id',$grade_id)->update(['is_delete'=>0,'delete_time'=>null]);
    }

    //批量恢复
    public function unDeleteAll()
    {
        GradeModel::onlyTrashed()->where('is_delete',1)->update(['is_delete'=>0,'delete_time'=>null]);
    }

    //测试方法
    public function test()
    {

    }

}
