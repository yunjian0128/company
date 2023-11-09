<?php
include_once('config/init.php');
include_once('check.php');

// 接收表单
if ($_POST) {
    // var_dump($_POST);
    // exit;

    // 根据管理员名称去查询一下，看是否已经存在
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';

    // 如果name为空，就需要重新填写
    if (empty($username)) {
        Notice("管理员名称为空，请重新填写");
        exit;
    }

    // 数据查询
    $sql = "SELECT * FROM {$pre_}admin WHERE username = '$username'";
    $info = find($sql);

    // 如果$info不为空，就说明重复录入了
    if ($info) {
        Notice("管理员名称已经存在，请重新填写");
        exit;
    }

    // 接收表单其余数据
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $salt = isset($_POST['salt']) ? trim($_POST['salt']) : '';

    // 密码加密
    $password = md5($password . $salt);

    // 组装数据
    $data = [
        'username' => $username,
        'password' => $password,
        'salt' => $salt,
    ];

    // 先判断是否有上传头像
    if ($_FILES['avatar']['error'] == 0 && $_FILES['avatar']['size'] > 0) {
        $path = 'assets/uploads/';
        $success = Upload('avatar', $path);

        if (!$success['result']) {
            Notice($success['msg']);
            exit;
        }

        // 直接将路径放到数据中
        $data['avatar'] = $success['data'];
    }

    // 数据入库
    $res = add("admin", $data);
    if ($res) {
        Notice("添加管理员成功", "AdminList.php");
        exit;
    } else {
        Notice("添加管理员失败");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- 引入公共样式 -->
    <?php include_once('meta.php'); ?>

    <style>
        .preview {
            width: 170px;
            height: 170px;
            overflow: hidden;
            margin-top: 5px;
        }

        .preview img {
            width: 100%;
        }

        form {
            width: 500px;
            margin: 0 auto;
        }
    </style>

</head>

<body>
    <!-- 引入头部 -->
    <?php include_once('header.php'); ?>

    <!-- 引入菜单 -->
    <?php include_once('menu.php'); ?>

    <div class="content">
        <div class="header">
            <h1 class="page-title">添加管理员</h1>
        </div>
        <ul class="breadcrumb">
            <li><a href="index.html">Home</a> <span class="divider">/</span></li>
            <li class="active">添加管理员</li>
        </ul>
        <div class="container-fluid">
            <div class="row-fluid">

                <div class="btn-toolbar">
                    <button class="btn btn-primary" onClick="location='AdminList.php'">
                        <i class="icon-list"></i> 返回管理员列表
                    </button>
                    <div class="btn-group"> </div>
                </div>

                <div class="well">
                    <div id="myTabContent" class="tab-content">
                        <div class="tab-pane active in" id="home">
                            <form method="post" enctype="multipart/form-data" class="form">
                                <label>管理员名称</label>
                                <input type="text" name="username" placeholder="请输入管理员名称" required
                                    class="input-xxlarge" />

                                <label>管理员密码</label>
                                <input type="password" name="password" placeholder="请输入密码" required
                                    class="input-xxlarge" />

                                <label>密码盐</label>
                                <input type="password" name="salt" placeholder="请输入密码盐" required
                                    class="input-xxlarge" />

                                <label>管理员头像</label>
                                <input type="file" name="avatar" id="avatar" />

                                <div class="preview">
                                    <img src="./assets/img/170x170.gif" />
                                </div>

                                <label></label>
                                <input class="btn btn-primary" type="submit" value="提交" />
                            </form>
                        </div>
                    </div>
                </div>

                <footer>
                    <hr>
                    <p>&copy; 2017 <a href="#" target="_blank">copyright</a></p>
                </footer>

            </div>
        </div>
    </div>

</body>

</html>

<script>
    $("#avatar").change(function () {
        var avatar = $(this)[0].files[0] ? $(this)[0].files[0] : null

        //如果没有选择图片
        if (!avatar) {
            return;
        }

        //创建一个读取器
        var reader = new FileReader()

        //加载文件
        reader.readAsDataURL(avatar)

        //触发一个加载成功事件
        reader.onload = function (e) {
            //获取加载成功后的图片数据
            // console.log(e.target.result)

            //追加元素
            $(".preview").html(`<img src='${e.target.result}' />`)
        }
    })
</script>