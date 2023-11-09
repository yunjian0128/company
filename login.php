<?php
//引入系统配置文件(链接数据库操作)
include_once('config/init.php');

//判断是否有表单数据提交

//接收action参数
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

//退出的流程
if ($action == "logout") {
    //清空session 
    session_destroy();

    //重定向
    header("Location:login.php");
    // Notice('退出成功', 'login.php');
    exit;
}

//判断如果有登录，就无须重复登录
$id = isset($_SESSION['id']) ? trim($_SESSION['id']) : 0;
$name = isset($_SESSION['username']) ? trim($_SESSION['username']) : '';

//id和用户名都不为空，就说明有可能登录
if ($id && !empty($name)) {
    @header("Location:index.php");
    exit;
}

//判断是否有表单数据提交
if ($_POST) {
    //如果有数据就接收
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $vercode = isset($_POST['vercode']) ? trim($_POST['vercode']) : '';

    //打印一下
    // var_dump($username, $password);
    // exit;

    if (strtolower($vercode) != strtolower($_SESSION['vercode'])) {
        Notice('验证码有误，请重新输入');
        // 清空输入的验证码

        exit;
    }

    //先判断用户是否存在，封装sql语句
    $sql = "SELECT * FROM {$pre_}admin WHERE username = '$username'";

    //调用封装函数
    $admin = find($sql);

    //用户不存在
    if (!$admin) {
        Notice("用户不存在");
        exit;
    }

    //用户存在在验证密码
    $salt = $admin['salt'];

    // 先给自己做一个密码 更新到数据库中
    // echo md5($password . $salt);
    // exit;


    // 输入的密码加密
    $password = md5($password . $salt);

    //如果密码加密的结果 不等于数据库中存的加密密码
    if ($password != $admin['password']) {
        Notice("密码错误");
        exit;
    }

    //将用户信息，记录到session中
    $_SESSION['id'] = $admin['id'];
    $_SESSION['username'] = $admin['username'];

    Notice("登录成功", "index.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- 引入公共样式 -->
    <?php include_once('meta.php'); ?>
</head>

<body>
    <div class="navbar">
        <div class="navbar-inner">
            <a class="brand"><span class="second">通讯录</span></a>
        </div>
    </div>

    <div class="row-fluid">
        <div class="dialog">
            <div class="block">
                <p class="block-heading">登录</p>
                <div class="block-body">
                    <form method="post">
                        <label>用户名</label>
                        <input type="text" name="username" placeholder="请输入用户名" required class="span12">

                        <label>密码</label>
                        <input type="password" name="password" placeholder="请输入密码" required class="span12">

                        <label>验证码</label>
                        <input type="text" name="vercode" placeholder="请输入验证码" required class="span12">

                        <img src="vercode.php" onclick="this.src=`vercode.php?random=${Math.random()}`" />

                        <button type="submit" class="btn btn-primary pull-right">登录</button>
                        <div class="clearfix"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>