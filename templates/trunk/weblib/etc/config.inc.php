<?php
// vim: set expandtab cindent tabstop=4 shiftwidth=4 fdm=marker:

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('error_log', '/tmp/jr.qq.com.error.log');
ini_set('date.timezone', 'Asia/Shanghai');

//定义各目录绝对路径
$web_root = preg_replace("/weblib.*/i", "", dirname(__FILE__));
define('WEB_ROOT', $web_root);
define('SITE_HTDOCS_ROOT', WEB_ROOT.'htdocs/');
define('SITE_TPL_ROOT', WEB_ROOT.'tpl/');
define('SITE_TPL_SRC_ROOT', WEB_ROOT.'tpl_src/');
define('SITE_MODULE_ROOT', WEB_ROOT.'module/');
define('SITE_API_ROOT', WEB_ROOT.'weblib/api/');
define('SITE_INTERFACE_ROOT', WEB_ROOT.'weblib/interface/');
define('SITE_LIB_ROOT', WEB_ROOT.'weblib/lib/');
define('SITE_ETC_ROOT', WEB_ROOT.'weblib/etc/');

// require_once(SITE_ETC_ROOT . 'db.inc.php');
require_once(SITE_LIB_ROOT . 'loadClass.php');

/* 用户的ip */
$user_real_ip = '';
$ip = isset( $_SERVER['HTTP_TRUE_CLIENT_IP'] ) ? $_SERVER['HTTP_TRUE_CLIENT_IP'] : '';
if( !empty( $ip ) )
{
    if( isRealIP( $ip ) )
    {
        $user_real_ip = $ip;
    }
}
else
{
    $user_real_ip_tmp = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
    $user_real_ip_arr = explode( ',', $user_real_ip_tmp );
    foreach( $user_real_ip_arr as $v )
    {
        if( !empty( $v ) )
        {
            $ip = str_pad( $v, 15 );
            if( isRealIP( $ip ) )
            {
                $user_real_ip = $ip;
                break;
            }
        }
    }
    if( empty( $user_real_ip ) )
    {
        $ip = str_pad( ( isset($_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0' ), 15 );
        if( isRealIP( $ip ) )
        {
            $user_real_ip = $ip;
        }
    }
}

define( 'USER_REAL_IP', trim($user_real_ip) );
function isRealIP( $ip )
{
    $ipbegin_1 = ip2long( '127.0.0.0' );
    $ipend_1 = ip2long( '127.255.255.255' );
    $ipbegin_2 = ip2long( '10.0.0.0' );
    $ipend_2 = ip2long( '10.255.255.255' );
    $ipbegin_3 = ip2long( '172.16.0.0' );
    $ipend_3 = ip2long( '172.31.255.255' );
    $ipbegin_4 = ip2long( '192.168.0.0' );
    $ipend_4 = ip2long( '192.168.255.255' );

    $ip = ip2long( $ip );
    if( ( $ip > $ipbegin_1 && $ip < $ipend_1 ) || ( $ip > $ipbegin_2 && $ip < $ipend_2 ) || ( $ip > $ipbegin_3 && $ip < $ipend_3 ) || ( $ip > $ipbegin_4 && $ip < $ipend_4 ) )
    {
        return false;
    }
    else
    {
        return true;
    }
}