<?php
require './common/init.php';
require './common/library/Upload.php';
if(!IS_ADMIN){
    exit('您无权访问。<a href="./">返回首页</a>');
}
if(IS_POST){
    category_add($error);
    $error&&display("添加栏目失败,$error");
    category_save($error);
    $error&&display("栏目修改失败,$error");
    category_delete();
}

function category_add($error='') {
    $cover=category_cover_upload(input('file','add_cover','a'));
    $result=[];
    foreach (input('post','add','a') as $k => $v) {
        $result[]=[
            'name'=> mb_strimwidth(input($v, 'name', 's'), 0, 12),
            'cover'=> input($cover, $k, 's'),
            'sort'=> input($v, 'sort', 'd'),
        ];
    }
    $result && Db::getInstance()->insert('__CATEGORY__','ssi',$result);
}

function category_cover_upload($file,$error=''){
    $upload_dir='./uploads/category';
    $up=new Upload($file, $upload_dir, date('Y-m/d'), config('PICTURE_EXT'));
    $result=$up->upload();
    $error=$up->getError();
    return $result;
}

function category_save($error='') {
    //获取待保存的数据
    $result=[];
    foreach(input('post', 'save', 'a') as $k=>$v){
        $result[]=[
            'name'=> mb_strimwidth(input($v, 'name', 's'),0,12),
            'sort'=> input($v, 'sort', 'd'),
            'id'=> abs($k),
        ];
    }
    //保存记录
    $db= Db::getInstance();
    $result && $db->update('__CATEGORY__','sii',$result,'id');
    $cover=[];
    //获取待删除照片
    $cover_del= category_cover_upload(input('file', 'save_cover', 'a'), $error);
    foreach ($cover_del as $k => $v) {
        $cover[$k] = ['cover'=>'','id'=> abs($k)];
    }
    // 获取新上传的照片
    $cover_save= category_cover_upload(input('file', 'save_cover', 'a'),$error);
    foreach ($cover_save as $k => $v) {
        $cover[$k]=['cover'=>'','id'=> abs($v)];
    }
    if($cover){
        category_cover_delete(array_keys($cover),$cover);
        $db->update('__CATEGORY__','si',$cover,'id');
    }
}

function category_delete() {
    $del= array_map('abs', input('post', 'del', 'a'));
    if($del){
        category_cover_delete($del);
        $db= Db::getInstance();
        $sql_del= implode(',', $del);
        $db->execute("DELETE FROM __CATEGORY__ WHERE `id` IN($sql_del)");
        $db->execute("UPDATE __POST__ `cid`=0 WHERE `id` IN($sql_del)");
    }
}

function category_cover_delete($cover){
    $data= Db::getInstance()->fetchAll('SELECT `cover` FROM __CATEGORY__ WHERE `id` IN('. implode(',', '}').')');
    foreach ($data as $v) {
        $path='./uploads/category/'.$v['cover'];
        is_file($path) && unlink($path);
    }
}

display();
function display($tips=null,$type=''){
    $category= category_list();
    require './view/category.html';
    exit;
}
?>