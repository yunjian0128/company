<?php
include_once('config/init.php');
include_once('check.php');

// 接收部门id
$id = isset($_GET['id']) ? trim($_GET['id']) : 0;

// 根据id查询部门是否真实存在
$sql = "SELECT * FROM {$pre_}department WHERE id = $id";
$department = find($sql);

// 当部门不存在的时候
if (!$department) {
    Notice('当前编辑的部门不存在');
    exit;
}

// 接收表单
if ($_POST) {
    // var_dump($_POST);
    // exit;

    // 根据用户名去查询一下，看是否已经存在
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';

    // 如果name为空，就需要重新填写
    if (empty($name)) {
        Notice("部门名称为空，请重新填写");
        exit;
    }

    // 数据查询
    $sql = "SELECT * FROM {$pre_}department WHERE id != $id AND name = '$name'";
    $info = find($sql);

    // 如果$info不为空，就说明重复录入了
    if ($info) {
        Notice("部门名称已经存在，请重新填写");
        exit;
    }

    $sql = "SELECT * FROM {$pre_}department WHERE id = $id AND name = '$name'";
    $info = find($sql);

    // 如果$info不为空，就说明未修改
    if ($info) {
        Notice("部门名称没有修改", "DepartmentList.php");
    }

    // 接收表单数据
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';

    // 组装数据
    $data = [
        'name' => $name
    ];

    // 数据更新
    $res = update("department", $data, "id = $id");
    if ($res) {
        Notice("编辑部门成功", "DepartmentList.php");
        exit;
    } else {
        Notice("编辑部门失败");
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
            <h1 class="page-title">编辑部门</h1>
        </div>
        <ul class="breadcrumb">
            <li><a href="index.html">Home</a> <span class="divider">/</span></li>
            <li class="active">编辑部门</li>
        </ul>
        <div class="container-fluid">
            <div class="row-fluid">

                <div class="btn-toolbar">
                    <button class="btn btn-primary" onClick="location='DepartmentList.php'">
                        <i class="icon-list"></i> 返回部门列表
                    </button>
                    <div class="btn-group"> </div>
                </div>

                <div class="well">
                    <div id="myTabContent" class="tab-content">
                        <div class="tab-pane active in" id="home">
                            <form method="post" enctype="multipart/form-data" class="form">
                                <label>部门名称</label>
                                <input type="text" name="name" placeholder="请输入部门名称" required class="input-xxlarge"
                                    value="<?php echo $department['name']; ?>" />
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