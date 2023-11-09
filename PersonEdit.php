<?php
include_once('config/init.php');
include_once('check.php');

//接收用户id
$id = isset($_GET['id']) ? trim($_GET['id']) : 0;

//根据id查询员工是否真实存在
$sql = "SELECT * FROM {$pre_}person WHERE id = $id";
$person = find($sql);

//当员工不存在的时候
if (!$person) {
    Notice('当前编辑的员工不存在');
    exit;
}

// var_dump($person);
// exit;

//先查询出所有的部门数据
$sql = "SELECT * FROM {$pre_}department";
$deplist = all($sql);

//找到当前员工所在部门的所有职位
$depid = isset($person['depid']) ? $person['depid'] : 0;
$sql = "SELECT * FROM {$pre_}job WHERE depid = $depid";
$joblist = all($sql);

//接收ajax给的参数
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

//确保action是否有传递，如果有传递action就说明当前的请求是ajax异步请求
if ($action == "job") {
    $result = [
        'result' => false,
        'msg' => '',
        'data' => []
    ];
    //才去接收部门id
    $depid = isset($_GET['depid']) ? trim($_GET['depid']) : 0;

    //直接找该部门下面的职位
    $sql = "SELECT * FROM {$pre_}job WHERE depid = $depid";
    $joblist = all($sql);

    //判断是否为空
    if (empty($joblist)) {
        $result['result'] = false;
        $result['msg'] = '该部门下暂无职位';
    } else {
        $result['result'] = true;
        $result['msg'] = '有职位';
        $result['data'] = $joblist;
    }

    //最后将数据返回给ajax 返回json
    echo json_encode($result);
    exit;
}

