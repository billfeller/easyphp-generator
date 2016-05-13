<?php
// vim: set expandtab cindent tabstop=4 shiftwidth=4 fdm=marker:

class Utils {
    /**
     * {{{ Method cUrl()
     *
     * @param string $url       The query url
     * @param string $fields    The full data to post/get in a HTTP "POST/GET" operation
     *                          such as : para1=val1&para2=val2&...
     * @param string $method    HTTP operation,such as : POST/GET
     * @param string $header    HTTP header
     * @param int    $timeout   Timeout, the default value is 2s.
     * @return string           The response content
     * @example
     *    echo Utils::cUrl('http://www.baidu.com/s', 'wd=php&rsv_bp=0&inputT=669');
     */
    public static function cUrl(
        $url,
        $fields = '',
        $method = 'POST',
        $header = array('Content-Type: application/x-www-form-urlencoded; charset=UTF-8'),
        $timeout = 30
    ) {
        if(empty($url)) {
            throw new Exception('请求Url为空');
        }
        $method = strtoupper($method);
        if(!in_array($method, array('POST', 'GET'))) {
            throw new Exception('请求方法有误');
        }
        //The GET method
        if('GET' == $method) {
            $url .= (strpos($url, '?')===FALSE) ? ('?'.$fields) : $fields;
        }
        //Init cURL
        $ch = curl_init($url);
        $option = array();
        $option[CURLOPT_RETURNTRANSFER] = 1;
        //HTTPS
        if(stripos($url, 'https') === 0){
            $option[CURLOPT_SSL_VERIFYPEER] = 0;
            $option[CURLOPT_SSL_VERIFYHOST] = 1;
        }
        //The POST method
        if($method == 'POST'){
            $option[CURLOPT_POST] = 1;
            $option[CURLOPT_POSTFIELDS] = $fields;
            //POST方式需携带Content-length头域
            $header[] = 'Content-length: '.strlen($fields);
            $header[] = 'Connection : close';
        }
        $option[CURLOPT_HTTPHEADER]     = $header;
        $option[CURLOPT_CONNECTTIMEOUT] = $timeout;
        $option[CURLOPT_TIMEOUT]        = $timeout;
        curl_setopt_array( $ch, $option );
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
    /* }}} */
    /**
     * {{{ Method getIP()
     *
     * 获取用户端IP
     *
     * @return string
     */
    public static function getIP() {
        if ( Env::server('HTTP_X_FORWARDED_FOR') != '' ) {
            return Env::server('HTTP_X_FORWARDED_FOR');
        } elseif ( Env::server('HTTP_X_REAL_IP') != '' ) {
            return Env::server('HTTP_X_REAL_IP');
        } elseif( Env::server('REMOTE_ADDR') != '' ) {
            return Env::server('REMOTE_ADDR');
        }
        return '';
    }
    /* }}} */

    /**
     * {{{ Method checkReferer()
     *
     * 检查来源Referer是否合法
     *
     * @return boolean
     */
    public static function checkReferer() {
        if(preg_match('/\.[A-Za-z0-9\-]+\.[A-Za-z]{2,3}$/', Env::server('HTTP_HOST'), $m)) {
            $domain = $m[0];
        } else {
            $domain = '.qq.com';
        }
        $ref = Env::server('HTTP_REFERER');
        return $ref === '' || (bool)preg_match("/^https?:\/\/[A-Za-z0-9\-]+{$domain}/", $ref);
    }
    /* }}} */

    /**
     * 检测待测字符串是否匹配指定的正则的表达式
     * 利用PHP魔术方法__callStatic，须PHP 5.3.0及以上支持
     *
     * @access public
     *
     * @param string $regularType   正则表达式类型
     * @param string $string        待匹配的字符串
     * @return boolean
     * @author zhanhailiang
     * @example
     * 1、Utils::checkTel('05988585959');
     * 2、Utils::checkEmail('bill@gmail.com');
     * 3、Utils::checkMobile('15986865959');
     * 4、Utils::checkPostCode('958695');
     */
    public static function __callStatic($func, $args) {
        $reg = array(
            "tel" => "/^(?:\d{3,5})?-?(?:\d{7,8})(?:-\d{1,})?$/",
            "email" => "/^[\w!#\$%'\*\+\-\/=\?\^`\{\}\|~]+(?:\.[\w!#\$%'\*\+\-\/=\?\^`{}\|~]+)*@[-a-z\d]{1,20}\.[a-z\d]{1,10}(?:\.[a-z]{2})?$/i",
            "mobile" => "/^1(?:3|4|5|8|7)\d{9}$/",
            "postcode" => "/^[1-9]\d{5}$/",
            "chinese" => "/^[\x{4e00}-\x{9fa5}]+$/u",
        );
        $regularType = strtolower(substr($func, 5));
        if(!isset($reg[$regularType])) {
            throw new Exception('缺少该正则表达式' . $regularType);
        }

        if(!isset($args[0])) {
            throw new Exception('缺少待匹配的字符串');
        }
        $string = $args[0];
        return (bool)preg_match($reg[$regularType], $string);
    }
    /**
     * {{{ Method unicodeToUCS()
     *
     * Unicode编码转UCS
     *
     * @param $string $str  待转化字符串，比如：\u7528\u5361，PHP5.4中已经有选项控制
     * @return string
     */
    public static function unicodeToUCS($str) {
        return preg_replace_callback('/\\\\u([\da-f]{4})/i',
            create_function('$matches','return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'),
            $str);
    }
    /* }}} */
}