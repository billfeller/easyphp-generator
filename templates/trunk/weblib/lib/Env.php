<?php
// vim: set expandtab cindent tabstop=4 shiftwidth=4 fdm=marker:

class Env {
    public function __construct() {}
    public function __destruct() {}
    /**
     * 获得'$_GET', '$_REQUEST', '$_POST', '$_COOKIE', '$_SERVER'参数值
     * 利用PHP魔术方法__callStatic，须PHP 5.3.0及以上支持
     *
     * @access public
     *
     * @param string $idx   索引值
     * @param int $isHack   TRUE OR FALSE，TRUE的时候代表开启过滤，否则不开启
     * @return string
     * @example
     * 1、获取$_GET参数：Pindao_Env::get('id', false);
     * 2、获取$_POST参数：Pindao_Env::post('id');
     * 3、获取$_REQUEST参数：Pindao_Env::request('id');
     * 4、获取$_COOKIE参数：Pindao_Env::cookie('id');
     * 5、获取$_SERVER参数：Pindao_Env::server('PHP_SELF');
     */
    public static function __callStatic($func, $args) {
        // 是否开启XSS过滤，默认开启
        $isHack = TRUE;
        $dict = array('_GET', '_REQUEST', '_POST', '_COOKIE', '_SERVER');
        // 索引值判断，不能为空
        if(!isset($args[0])){
            return '';
        }
        $idx = $args[0]; // 索引值
        // 是否启用XSS过滤
        if(isset($args[1])){
            $isHack = (bool)$args[1];
        }
        // 函数名大写
        $func = '_' . strtoupper($func);
        // 函数名不是指定的，返回空值
        if(!in_array($func, $dict)){
            return 'method not valid';
        }
        //eval("\$var = $func;"); // 不使用可变变量的原因：在PHP的函数和类的方法中，超全局变量不能用作可变变量
        $_SERVER;$_REQUEST;
        $var = $GLOBALS[$func];
        if(!isset($var[$idx]))
            return 'global vars not valid';
        if($isHack){
            return self::_hack($var[$idx]);
        }
        return $var[$idx];
    }
    private static function _hack($val) {
        if(is_array($val)) {
            array_walk_recursive($val, 'self::filter');
        } else {
            $val = self::filter($val);
        }
        return $val;
    }
    /**
     * 去掉string中不安全因素
     *
     * @access public
     *
     * @param string $val
     * @return string $val
     */
    public static function filter(&$val) {
        $val = htmlspecialchars($val);
        return $val;
    }
}
