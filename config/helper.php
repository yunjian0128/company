<?php

//声明编码
@header("Content-Type:text/html;charset=utf-8");

/**
 * 跳转提醒方法
 * @param $msg 提醒的文案内容
 * @param $url 跳转的地址
 * @return 无返回值
 */
function Notice($msg = '未知消息', $url = '')
{
    if (empty($url)) {
        echo "<script>alert('$msg');history.go(-1);</script>";
    } else {
        echo "<script>alert('$msg');location.href='$url';</script>";
    }
    return;
}

/**
 * 单条数据查询方法
 * @param $sql 要执行的sql语句
 * @return 返回查询的结果
 */
function find($sql = "")
{
    //引入全局变量
    global $link;

    //执行语句
    $query = @mysqli_query($link, $sql);

    //判断是否执行成功
    if (!$query) {
        echo "【SQL语句】：$sql <br /> 【错误信息】：" . mysqli_error($link);
        exit;
    }

    //返回出查询的结果 返回关联数组
    return mysqli_fetch_assoc($query);
}

/**
 * 多条数据查询方法
 * @param $sql 要执行的sql语句
 * @return 返回查询的结果
 */
function all($sql = " ")
{
    global $link;
    $query = @mysqli_query($link, $sql);
    if (!$query) {
        echo "【SQL语句】：$sql <br /> 【错误信息】：" . mysqli_error($link);
        exit;
    }
    //获取数据
    $list = [];

    //循环拿数据
    while ($data = mysqli_fetch_assoc($query)) {
        $list[] = $data;
    }

    //返回数据结果，二维数组
    return $list;
}

/**
 * 插入数据方法
 * @param $table 插入的表
 * @param $data 插入的数据
 * @return 返回插入的结果
 */
function add($table = '', $data = [])
{
    //引入全局变量
    global $link;
    global $pre_;

    //组装一个完整表名
    $table = $pre_ . $table;

    //将数组中所有的索引抽离到一个新的一位数组里面
    $keys = array_keys($data);

    //在将数组转换为字符串
    $str = "`" . implode("`,`", $keys) . "`";


    //将数组转换成字符串
    $values = "'" . implode("','", $data) . "'";

    // var_dump($str);
    // var_dump($values);
    // var_dump($data);
    // exit;

    //插入语句
    // INSERT INTO 表名(`字段1`,`字段2`,`字段3`)VALUES('值1','值2','值3');
    $sql = "INSERT INTO $table($str)VALUES($values)";

    //执行sql语句
    $query = mysqli_query($link, $sql);

    //判断是否执行成功
    if (!$query) {
        echo "【SQL语句】：$sql <br /> 【错误信息】：" . mysqli_error($link);
        exit;
    }

    //执行成功返回插入的自增id
    return mysqli_insert_id($link);
}

/**
 * 删除数据方法
 * @param $table 表名
 * @param $where 条件
 * @return 返回影响行数
 */
function delete($table = '', $where = '1')
{
    //引入全局变量
    global $link;
    global $pre_;

    //拼接完整表名
    $table = $pre_ . $table;

    //删除语句
    // DELETE FROM 表名 WHERE 条件
    $sql = "DELETE FROM $table WHERE $where";

    //执行sql语句
    $query = mysqli_query($link, $sql);

    //判断是否执行成功
    if (!$query) {
        echo "【SQL语句】：$sql <br /> 【错误信息】：" . mysqli_error($link);
        exit;
    }

    //返回影响函数
    return mysqli_affected_rows($link);
}

/**
 * 更新数据方法
 * @param $table 表名
 * @param $data 要更新的数据
 * @param $where 条件
 * @return 返回影响行数
 */

function update($table = '', $data = [], $where = '1')
{
    //引入全局变量
    global $link;
    global $pre_;

    //拼接完整表名
    $table = $pre_ . $table;

    //组装更新的数据
    $str = '';
    foreach ($data as $k => $v) {
        $str .= "`$k`='$v',";
    }

    //去除最后一个逗号
    $str = rtrim($str, ',');

    //更新语句
    // UPDATE 表名 SET 字段1='值1',字段2='值2' WHERE 条件
    $sql = "UPDATE $table SET $str WHERE $where";

    //执行sql语句
    $query = mysqli_query($link, $sql);

    //判断是否执行成功
    if (!$query) {
        echo "【SQL语句】：$sql <br /> 【错误信息】：" . mysqli_error($link);
        exit;
    }

    //返回影响函数
    return mysqli_affected_rows($link);
}

