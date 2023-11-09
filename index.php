<?php
//引入系统配置文件
include_once('config/init.php');
include_once('check.php');

//接收action参数
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

// 统计职位信息的数据
if ($action == "job") {
    $sql = "SELECT count(person.id) AS c, job.name FROM {$pre_}person AS person LEFT JOIN {$pre_}job AS job ON person.jobid = job.id GROUP BY jobid";
    $job = all($sql);

    //字段提取
    $x = array_column($job, "name");
    $y = array_column($job, "c");

    //组装结果
    $res = [
        'x' => $x,
        'y' => $y,
    ];

    echo json_encode($res);
    exit;
}

if ($action == "dep") {
    $sql = "SELECT count(person.id) AS value, dep.name FROM {$pre_}person AS person LEFT JOIN {$pre_}department AS dep ON person.depid = dep.id GROUP BY depid";
    $deplist = all($sql);

    echo json_encode($deplist);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- 引入公共样式 -->
    <?php include_once('meta.php'); ?>

    <!-- 引入echarts图表插件 -->
    <script src="./assets/plugins/echarts/echarts.min.js"></script>

    <style>
        #job,
        #dep {
            height: 500px;
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
            <h1 class="page-title">后台首页</h1>
        </div>

        <div class="container-fluid">
            <div class="row-fluid">
                <div class="row" style="margin:0px;">
                    <div id="job" class="span6">图表</div>
                    <div id="dep" class="span6">图表</div>
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
    // 职位统计
    $.ajax({
        type: 'post',
        dataType: 'json',
        data: { action: 'job' },
        success: function (success) {
            var Ydata = []

            for (var item of success.y) {
                var r = Math.floor(Math.random() * 255)
                var g = Math.floor(Math.random() * 255)
                var b = Math.floor(Math.random() * 255)
                Ydata.push({
                    value: item,
                    itemStyle: {
                        color: `rgb(${r},${g},${b})`
                    }
                })
            }

            //配置图表选项
            var options = {
                title: {
                    text: "职位统计条形图",
                    subtext: "子标题",
                    left: "center"
                },
                xAxis: {    //X轴
                    type: 'category',
                    data: success.x,
                    // 设置字体属性
                    axisLabel: {
                        fontSize: 12,
                        rotate: 45,  //倾斜角度
                        interval: 0  //间隔显示
                    }
                },
                yAxis: {  //Y轴
                    type: 'value'
                },
                series: [ //Y轴对应的数据选项
                    {
                        name: '统计人数',
                        data: Ydata,
                        type: 'bar', // bar柱状图  line折线图  pie饼图 scatter散点图 
                        // radar雷达图 map地图 k线图 boxplot箱形图 heatmap热力图
                        // graph关系图 parallel平行坐标图 sankey桑基图 funnel漏斗图
                        // gauge仪表盘 pictorialBar象形柱图 themeRiver主题河流

                        // // 设置柱状图的宽度
                        // barWidth: 20,
                        // // 设置柱状图的间距
                        // barGap: 0,
                        // // 设置柱状图的阴影
                        // itemStyle: {
                        //     shadowBlur: 10, //阴影大小
                        //     shadowOffsetX: 0, //阴影水平方向上的偏移
                        //     shadowColor: 'rgba(0, 0, 0, 0.5)'
                        // }
                    }
                ],
                tooltip: {  //提示框
                    trigger: 'axis',
                    axisPointer: {  //坐标轴指示器
                        type: 'shadow',
                        // 设置阴影颜色
                        shadowStyle: {
                            color: 'rgba(255, 255, 255, 0.2)',
                            // innerWidth: 10, //阴影大小
                            // innerHeight: 10 //阴影大小
                        },
                        label: {
                            show: true
                        }
                    }
                },

            }

            //获取页面dom元素
            var dom = document.getElementById('job')
            //初始化
            var myChart = echarts.init(dom)
            //设置配置选项
            myChart.setOption(options)
        },
        error: function (error) {
            console.log(error)
        }
    })

    // 部门统计
    $.ajax({
        type: 'post',
        dataType: 'json',
        data: { action: 'dep' },
        success: function (success) {
            //配置图表选项
            var options = {
                title: {
                    text: "部门统计扇形图",
                    subtext: "子标题",
                    left: "center"
                },
                // 工具栏，鼠标移上去显示弹框
                tooltip: {
                    trigger: 'item'
                },
                //信息的分栏结构
                legend: {
                    orient: 'vertical',
                    left: 'left'
                },
                series: [ //Y轴对应的数据选项
                    {
                        name: '统计人数',
                        type: 'pie',
                        radius: '50%',
                        data: success,
                        emphasis: { //阴影配置
                            itemStyle: {
                                shadowBlur: 10, //阴影大小
                                shadowOffsetX: 0, //阴影水平方向上的偏移
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            }

            //获取页面dom元素
            var dom = document.getElementById('dep')
            //初始化
            var myChart = echarts.init(dom)
            //设置配置选项
            myChart.setOption(options)
        },
        error: function (error) {
            console.log(error)
        }
    })
</script>