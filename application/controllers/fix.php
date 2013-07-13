<?php
/**
 * 请勿在未明确情况下使用
 */
class Fix extends CI_Controller{
    function index(){
        
    }
    /**
     * 数据库comment更改结构后的修复脚本
     */
    function fixComment(){
        $rs=$this->db->select('cm_id,cm_reply_name')->where('cm_reply_name !=',"")->get('vx_comment')->result_array();
        print_r($rs);
        foreach($rs as $item){
            $user_id=$this->db->select('user_id')->from('vx_user')->where('user_name',$item['cm_reply_name'])->get()->row_array();
            $this->db->update('vx_comment',array('cm_reply_id'=>$user_id['user_id']),array('cm_id'=>$item['cm_id']));
        }
    }
}
