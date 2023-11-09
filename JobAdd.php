<?php
include_once('config/init.php');
include_once('check.php');

// 接收表单
if ($_POST) {
    // var_dump($_POST);
    // exit;

    // 根据职位名称去查询一下，看是否已经存在
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $depname = isset($_POST['depname']) ? trim($_POST['depname']) : '';

    // 如果有任何一个为空，就需要新填写
    if (empty($name) || empty($depname)) {
        Notice("职位名称或部门名称为空，请重新填写");
        exit;
    }

    // 根据输入的部门名称去查询已有的部门名称，确定部门是否存在 不存在就不能添加
    $sql = "SELECT * FROM {$pre_}department where name = '$depname'";
    $info = find($sql);

    // 如果$info为空，就说明部门不存在
    if (!$info) {
        Notice("部门名称不存在，请重新填写");
        exit;
    }

    // 把得到的部门ID一起添加到职位表中
    $depid = $info['id'];

    // 数据查询
    $sql = "SELECT * FROM {$pre_}job WHERE name = '$name' AND depid = '$depid'";
    $info = find($sql);

    // 如果$info不为空，就说明重复录入了
    if ($info) {
        Notice("职位名称已经存在，请重新填写");
        exit;
    }

    // 组装数据
    $data = [
        'name' => $name,
        'depid' => $depid
    ];

    // 数据入库
    $res = add("job", $data);
    if ($res) {
        Notice("添加职位成功", "JobList.php");
        exit;
    } else {
        Notice("添加职位失败");
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
            <h1 class="page-title">添加职位</h1>
        </div>
        <ul class="breadcrumb">
            <li><a href="index.html">Home</a> <span class="divider">/</span></li>
            <li class="active">添加职位</li>
        </ul>
        <div class="container-fluid">
            <div class="row-fluid">

                <div class="btn-toolbar">
                    <button class="btn btn-primary" onClick="location='JobList.php'">
                        <i class="icon-list"></i> 返回职位列表
                    </button>
                    <div class="btn-group"> </div>
                </div>

                <div class="well">
                    <div id="myTabContent" class="tab-content">
                        <div class="tab-pane active in" id="home">
                            <form method="post" enctype="multipart/form-data" class="form">
                                <label>职位名称</label>
                                <input type="text" name="name" placeholder="请输入职位名称" required class="input-xxlarge" />

                                <label>部门名称</label>
                                <input type="text" name="depname" placeholder="请输入部门名称" required
                                    class="input-xxlarge" />

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