/* 
 *   获取当前的网址   
 */

function get_url()
{
    // 获取当前的完整地址
    $str = $_SERVER['PHP_SELF'] . '?';

    if ($_GET) {
        foreach ($_GET as $k => $v) {
            if ($k != 'page') {
                $str .= $k . '=' . $v . '&';
            }
        }
    }

    return $str;
}

/**
 * 输出分页函数
 * @param $current 当前页
 * @param $count   记录总数(查询数据表的总数)
 * @param $limit   每页显示多少条
 * @param $size    显示页码
 * @param $class   样式
 */
function page($current, $count, $limit, $size, $class = 'digg')
{
    $page = '';

    // 判断是否显示分页
    if ($count > $limit) {
        // 总分页
        $pages = ceil($count / $limit);

        // 获取地址
        $url = get_url();

        $page .= '<div class="' . $class . '">';

        // 首页 上一页
        if ($current == 1) {
            $page .= '<span class="disabled">首&nbsp;页</span>';
            $page .= '<span class="disabled">上一页</span>';
        } else {
            $page .= '<a href="' . $url . 'page=1">首&nbsp;页</a>';
            $page .= '<a href="' . $url . 'page=' . ($current - 1) . '">上一页</a>';
        }

        // 中间部分显示页码，  取的页码范围
        if ($current <= floor($size / 2)) {
            // 当前页在中间位置靠左,floor()向下取整
            $start = 1;
            // 如果总页数大于中间显示多少页,结束数字就是中间显示多少页的数字,否则结束数字就是总页数
            $end = $pages < $size ? $pages : $size;
        } else if ($current >= $pages - floor($size / 2)) {
            // 当前页在中间位置靠右 避免页数出现0或者负数
            $start = $pages - $size + 1 <= 0 ? 1 : $pages - $size + 1;
            $end = $pages;
        } else {
            // 当前页刚好在中间位置
            $start = $current - floor($size / 2);
            $end = $current + floor($size / 2);
        }

        for ($i = $start; $i <= $end; $i++) {
            if ($i == $current) {
                $page .= '<span class="current">' . $i . '</span>';
            } else {
                $page .= '<a href="' . $url . 'page=' . $i . '">' . $i . '</a>';
            }
        }

        // 下一页 尾页
        if ($pages == $current) {
            $page .= '<span class="disabled">下一页</span>';
            $page .= '<span class="disabled">尾&nbsp;页</span>';
        } else {
            $page .= '<a href="' . $url . 'page=' . ($current + 1) . '">下一页</a>';
            $page .= '<a href="' . $url . 'page=' . $pages . '">尾&nbsp;页</a>';
        }

        $page .= '</div>';
    }
    return $page;
}

/**
 * 单张上传
 * @param $name 表单input file name的名字
 * @param $path 存放路径
 */
function Upload($name = 'file', $path = '', $type = ['jpg', 'jpeg', 'png', 'gif', 'webp'])
{
    //封装一个结果信息
    $success = [
        'result' => true,
        //成功还是失败的状态
        'msg' => '',
        //提醒的内容
        'data' => '' //返回的数据
    ];

    //先获取到上传的文件 是一个数组结构
    $file = @isset($_FILES[$name]) ? $_FILES[$name] : [];

    //判断数组是否为空
    if (empty($file)) {
        $success['result'] = false;
        $success['msg'] = '没有文件上传';
        return $success;
    }

    //判断一下文件是否上传成功是否有错误信息
    $error = $file['error'];
    if ($error > 0) {
        switch ($error) {
            case 1:
                $success['result'] = false;
                $success['msg'] = '超出了服务器上传限制大小';
                break;
            case 2:
                $success['result'] = false;
                $success['msg'] = '超出了表单上传的限制大小';
                break;
            case 3:
                $success['result'] = false;
                $success['msg'] = '网络中断';
                break;
            case 4:
                $success['result'] = false;
                $success['msg'] = '无文件上传';
                break;
            default:
                $success['result'] = false;
                $success['msg'] = '未知错误';
                break;
        }

        return $success;
    }

    //上传文件
    //弄一个唯一的名字
    $filename = date("YmdHis") . "_" . $file['name'];

    //判断是否是规定的类型
    $ext = pathinfo($filename, PATHINFO_EXTENSION);

    //判断是否是允许上传的类型
    if (!in_array($ext, $type)) {
        $success['result'] = false;
        $success['msg'] = '非法类型上传';
        return $success;
    }

    //判断存放的目录是否存在，如果不存在就自动创建它
    if (!is_dir($path)) {
        //帮他创建
        $res = mkdir($path, 0777, true);

        //创建失败的时候提醒
        if (!$res) {
            $success['result'] = false;
            $success['msg'] = '创建文件夹失败';
            return $success;
        }
    }

    //将上传的文件完整路径拼接一下
    //第二个参数代表的意思是 如果给了 就删除第二个给的参数的内容
    // 不管你加没加 / 我全部给你们清空 自己加
    $path = trim($path, "/");

    //覆盖一下
    $filename = $path . '/' . $filename;

    //上传图片
    //判断临时文件是否是安全上传的
    if (is_uploaded_file($file['tmp_name'])) {
        //移动
        $res = move_uploaded_file($file['tmp_name'], $filename);

        if ($res) {
            $success['result'] = true;
            $success['msg'] = '文件上传成功';
            $success['data'] = $filename;
            return $success;
        } else {
            $success['result'] = false;
            $success['msg'] = '文件上传失败';
            return $success;
        }
    } else {
        //不安全就提醒
        $success['result'] = false;
        $success['msg'] = '非法文件';
        return $success;
    }
}

