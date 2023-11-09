<?php
//引入系统配置文件
include_once('config/init.php');
include_once('check.php');

// 接收操作参数
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

// 判断是否是删除操作
if ($action == "delete") {
    // 封装一个返回结果
    $success = array(
        'result' => false,
        'msg' => '删除失败'
    );

    // 接收要删除的id
    $ids = isset($_POST['ids']) ? trim($_POST['ids']) : 0;

    // 先查询出要删除的数据
    $sql = "SELECT * FROM {$pre_}admin WHERE id IN ($ids)";
    $deletelist = all($sql);

    if (empty($deletelist)) {
        $result['result'] = false;
        $result['msg'] = '暂无删除的数据';

        // 返回json数据
        echo json_encode($success);
        exit;
    }

    // 先从二维数组中，抽离出指定的字段，放到一个一位数组
    $avatar = array_column($deletelist, 'avatar');

    //将数组中的空元素去除 去空
    $avatar = array_filter($avatar);

    // 先删除数据，再删除图片
    $where = "id IN ($ids)";
    $affect = delete('admin', $where);

    if ($affect) {
        //删除成功
        //先判断图片的数组不是空再去删除
        if (!empty($avatar)) {
            //循环删除，必须要图片真实存在，再去删除， 不存在
            foreach ($avatar as $file) {
                //is_file 判断文件是否存在，如果存在返回true
                is_file("./" . $file) && @unlink("./" . $file);
            }
        }

        $success['result'] = true;
        $success['msg'] = '删除成功';
    } else {
        //删除失败
        $success['result'] = false;
        $success['msg'] = '删除失败';
    }

    //返回结果
    echo json_encode($success);
    exit;
}

// 当前页码
$page = isset($_GET['page']) ? trim($_GET['page']) : 1;

// 每页显示多少条
$limit = 2;

// 中间显示多少个页码数
$size = 5;

// sql查询数据总数
$sql = "SELECT COUNT(id) AS c FROM {$pre_}admin";
$count = find($sql);
// var_dump($count);
$count = isset($count['c']) ? trim($count['c']) : 0;

// 调用分页函数
$html = page($page, $count, $limit, $size, 'black2');

// 偏移量
$offset = ($page - 1) * $limit;

//查询管理员数据
$sql = "SELECT * FROM {$pre_}admin ORDER BY id DESC LIMIT $offset,$limit";

//调用函数
$list = all($sql);

// var_dump($list);
// exit;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- 引入公共样式 -->
    <?php include_once('meta.php'); ?>

    <!-- 引入分页样式 -->
    <link rel="stylesheet" href="assets/css/page.css" />
</head>

