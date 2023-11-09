<?php
include_once('config/init.php');
include_once('check.php');

// 先查询出所有的部门数据
$sql = "SELECT * FROM {$pre_}department";
$deplist = all($sql);

// 接收ajax给的参数
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

// 确保action是否有传递，如果有传递action就说明当前的请求是ajax异步请求
if ($action == "job") {
  $result = [
    'result' => false,
    'msg' => '',
    'data' => []
  ];
  // 才去接收部门id
  $depid = isset($_GET['depid']) ? trim($_GET['depid']) : 0;

  // 直接找该部门下面的职位
  $sql = "SELECT * FROM {$pre_}job WHERE depid = $depid";
  $joblist = all($sql);


  // 判断是否为空
  if (empty($joblist)) {
    $result['result'] = false;
    $result['msg'] = '该部门下暂无职位';
  } else {
    $result['result'] = true;
    $result['msg'] = '有职位';
    $result['data'] = $joblist;
  }

  // 最后将数据返回给ajax 返回json
  echo json_encode($result);
  exit;
}

// 接收表单
if ($_POST) {
  // var_dump($_POST);
  // exit;

  // 要根据邮箱和手机号去查询一下，看是否存在
  $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';

  // 只要有一个为空，就告诉他重新填写
  if (empty($mobile) || empty($email)) {
    Notice("手机号或者邮箱为空，请重新填写");
    exit;
  }

  // 数据查询 手机号和邮箱只要有一个重复的 就不能录入 逻辑或 || OR
  $sql = "SELECT * FROM {$pre_}person WHERE mobile = '$mobile' OR email = '$email'";

  // 只要是查询出一条就说明重复录入了
  $check = find($sql);

  // 如果不为空，就说明查询到了，查询到就不能录入了
  if (!empty($check)) {
    Notice("手机号或者邮箱已经存在，请重新填写");
    exit;
  }

  // 组装数据
  $data = [
    'name' => isset($_POST['name']) ? trim($_POST['name']) : '',
    'sex' => isset($_POST['sex']) ? trim($_POST['sex']) : '0',
    'mobile' => $mobile,
    'email' => $email,
    'address' => isset($_POST['address']) ? trim($_POST['address']) : '',
    'depid' => isset($_POST['depid']) ? trim($_POST['depid']) : '',
    'jobid' => isset($_POST['jobid']) ? trim($_POST['jobid']) : '',
  ];

  // 入职时间
  $createtime = isset($_POST['createtime']) ? trim($_POST['createtime']) : date("Y-m-d", time());

  // 将时间转换为时间戳
  $data['createtime'] = strtotime($createtime);

  // 地区
  $region = isset($_POST['region']) ? trim($_POST['region']) : '';

  // 地区不为空 说明有选择
  if (!empty($region)) {
    // 将地区转换为数组
    $region = explode('/', $region);

    // 将省市区分别存储到数组中
    $data['province'] = isset($region[0]) ? $region[0] : NULL;
    $data['city'] = isset($region[1]) ? $region[1] : NULL;
    $data['district'] = isset($region[2]) ? $region[2] : NULL;
  }

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

  // 录入
  $res = add("person", $data);

  if ($res) {
    Notice("添加员工成功", "PersonList.php");
    exit;
  } else {
    Notice("添加员工失败");
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
      <h1 class="page-title">添加员工</h1>
    </div>
    <ul class="breadcrumb">
      <li><a href="index.html">Home</a> <span class="divider">/</span></li>
      <li class="active">添加员工</li>
    </ul>

    <div class="container-fluid">
      <div class="row-fluid">

        <div class="btn-toolbar">
          <button class="btn btn-primary" onClick="location='PersonList.php'"><i class="icon-list"></i> 返回员工列表</button>
          <div class="btn-group">
          </div>
        </div>

        <div class="well">
          <div id="myTabContent" class="tab-content">
            <div class="tab-pane active in" id="home">
              <form method="post" enctype="multipart/form-data" class="form">
                <label>员工名称</label>
                <input type="text" name="name" placeholder="请输入员工名称" required class="input-xxlarge" />

                <label>手机号</label>
                <input type="number" name="mobile" placeholder="请输入手机号" required class="input-xxlarge" />

                <label>邮箱</label>
                <input type="email" name="email" placeholder="请输入邮箱" required class="input-xxlarge" />

                <label>入职时间</label>
                <input type="date" name="createtime" placeholder="请选择入职时间" required class="input-xxlarge"
                  value="<?php echo date("Y-m-d", time()); ?>" />

                <label>地址</label>
                <input type="text" name="address" placeholder="请输入地址" class="input-xxlarge" />

                <label>所在地区</label>
                <div class="region">
                  <input type="text" name="region" id="region" readonly data-responsive="true" placeholder="请选择所在地区"
                    class="input-xxlarge" />
                </div>

                <label>性别</label>
                <select name="sex" class="input-xlarge">
                  <option value="0">保密</option>
                  <option value="1">男</option>
                  <option value="2">女</option>
                </select>

                <label>所在部门</label>
                <select name="depid" required class="input-xlarge">
                  <option value="">请选择</option>
                  <?php foreach ($deplist as $item) { ?>
                    <option value="<?php echo $item['id']; ?>">
                      <?php echo $item['name']; ?>
                    </option>
                  <?php } ?>
                </select>

                <label>所在职位</label>
                <select name="jobid" required class="input-xlarge">
                  <option value="">请选择</option>
                </select>

                <label>员工头像</label>
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
  // 给城市插件初始化
  $('#region').citypicker();


  // 选择部门后，动态加载职位
  $("select[name=depid]").change(function () {
    // 获取部门id
    var depid = $(this).val();

    // 为空不查询职位
    if (!depid) {
      alert('请选择部门');
      return;
    }

    // 发送ajax请求，获取职位数据
    $.ajax({
      type: 'get',
      data: {
        depid: depid,
        action: 'job'
      },
      dataType: 'json',
      success: function (success) {
        // console.log(success);
        // 没有数据
        if (!success.result) {
          alert(success.msg);
          return false;
        }

        // 有数据
        var html = '';
        for (var item of success.data) {
          // console.log(item);
          //拼接
          html += `<option value="${item.id}">${item.name}</option>`
        }

        // 将数据显示到页面中
        $('select[name=jobid]').html(html);
      },
      error: function (error) {
        console.log(error);
      }
    });
  });

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