//接收表单
if ($_POST) {
    // var_dump($_POST);
    // exit;

    //要根据邮箱和手机号去查询一下，看是否存在
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    //只要有一个为空，就告诉他重新填写
    if (empty($mobile) || empty($email)) {
        Notice("手机号或者邮箱为空，请重新填写");
        exit;
    }

    //数据查询 手机号和邮箱只要有一个重复的 就不能录入 但是要除了自己以外 逻辑或 || OR
    $sql = "SELECT * FROM {$pre_}person WHERE id != $id AND (mobile = '$mobile' OR email = '$email')";

    //只要是查询出一条就说明重复录入了
    $check = find($sql);

    //如果不为空，就说明查询到了，查询到就不能录入了
    if (!empty($check)) {
        Notice('手机号或者邮箱已存在，请重新填写');
        exit;
    }

    //组装数据
    $data = [
        'name' => isset($_POST['name']) ? trim($_POST['name']) : '',
        'sex' => isset($_POST['sex']) ? trim($_POST['sex']) : '0',
        'mobile' => $mobile,
        'email' => $email,
        'address' => isset($_POST['address']) ? trim($_POST['address']) : '',
        'depid' => isset($_POST['depid']) ? trim($_POST['depid']) : '',
        'jobid' => isset($_POST['jobid']) ? trim($_POST['jobid']) : '',
    ];

    //入职时间
    $createtime = isset($_POST['createtime']) ? trim($_POST['createtime']) : date("Y-m-d", time());

    //将时间转换为时间戳
    $data['createtime'] = strtotime($createtime);

    //地区
    $region = isset($_POST['region']) ? trim($_POST['region']) : '';

    //地区不为空 说明有选择
    if (!empty($region)) {
        //将数据结构(字符串) 转换为数组  /
        $arr = explode('/', $region);

        //判断这个数组选择了几个
        $data['province'] = isset($arr[0]) ? trim($arr[0]) : NULL;
        $data['city'] = isset($arr[1]) ? trim($arr[1]) : NULL;
        $data['district'] = isset($arr[2]) ? trim($arr[2]) : NULL;
    }
    $data['id'] = $person['id'];
    $data['avatar'] = $person['avatar'];

    //头像先判断是否有上传图片
    if ($_FILES['avatar']['error'] == 0 && $_FILES['avatar']['size'] > 0) {
        $path = 'assets/uploads/';
        $success = Upload('avatar', $path);

        if (!$success['result']) {
            Notice($success['msg']);
            exit;
        }

        //直接将路径放到数据中
        $data['avatar'] = $success['data'];
    }

    // 如果data和person各项内容一致，则表明员工信息未修改
    if ($data == $person) {
        Notice('员工信息未修改', 'PersonList.php');
        exit;
    }

    //更新
    $res = update("person", $data, "id = $id");

    if ($res) {
        //判断是否有上传新头像，如果有就要把就图像删掉
        if (isset($data['avatar'])) {
            //员工头像不为空,而且还得是真实存在，在删除
            if (!empty($person['avatar']) && is_file("./" . $person['avatar'])) {
                @unlink('./' . $person['avatar']);
            }
        }

        Notice("编辑员工成功", "PersonList.php");
        exit;
    } else {
        Notice("编辑员工失败");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- 引入公共样式 -->
    <?php include_once('meta.php'); ?>

    <!-- 城市联动的插件 -->
    <link rel="stylesheet" href="assets/plugins/city-picker/city-picker.css" />
    <script src="assets/plugins/city-picker/city-picker.data.min.js"></script>
    <script src="assets/plugins/city-picker/city-picker.min.js"></script>
    <style>
        .region {
            position: relative;
            display: block;
            width: 108.5%;
            height: 30px;
            margin-bottom: 10px;
            border: 1px solid #aaa !important;
            border-radius: 2px;
        }

        .region .city-picker-span {
            margin-left: 5px !important;
        }

        .region .city-picker-span>.placeholder,
        .region .city-picker-span>.title,
        .region .city-picker-span>.title>span {
            color: #aaa !important;
            font-weight: normal !important;
        }

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
            <h1 class="page-title">编辑员工</h1>
        </div>
        <ul class="breadcrumb">
            <li><a href="index.php">Home</a> <span class="divider">/</span></li>
            <li class="active">编辑员工</li>
        </ul>

        <div class="container-fluid">
            <div class="row-fluid">
                <div class="btn-toolbar">
                    <button class="btn btn-primary" onClick="location='PersonList.php'"><i class="icon-list"></i>
                        返回员工列表</button>
                </div>

                <div class="well">
                    <div id="myTabContent" class="tab-content">
                        <div class="tab-pane active in" id="home">
                            <form method="post" enctype="multipart/form-data">
                                <label>员工名称</label>
                                <input type="text" name="name" placeholder="请输入员工名称" required class="input-xxlarge"
                                    value="<?php echo $person['name']; ?>" />

                                <label>手机号</label>
                                <input type="number" name="mobile" placeholder="请输入手机号" required class="input-xxlarge"
                                    value="<?php echo $person['mobile']; ?>" />

                                <label>邮箱</label>
                                <input type="email" name="email" placeholder="请输入邮箱" required class="input-xxlarge"
                                    value="<?php echo $person['email']; ?>" />

                                <label>入职时间</label>
                                <input type="date" name="createtime" placeholder="请选择入职时间" required
                                    class="input-xxlarge" value="<?php echo date("Y-m-d", $person['createtime']); ?>" />

                                <label>所在地区</label>
                                <div class="region">
                                    <input type="text" name="region" id="region" readonly data-responsive="true"
                                        placeholder="请选择所在地区" class="input-xxlarge" />
                                </div>

                                <label>地址</label>
                                <input type="text" name="address" placeholder="请输入地址" class="input-xxlarge"
                                    value="<?php echo $person['address']; ?>" />

                                <label>性别</label>
                                <select name="sex" class="input-xlarge">
                                    <option value="0" <?php echo $person['sex'] == '0' ? 'selected' : ''; ?>>保密</option>
                                    <option value="1" <?php echo $person['sex'] == '1' ? 'selected' : ''; ?>>男</option>
                                    <option value="2" <?php echo $person['sex'] == '2' ? 'selected' : ''; ?>>女</option>
                                </select>

                                <label>所在部门</label>
                                <select name="depid" required class="input-xlarge">
                                    <option value="">请选择</option>
                                    <?php foreach ($deplist as $item) { ?>
                                        <option <?php echo $person['depid'] == $item['id'] ? "selected" : ""; ?>
                                            value="<?php echo $item['id']; ?>">
                                            <?php echo $item['name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <label>所在职位</label>
                                <select name="jobid" required class="input-xlarge">
                                    <option value="">请选择</option>
                                    <?php foreach ($joblist as $item) { ?>
                                        <option <?php echo $person['jobid'] == $item['id'] ? 'selected' : ''; ?>
                                            value="<?php echo $item['id']; ?>">
                                            <?php echo $item['name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <label>员工头像</label>
                                <input type="file" name="avatar" id="avatar" />

                                <div class="preview">
                                    <?php if (!empty($person['avatar']) && is_file("./" . $person['avatar'])) { ?>
                                        <img src="./<?php echo $person['avatar']; ?>" />
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
    //给城市插件初始化
    $('#region').citypicker({
        province: `<?php echo $person['province']; ?>`,
        city: `<?php echo $person['city']; ?>`,
        district: `<?php echo $person['district']; ?>`,
    })

    //给部门绑定改变事件
    $("select[name=depid]").change(function () {
        //先获取到所切换的部门id,为空不查询职位
        var depid = $(this).val()

        //为空
        if (!depid) {
            alert('请选择部门')
            return false;
        }

        //不为空，我就要去请求职位的数据，要向后端php，发送异步请求,Ajax
        $.ajax({
            type: 'get', //请求方法
            dataType: 'json',
            data: { depid: depid, action: 'job' }, //请求的数据
            success: function (success) //请求成功的回调函数
            {
                //没有数据的时候
                if (!success.result) {
                    alert(success.msg)
                    return false;
                }

                //如果有数据,将数据渲染到下拉框里面去
                var html = '';

                for (var item of success.data) {
                    //拼接
                    html += `<option value="${item.id}">${item.name}</option>`
                }

                //将拼接的结果塞到select下拉框里面
                $("select[name=jobid]").html(html)
            },
            error: function (error) {
                //请求出错
                console.log(error)
            }
        })
    })

    //给图片选择绑定一个改变事件
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