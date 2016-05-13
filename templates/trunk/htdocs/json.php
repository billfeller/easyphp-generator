<?php
// vim: set expandtab cindent tabstop=4 shiftwidth=4 fdm=marker:

// php 运行环境配置
$lib_root = preg_replace('/htdocs.*/i', 'weblib/', dirname(__FILE__));
require_once($lib_root . 'etc/config.inc.php');

// 定义输出头的数组(以便 modules 中能够修改)
$wrap_header = array(
    "httpv1_1" => "HTTP/1.1 200 OK",
    "cache"    => "Cache-Control: max-age=0, must-revalidate",
    "type"     => "Content-type:text/html; charset=utf-8"
);

// 获得 module 名
if ( !empty( $_REQUEST['mod'] ) )
{
    $mod_name = preg_replace("/[^a-zA-Z]/", '',trim($_REQUEST['mod']));
}
else
{
    $mod_name = 'main';
}

$mod_file = SITE_MODULE_ROOT.$mod_name.'.php';
if ( !file_exists($mod_file) )
{
    exit( $mod_name.' is wrong-mod-name' );
}

/* 获得 act 名 */
if ( !empty($_REQUEST['act']) )
{
    $act_name = preg_replace( "/[^a-zA-Z]/", '', trim( $_REQUEST['act'] ) );
}
else
{
    $act_name = 'page';
}

$func_name = $mod_name . '_' . $act_name;

require_once $mod_file;
if ( !function_exists($func_name) )
{
    $func_name = $mod_name.'_page';
    if ( !function_exists( $func_name ) )
    {
        exit('wrong-function-name');
    }
}

/* 获得 json 字符串(因为函数中可能需要修改 $wrap_header ，所以需要先执行) */
$json_str = $func_name();

/* 从全局数组变量中输出 header */
if ( is_array($wrap_header) )
{
    foreach ( $wrap_header as $key => $header_line )
    {
        @header( $header_line );
    }
}

/* 获得请求的回调函数名 */
$callback_function = (empty($_REQUEST['callback']) || !preg_match("/^[a-zA-Z0-9_$\.]+$/", trim($_REQUEST['callback'])))
    ? '' : trim($_REQUEST['callback']);

$is_json_format = (empty($_REQUEST['fmt']) || !preg_match("/^[0-9]+$/", trim($_REQUEST['fmt'])))
    ? 0 : intval($_REQUEST['fmt']);

/* 如果不是iframe方式，输出json信息 */
if ( $is_json_format == 0 )
{
    if ( empty( $callback_function ) )
    {
        echo $json_str;
    }
    else
    {
        if ( isset( $_REQUEST['exception']) )
        {
            echo sprintf( 'try{ %s( %s ); }catch(e){};', $callback_function, $json_str );
        }
        else
        {
            echo sprintf( '%s( %s );', $callback_function, $json_str );
        }
    }
}
else
{
    echo <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>页面</title>
</head>
<body>
<script type="text/javascript">
document.domain = "qq.com";
parent.$callback_function($json_str);
</script>
</body>
</html>
EOT;
}
