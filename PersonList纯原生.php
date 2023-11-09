<?php
//引入系统配置文件
include_once('config/init.php');
include_once('check.php');

//查询员工数据
// $sql = "SELECT * FROM {$pre_}person ORDER BY createtime DESC";

// 当前在第几页 能接收到就拿，接收不到就给1 默认第一页
$page = isset($_GET['page']) ? trim($_GET['page']) : 1;
$limit = 2; //每页显示多少条数据

// 查询出总共有多少条数据
$sql = "SELECT count(id) AS c FROM {$pre_}person";
$count = find($sql);
$count = isset($count['c']) ? $count['c'] : 0;

// 计算出总共有多少页
$pages = ceil($count / $limit); // ceil() 函数向上舍入为最接近的整数。

// 组装一个html的分页结构
$html = "<ul>";

// 首页判断
if ($page <= 1) {
    // 不能点击
    $html .= "<li><a>首页</a></li>";
    $html .= "<li><a>上一页</a></li>";
} else {
    // 可以点击
    $prev = $page - 1;
    $html .= "<li><a href='?page=1'>首页</a></li>";
    $html .= "<li><a href='?page=$prev'>上一页</a></li>";
}

// 中间循环页码数
for ($i = 1; $i <= $pages; $i++) {
    // 判断当前在哪一页，如果在某一页就不能点击
    if ($page == $i) {
        $html .= "<li><a>$i</a></li>";
    } else {
        $html .= "<li><a href='?page=$i'>$i</a></li>";
    }
}

// 尾页
if ($page >= $pages) {
    $html .= "<li><a>下一页</a></li>";
    $html .= "<li><a>尾页</a></li>";
} else {
    $next = $page + 1;
    $html .= "<li><a href='?page=$next'>下一页</a></li>";
    $html .= "<li><a href='?page=$pages'>尾页</a></li>";
}

$html .= "</ul>";

// 偏移量
$offset = ($page - 1) * $limit;

//链表查询
$sql = "SELECT person.*,dep.name AS depname,job.name AS jobname FROM {$pre_}person AS person 
LEFT JOIN {$pre_}department AS dep ON person.depid = dep.id 
LEFT JOIN {$pre_}job AS job ON person.jobid = job.id LIMIT $offset,$limit";
// LEFT JION是表示左连接，左连接是以左边的表为主，右边的表为辅，
// 以左边的表为基础，右边的表为补充，如果右边的表没有对应的数据，那么就显示null
// ON是表示连接的条件，如果没有连接条件，
// 那么就会把左边的表的数据和右边的表的数据进行笛卡尔积的组合

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
</head>

<body>
    <!-- 引入头部 -->
    <?php include_once('header.php'); ?>

    <!-- 引入菜单 -->
    <?php include_once('menu.php'); ?>

    <div class="content">
        <div class="header">
            <h1 class="page-title">员工列表</h1>
        </div>
        <ul class="breadcrumb">
            <li><a href="index.php">Home</a> <span class="divider">/</span></li>
            <li class="active">员工列表</li>
        </ul>

        <div class="container-fluid">
            <div class="row-fluid">
                <div class="btn-toolbar">
                    <button class="btn btn-primary" onClick="location='add.html'"><i class="icon-plus"></i>添加员工</button>
                </div>
                <div class="well">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="toggle" />
                                </th>
                                <th>工号</th>
                                <th>姓名</th>
                                <th>性别</th>
                                <th>手机号</th>
                                <th>邮箱</th>
                                <th>头像</th>
                                <th>部门</th>
                                <th>职位</th>
                                <th>入职时间</th>
                                <th style="width: 60px;">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($list as $item) { ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="list" />
                                    </td>
                                    <td>
                                        <?php echo $item['id']; ?>
                                    </td>
                                    <td>
                                        <?php echo $item['name']; ?>
                                    </td>
                                    <?php if ($item['sex'] == '0') { ?>
                                        <td>保密</td>
                                    <?php } else if ($item['sex'] == '1') { ?>
                                            <td>男</td>
                                    <?php } else if ($item['sex'] == '2') { ?>
                                                <td>女</td>
                                    <?php } else { ?>
                                                <td></td>
                                    <?php } ?>
                                    <td>
                                        <?php echo $item['mobile']; ?>
                                    </td>
                                    <td>
                                        <?php echo $item['email']; ?>
                                    </td>
                                    <?php if (is_file("." . $item['avatar'])) { ?>
                                        <td>
                                            <a style="display: block;width:100px;height:100px"
                                                href="<?php echo "." . $item['avatar']; ?>" target="_blank">
                                                <img src="<?php echo "." . $item['avatar']; ?>">
                                            </a>
                                        </td>
                                    <?php } else { ?>
                                        <td>暂无头像</td>
                                    <?php } ?>
                                    <td>
                                        <?php echo $item['depname']; ?>
                                    </td>
                                    <td>
                                        <?php echo $item['jobname']; ?>
                                    </td>
                                    <td>
                                        <?php echo date("Y-m-d", $item['createtime']); ?>
                                    </td>
                                    <td>
                                        <a href="add.html"><i class="icon-pencil"></i></a>
                                        <a href="#myModal" role="button" data-toggle="modal"><i class="icon-remove"></i></a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    <?php echo $html; ?>
                </div>

                <div class="modal small hide fade" id="myModal" tabindex="-1" role="dialog"
                    aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3 id="myModalLabel">Delete Confirmation</h3>
                    </div>
                    <div class="modal-body">
                        <p class="error-text"><i class="icon-warning-sign modal-icon"></i>Are you sure you want to
                            delete the user?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                        <button class="btn btn-danger" data-dismiss="modal">Delete</button>
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
</script>