/**
 * 获取当前本机的IP地址信息
 */
function GetClientIP()
{
    global $pre_;

    //调用接口 返回json
    $json = file_get_contents("http://ip-api.com/json/");

    //把json 变成 php数组
    $result = json_decode($json, true);

    //获取城市信息
    $city = isset($result['city']) ? trim($result['city']) : '';

    //如果不为空,就要去数据库中查询出，这个城市存不存在
    if (empty($city)) {
        return null;
    }

    $sql = "SELECT name AS city,province FROM {$pre_}region WHERE pinyin LIKE '%$city%'";

    $region = find($sql);

    if (empty($region)) {
        return null;
    }

    //如果找到了城市信息就返回
    return $region;
}

/**
 * 获取某个城市的天气情况
 * @param $city 城市名称
 * @return 文本 天气情况
 */
function GetWeatherInfo($city = '')
{
    $success = [
        'result' => false,
        'msg' => ''
    ];

    //城市信息为空
    if (empty($city)) {
        $success['result'] = false;
        $success['msg'] = '城市信息为空';
        return $success;
    }

    $url = "https://api.asilu.com/weather/?city=$city";

    //请求接口
    $json = file_get_contents($url);

    // $json = '{"city":"北京","update_time":"11:30","date":"10月7日","weather":[{"date":"7日（今天）","weather":"多云转小雨","icon1":"01","icon2":"07","temp":"22/11℃","w":"","wind":"无持续风向","icond":"101","iconn":"305"},{"date":"8日（明天）","weather":"多云转晴","icon1":"01","icon2":"00","temp":"22/11℃","w":"","wind":"无持续风向","icond":"101","iconn":"150"},{"date":"9日（后天）","weather":"晴","icon1":"00","icon2":"00","temp":"23/9℃","w":"","wind":"无持续风向","icond":"100","iconn":"150"},{"date":"10日（周二）","weather":"晴转多云","icon1":"00","icon2":"01","temp":"22/9℃","w":"","wind":"无持续风向","icond":"100","iconn":"151"},{"date":"11日（周三）","weather":"多云转阴","icon1":"01","icon2":"02","temp":"23/11℃","w":"","wind":"无持续风向","icond":"101","iconn":"104"},{"date":"12日（周四）","weather":"多云","icon1":"01","icon2":"01","temp":"24/10℃","w":"","wind":"无持续风向","icond":"101","iconn":"151"},{"date":"13日（周五）","weather":"晴","icon1":"00","icon2":"00","temp":"22/8℃","w":"","wind":"无持续风向","icond":"100","iconn":"150"}]}';
    // sleep(5);

    //把json转换为php的数组
    $result = json_decode($json, true);

    //天气情况的第一天信息
    $info = $result['weather'][0];

    if (empty($info)) {
        $success['result'] = false;
        $success['msg'] = '未获取到当前城市的天气信息';
        return $success;
    }

    $date = $info['date']; //日期
    $weather = $info['weather']; //天气情况
    $temp = $info['temp']; //温度
    $wind = $info['wind']; //风向

    $success['result'] = true;
    $success['msg'] = "$date $weather $temp $wind";
    return $success;
}

?>