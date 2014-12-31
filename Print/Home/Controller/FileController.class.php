<?php
// ===================================================================
// | FileName:      FileController.class.php
// ===================================================================
// | Discription：   FileController 文件管理控制器
//      <命名规范：>
// ===================================================================
// +------------------------------------------------------------------
// | 云印南开
// +------------------------------------------------------------------
// | Copyright (c) 2014 云印南开团队 All rights reserved.
// +------------------------------------------------------------------
/**
 * Class and Function List:
 * Function list:
 * - index()
 * - add()
 * - upload()
 * - delete()
 * Classes list:
 * - FileController extends Controller
 */
namespace Home\Controller;
use Think\Controller;
class FileController extends Controller
{
    
    /**
     *文件列表页
     */
    public function index() 
    {
        $uid        = use_id(U('Index/index'));
        if ($uid) 
        {
            $condition['use_id']            = $uid;
            $condition['status']            = array('between', '1,5');
            $File       = D('FileView');
            $this->data = $File->where($condition)->order('file.id desc')->select();
            $this->display();
        } else
        {
            $this->redirect('Home/Index/index');
        }
    }
    
    /**
     *上传页面
     */
    public function add() 
    {
        $uid = use_id(U('Index/index'));
        if ($uid) 
        {
            $this->display();
        } else
        {
            $this->redirect('Home/Index/index');
        }
    }
    
    /**
     *上传处理
     */
    
    public function upload() 
    {
        $uid              = use_id(U('Index/index'));
        if ($uid) 
        {
            $upload           = new \Think\Upload();
            $upload->maxSize  = 10485760;
            
            //10Mb
            $upload->exts     = array('doc', 'docx', 'pdf');
            $upload->rootPath = './Uploads/';
            $upload->savePath = '';
            $info             = $upload->upload();
            if (!$info) 
            {
                $this->error($upload->getError());
            } else
            {
                foreach ($info as $file) 
                {
                    $data['name']                      = $file['name'];
                    $data['pri_id']                      = I('post.pri_id');
                    $data['requirements']                      = "";
                    
                    //I('post.requirements');
                    $data['url']                      = $file['savepath'] . $file['savename'];
                    $data['status']                      = 1;                                       
                    $data['use_id']                      = $uid;
                    $data['copies']                      = I('post.copies');
                    $data['double_side']                 = I('post.double_side');
                    $File                 = M('File');
                    $result               = $File->add($data);
                    if ($result) 
                    {
                        $Notification         = M('Notification');
                        $Notification->fil_id = $result;
                        $Notification->to_id  = $data['pri_id'];
                        $Notification->type   = 1;
                        $Notification->add();
                        $this->success('上传完成');
                    } else
                    {
                        $this->error($File->getError());
                    }
                }
            }
        } else
        {
            $this->error('登录信息已失效', 'Home/Index/index');
        }
    }
    
    /**
     *删除文件记录
     */
    public function delete() 
    {
        $uid    = use_id(U('Index/index'));
        $fid    = I('fid', null, 'intval');
        if ($uid && $fid) 
        {
            $map['id']        = $fid;
            $map['_string'] = 'status=1 OR status=5';
            $File = M('File');
            $result = $File->where($map)->getField('url');
            if ($result) 
            {
                if(@unlink("./Uploads/".$result))
                {
                    $File = M('File');
                    $File->status = 0;
                    $File->url = NULL;
                    $result_1 = $File->where($map)->save();
                    if($result_1)
                    {
                        $this->success($result_1);
                        return;
                    }
                    $this->error('Can not update SQL');
                }
                $this->error('Can not delete URL'.$result);
            }
            $this->error('Can not query SQL');
        }
        $this->error('当前状态不允许删除！');
    }
    
    public function test()
    {
        $File = M('File');
        $result = $File->where('id=1')->find();
        $result_1 = @unlink("./Uploads/".$result['url']);
        echo $result_1;
    }
}