<body>
    <!-- 引入头部 -->
    <?php include_once('header.php'); ?>

    <!-- 引入菜单 -->
    <?php include_once('menu.php'); ?>

    <div class="content">
        <div class="header">
            <h1 class="page-title">管理员列表</h1>
        </div>
        <ul class="breadcrumb">
            <li><a href="index.php">Home</a> <span class="divider">/</span></li>
            <li class="active">管理员列表</li>
        </ul>

        <div class="container-fluid">
            <div class="row-fluid">
                <div class="btn-toolbar">
                    <button class="btn btn-primary" onClick="location='AdminAdd.php'">
                        <i class="icon-plus"></i>添加管理员
                    </button>
                </div>
                <div class="well">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="toggle" />
                                </th>
                                <th>ID</th>
                                <th>姓名</th>
                                <!-- <th>密码</th>
                                <th>密码盐</th> -->
                                <th>头像</th>
                                <th style="width: 60px;">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($list as $item) { ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="list" value="<?php echo $item['id']; ?>" />
                                    </td>
                                    <td>
                                        <?php echo $item['id']; ?>
                                    </td>
                                    <td>
                                        <?php echo $item['username']; ?>
                                    </td>
                                    <!-- <td>
                                        <?php echo $item['password']; ?>
                                    </td>
                                    <td>
                                        <?php echo $item['salt']; ?>
                                    </td> -->
                                    <?php if (is_file("./" . $item['avatar'])) { ?>
                                        <td>
                                            <a style="display: block;width:100px;height:100px"
                                                href="<?php echo "./" . $item['avatar']; ?>" target="_blank">
                                                <img src="<?php echo "./" . $item['avatar']; ?>">
                                            </a>
                                        </td>
                                    <?php } else { ?>
                                        <td>暂无头像</td>
                                    <?php } ?>
                                    <td>
                                        <a href="AdminEdit.php?id=<?php echo $item['id']; ?>">
                                            <i class="icon-pencil"></i></a>
                                        <a class="delone" data-ids="<?php echo $item['id']; ?>" href="#myModal"
                                            role="button" data-toggle="modal">
                                            <i class="icon-remove"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td colspan="20">
                                    <!-- 点击是一个锚点 href = id属性 -->
                                    <a class="btn btn-success delall" href="#myModal" role="button" data-toggle="modal">
                                        <i class="icon-remove"></i> 批量删除
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php echo $html; ?>

                <div class="modal small hide fade" id="myModal" tabindex="-1" role="dialog"
                    aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3 id="myModalLabel">删除提示框</h3>
                    </div>
                    <div class="modal-body">
                        <p class="error-text">
                            <i class="icon-warning-sign modal-icon"></i>是否确认删除？
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
                        <button class="btn btn-danger confirm" data-dismiss="modal">确认删除</button>
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
    // 全选
    // 获取元素
    var toggle = document.getElementById('toggle');
    var list = document.getElementsByName('list');
    // 给全选按钮绑定点击事件
    toggle.onclick = function () {
        // 获取全选按钮的状态
        var status = toggle.checked;
        // 循环遍历所有的复选框
        for (var i = 0; i < list.length; i++) {
            // 将全选按钮的状态赋值给所有的复选框
            list[i].checked = status;
        }
    }
    for (var i = 0; i < list.length; i++) {
        // 给每一个复选框绑定点击事件
        list[i].onclick = function () {
            // 获取所有复选框的数量
            var len = list.length;
            // 定义一个变量，用来记录选中的复选框的数量
            var num = 0;
            // 循环遍历所有的复选框
            for (var i = 0; i < len; i++) {
                // 判断复选框是否选中
                if (list[i].checked) {
                    // 如果选中，那么就让num自增
                    num++;
                }
            }
            // 判断选中的复选框的数量是否等于所有复选框的数量
            if (num == len) {
                // 如果相等，那么就让全选按钮选中
                toggle.checked = true;
            } else {
                // 如果不相等，那么就让全选按钮不选中
                toggle.checked = false;
            }
        }
    }

    // // 单条删除
    // 全局变量
    var ids = [];
    $('.delone').click(function () {
        //拿到点击元素身上ids属性 删除id

        //data方法 获取元素身上的 data- 开头的自定义属性
        var id = $(this).data('ids')

        //将拿到的值塞到全局数组中
        ids = [id]
        // console.log(ids)
    })

    $('.delall').click(function () {
        //清空数组
        ids = []

        //拿到所有选中的复选框
        var list = $('input[name="list"]:checked')

        if (list.length <= 0) {
            alert('未选择删除的元素')
            return false;
        }

        //循环遍历所有的复选框
        list.each(function (index, item) {
            //往数组里面追加元素
            ids.push(item.value)
        })

        // console.log(ids)
    })

    //确认删除
    $('.confirm').click(function () {
        //手动关闭模态框
        $("#myModal").modal('hide')

        //将数组转换为字符串
        var str = ids.join(',')
        // console.log(str)

        //发送ajax请求
        $.ajax({
            type: 'post', // get或post
            dataType: 'json', // 预期服务器返回的数据类型
            data: {
                action: 'delete',
                ids: str
            },
            success: function (success) {
                if (success.result) {
                    location.href = "AdminList.php";
                } else {
                    alert(success.msg)
                }
                return false;
            },
            error: function (error) {
                console.log(error)
            }
        })
    })

</script>