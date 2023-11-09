<?php
include_once('config/init.php');
include_once('check.php');

// 接收管理员id
$id = isset($_GET['id']) ? trim($_GET['id']) : 0;

// 根据id查询管理员是否真实存在
$sql = "SELECT * FROM {$pre_}admin WHERE id = $id";
$admin = find($sql);

// 当管理员不存在的时候
if (!$admin) {
    Notice('当前编辑的管理员不存在');
    exit;
}

// 接收表单
if ($_POST) {
    // var_dump($_POST);
    // exit;

    // 接收表单数据
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $salt = isset($_POST['salt']) ? trim($_POST['salt']) : '';

    // 声明一个数组
    $data = [];

    // 如果为空，就需要重新填写
    if (empty($username)) {
        Notice("管理员名字为空，请重新填写");
        exit;
    }

    // 查询管理员名字
    $sql = "SELECT * FROM {$pre_}admin WHERE id != $id AND username = '$username'";
    $info = find($sql);

    // 如果$info不为空，就说明重复录入了
    if ($info) {
        Notice("管理员名字已经存在，请重新填写");
        exit;
    }

    $data["username"] = $username;

    // 如果password和salt均为空，就代表二者都不做修改
    if (empty($password) || empty($salt)) {
        $data += [
            "password" => $admin["password"],
            "salt" => $admin["salt"]
        ];
    }

    // 如果单独修改salt，不允许
    if (empty($password) && !empty($salt)) {
        Notice("密码未修改，不允许修改盐");
        exit;
    }

    // 如果password和salt都不为空
    if (!empty($password) && !empty($salt)) {
        $password = md5($password . $salt);
        $data += [
            "password" => $password,
            "salt" => $salt
        ];
    }

    // 如果单独修改password
    if (!empty($password) && empty($salt)) {
        $password = md5($password . $admin["salt"]);
        $data += [
            "password" => $password,
            "salt" => $admin["salt"]
        ];
    }

    // 头像默认是原来的头像路径
    $data["avatar"] = $admin["avatar"];
    $data["id"] = $admin["id"];

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

    // 如果data和admin各项内容一致，则表明管理员信息未修改
    if ($data == $admin) {
        Notice("管理员信息未修改", "AdminList.php");
        exit;
    }

    // var_dump($data);
    // exit;

    // 数据更新
    $res = update("admin", $data, "id = $id");
    if ($res) {
        Notice("编辑管理员成功", "AdminList.php");
        exit;
    } else {
        Notice("编辑管理员失败");
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
                                <input type="text" name="username" placeholder="请输入管理员名称" class="input-xxlarge"
                                    value="<?php echo $admin['username']; ?>" />

                                <label>管理员密码</label>
                                <input type="password" name="password" placeholder="请输入密码" class="input-xxlarge" />

                                <label>密码盐</label>
                                <input type="password" name="salt" placeholder="请输入密码盐" class="input-xxlarge" />

                                <label>管理员头像</label>
                                <input type="file" name="avatar" id="avatar" />

                                <div class="preview">
                                    <?php if (!empty($admin['avatar']) && is_file("./" . $admin['avatar'])) { ?>
                                        <img src="./<?php echo $admin['avatar']; ?>" />
                                    <?php } else { ?>
                                        <img src="./assets/img/170x170.gif" />
                                    <?php } ?>
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