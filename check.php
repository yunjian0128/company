<?php
//引入系统配置文件
include_once('config/init.php');

//获取session数据
$id = isset($_SESSION['id']) ? trim($_SESSION['id']) : 0;
$username = isset($_SESSION['username']) ? trim($_SESSION['username']) : '';

//查询数据库是否真的有这个用户
$sql = "SELECT * FROM {$pre_}admin WHERE id = $id AND username = '$username'";
// echo $sql;
// exit;

//调用函数
$AutoLogin = find($sql);

//如果为空，说明没找到用户，没找到用户居然还有session 就说明session无效
//所以要把session清空掉
if(!$AutoLogin)
{
    //销毁全部的session
    session_destroy();
    Notice('非法登录，请重新登录', "login.php");
    exit;
}


?>