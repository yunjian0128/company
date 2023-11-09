<?php
include_once('config/init.php');
include_once('check.php');

$region = isset($_POST['region']) ? trim($_POST['region']) : '';

//对应ajax请求部分
if (!empty($region)) {
    $success = [
        'result' => false,
        'msg' => '',
    ];

    //字符串转换为数组
    $arr = explode('/', $region);

    $province = isset($arr[0]) ? trim($arr[0]) : '';
    $city = isset($arr[1]) ? trim($arr[1]) : '';

    //切换了城市，我们需要重新覆盖cookie信息
    !empty($province) && setcookie("province", $province);
    !empty($city) && setcookie("city", $city);

    //调用封装天气查询方法
    $weather = GetWeatherInfo($city);

    if ($weather['result']) {
        //设置一下cookie
        setcookie('weather', $weather['msg']);
        $success['result'] = true;
        $success['msg'] = $weather['msg'];
    } else {
        $success['result'] = false;
        $success['msg'] = $weather['msg'];
    }

    echo json_encode($success);
    exit;
}

$city = [
    'province' => @$_COOKIE['province'],
    'city' => @$_COOKIE['city'],
    'weather' => @$_COOKIE['weather'],
];

//如果天气情况为空的时候，请求天气结果
if (empty($city['weather'])) {
    if (isset($_COOKIE['province']) && isset($_COOKIE['city'])) {
        $city['province'] = $_COOKIE['province'];
        $city['city'] = $_COOKIE['city'];
    } else {
        //如果没有cookie的时候，就重新获取，并覆盖cookie
        //获取当前地理位置
        $city = GetClientIP();

        // 将城市信息设置到cookie中
        setcookie("province", $city['province']);
        setcookie("city", $city['city']);
    }

    //获取天气情况
    $weather = GetWeatherInfo($city['city']);

    if ($weather['result']) {
        setcookie('weather', $weather['msg']);
        $city['weather'] = $weather['msg'];
    }
}
?>

<!-- 城市联动的插件 -->
<link rel="stylesheet" href="assets/plugins/city-picker/city-picker.css" />
<script src="assets/plugins/city-picker/city-picker.data.min.js"></script>
<script src="assets/plugins/city-picker/city-picker.min.js"></script>
<style>
    .city-picker-span {
        display: inline-block;
        width: auto !important;
        background: rgba(71, 71, 71, 0) !important;
        border: 0px;
        text-overflow: clip;
        white-space: nowrap;
        color: #fff !important;
        font-size: 1em;
        font-weight: bold;
        padding-top: 2px;
        margin-right: 15px;
    }

    .city-picker-span>.placeholder {
        color: #fff;
    }

    .city-picker-span>.title>span {
        color: #fff;
    }

    .city-picker-span>.arrow {
        display: none;
    }

    .city-picker-span>.title>span:hover {
        color: #333;
    }
</style>

<div class="navbar">
    <div class="navbar-inner">
        <ul class="nav pull-right">
            <li>
                <a href="javascript:void(0)">
                    <span id="region1" readonly placeholder="请选择城市" data-level="city"></span>
                    <b id="weather">
                        <?php echo $city['weather']; ?>
                    </b>
                </a>
            </li>
            <li>
                <a href="login.php" role="button">
                    <i class="icon-user"></i>
                    <?php echo isset($AutoLogin['username']) ? $AutoLogin['username'] : '未知管理员'; ?>
                </a>
            </li>
            <li>
                <a id="logout" href="javascript:void(0)" class="hidden-phone visible-tablet visible-desktop"
                    role="button">退出</a>
            </li>
        </ul>
        <a class="brand" href="index.php"><span class="second">公司通讯录</span></a>
    </div>
</div>

<script>
    //城市插件初始化
    $("#region1").citypicker({
        province: `<?php echo $city['province']; ?>`,
        city: `<?php echo $city['city']; ?>`
    })

    //给地区组件绑定切换事件
    $("#region1").on("cp:updated", function () {
        var citypicker = $(this).data("citypicker");
        var code = citypicker.getCode("city");
        if (!code) {
            return;
        }

        //获取城市信息
        var region = citypicker.getVal(code)

        $("#weather").html("天气情况正在加载中....")

        //发送异步请求
        $.ajax({
            url: 'header.php',
            type: 'post',
            dataType: 'json',
            data: { region: region },
            success: function (success) {
                $("#weather").html(success.msg)
            },
            error: function (error) {
                console.log(error)
            }
        })
    })

    //退出登录
    $("#logout").click(function () {
        if (confirm('是否确认退出登录')) {
            //跳转传参 get方式传递
            location.href = 'login.php?action=logout';
        }

        return false;
    })
</script>