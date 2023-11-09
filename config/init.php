<?php
// 开启session
session_start();

//声明编码
@header("Content-Type:text/html;charset=utf-8");

//链接数据库 全局变量
$link = mysqli_connect("localhost", "root", "000000");

//判断是否链接成功
if (!$link) {
    echo "【链接数据库失败】：" . mysqli_connect_error();
    exit;
}

//选择数据库
$select = mysqli_select_db($link, "company");

if (!$select) {
    echo "链接数据库失败";
    exit;
}

//设置编码
mysqli_set_charset($link, "UTF8");

//设置一个全局的表前缀变量
$pre_ = "pre_";

//引入函数库文件 不用每个页面中都重复引入了
include_once('helper.php');

?>