<?php
// vim: set expandtab cindent tabstop=4 shiftwidth=4 fdm=marker:

/**
 * $logger = new Logger('suyi');
 * $logger->info('test');
 */
class Logger
{
    const INFO  = 0;
    const WARN  = 1;
    const ERR   = 2;
    const FATAL = 3;

    const LOG_CHUNK_SIZE = 104857600; // 100M
    const LOG_CHUNK_MAX = 10;

    // log 级别, 分为 INFO NOTICE WARN ERR (FATAL)
    private $level;

    // 保存 log 文件的时间

    // 保存 log 文件的句柄
    private $logFile;

    // 保存 log 文件名称
    private $logFileName;

    // 客户端 ip
    private $ip;

    // 单例模式
    private static $log = NULL;

    // 缓存
    private $records = array();

    // 记录 cache 中保存的流水的大小, 即每 20 条写一次文件
    private $maxRecordCount = 20;

    // 记录 cache 中当前保存的流水的数量
    private $curRecordCount = 0;

    // 当前进程ID
    private $processID = '0';

    //log实例
    private static $logs = array() ;

    /**
     * 构造函数
     *
     * @param        string        $file, log文件名
     * @return        void
     */
    function __construct( $logname = '' )
    {
        register_shutdown_function(array($this, 'save'));

        if ( strlen( $logname ) )
        {
            $logname = self::_transFilename( $logname );
            $logname = basename( $logname, '.log' );
        }
        else
        {
            $logname = basename( $_SERVER['SCRIPT_NAME'], '.php' );
        }
        $logname = $logname.'.log';


        if ( isset( self::$logs[ $logname ] ) ) {
            $this -> logFileName = &self::$logs[ $logname ] -> logFileName;
            $this -> records = &self::$logs[ $logname ] -> records;
            $this -> curRecordCount = &self::$logs[ $logname ] -> curRecordCount;
            $this -> level = defined( 'LOG_LEVEL' ) ? LOG_LEVEL : self::$logs[ $logname ] -> level;
            $this -> ip = &self::$logs[ $logname ] -> ip;
            $this -> processID = &self::$logs[ $logname ] -> processID;
            return ;
        }

        $this->logFileName = $logname;
        $this->level       = defined( 'LOG_LEVEL' ) ? LOG_LEVEL : self::ERR;
        $this->ip          = defined( 'USER_REAL_IP' ) ? USER_REAL_IP : '';
        if ( empty( $this->ip ) )
        {
            $this->ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
        }

        $tmp = explode( ',', $this->ip );
        if ( isset( $tmp[0] ) )
        {
            $this->ip = $tmp[0];
        }
        $this->processID   = str_pad( (function_exists('posix_getpid') ? posix_getpid() : 0), 5 );

        self::$log = $this;
        self::$logs[ $logname ] = $this ;
    }

    /**
     * 析构函数
     */
    function __destruct()
    {
    }

    function save()
    {
        if ( $this->curRecordCount > 0 )
        {
            $str = implode( "\n", $this->records )."\n";
            $logPath = self::_getLogPath() ;

            self::_writeFileLog( $logPath, $this->logFileName,  $str ) ;

            $this->records = array();
            $this->curRecordCount = 0;
        }

        if ( !empty($this->logFile) )
        {
            fclose($this->logFile);
        }
    }

    /**
     * 通过对象实例调用
     */
    function __call( $func, $args ) {
        $str = isset($args[0])? $args[0] : '' ;
        if ( !is_string($str) ) {
            return false;
        }

        switch ( $func ) {
            case 'info':
                if ($this->level < self::INFO) {
                  return false;
                }

                $level = 'INFO' ;
                break;
            case 'notice':
                if ($this->level < self::INFO) {
                    return false;
                }

                $level = 'NOTICE' ;
                break;
            case 'warn':
                if ($this->level < self::WARN) {
                    return false;
                }

                $level = 'WARN' ;
                break;
            case 'err':
                if ($this->level < self::ERR) {
                    return false;
                }

                $level = 'ERR' ;
                break;
        }

        $trc = debug_backtrace();
        $s = date('Y-m-d H:i:s');
        $s .= "    $level    PID:".$this->processID;
        $s .= "    ".$trc[0]['file'];
        $s .= "    line:".$trc[0]['line'];
        $s .= "    ip:".$this->ip."    ";
        $s .= $str;
        self::_write( $this->logFileName, $s);

        return true;
    }

    /**
     * 通过类直接调用
     */
    public static function __callStatic( $func, $args ) {

        $str = $args[0] ;
        if ( !is_string($str) ) {
            return false;
        }

        $fileName = '' ;
        if ( isset( $args[1] ) && !empty( $args[1] ) ) {
            $fileName = trim( $args[1] ) ;
        }

        if ( empty(self::$log) ) {
            self::$log = new Logger( $fileName );
        }

        switch ( $func ) {
            case 'info':
                if (self::$log->level < self::INFO) {
                    return false;
                }

                $level = 'INFO' ;
                break;
            case 'notice':
                if (self::$log->level < self::INFO) {
                    return false;
                }

                $level = 'NOTICE' ;
                break;
            case 'warn':
                if (self::$log->level < self::WARN) {
                    return false;
                }

                $level = 'WARN' ;
                break;
            case 'err':
                if (self::$log->level < self::ERR) {
                    return false;
                }

                $level = 'ERR' ;
                break;
        }

        $trc = debug_backtrace();
        $s = date('Y-m-d H:i:s');
        $s .= "    $level    PID:".self::$log->processID;
        $s .= "    ".$trc[0]['file'];
        $s .= "    line:".$trc[0]['line'];
        $s .= "    ip:".self::$log->ip."    ";
        $s .= $str;
        self::_write( self::$log -> logFileName, $s);

        return true;
    }

    /**
     * 转义文件名包含的非法字符
     *
     * @param        string        $filename, 文件名
     *
     * @return        string        $filename
     */
    private function _transFilename($filename)
    {
        if  ( !strlen($filename) ) {
            return $filename;
        }

        $filename = str_replace('\\', '#', $filename);
        $filename = str_replace('/', '#', $filename);
        $filename = str_replace(':', ';', $filename);
        $filename = str_replace('"', '$', $filename);
        $filename = str_replace('*', '@', $filename);
        $filename = str_replace('?', '!', $filename);
        $filename = str_replace('>', ')', $filename);
        $filename = str_replace('<', '(', $filename);
        $filename = str_replace('|', ']', $filename);

        return $filename;
    }

    /**
     * 检测日志文件是否是当前日期的, 主要考虑 Server, Daemon
     */
    private function _write( $fileName, $s )
    {
        if ( !strlen( $s ) )
        {
            return false;
        }

        $logPath = self::_getLogPath() ;

        $log = NULL ;
        if ( isset( self::$logs[ $fileName ] ) ) {
            $log = self::$logs[ $fileName ] ;
        } else {
            $log = new Logger( $fileName ) ;
        }

        $log->records[] = $s;
        $log->curRecordCount++;

        $str = implode( "\n", $log->records )."\n";

        if ( $log->curRecordCount >= $log->maxRecordCount ) {
            self::_writeFileLog( $logPath, $fileName,  $str ) ;

            $log->curRecordCount = 0;
            $log->records = array();
        }

        return true;
    }

    /**
     * 获取日志路径
     */
     private static function _getLogPath(){
        $today = date('Ymd');
        $path = '/tmp/jr.qq.com/'.$today.'/';
        return $path ;
     }

    /**
     * 通过本地文件写日志
     */
    private static function _writeFileLog( $logDir, $fileName, $str ){
      if ( !isset( self::$logs[ $fileName ] ) ) {
            return false ;
        }

        $log = self::$logs[ $fileName ] ;
        if ( empty( $log -> logFile ) ) {
            if ( !file_exists($logDir) )
            {
                umask(0);
                mkdir($logDir, 0777, true);
            }

            $targetPath = $absolutePath = $logDir . $fileName;

            $i = 1;
            while($i < self::LOG_CHUNK_MAX) {
                if (file_exists($targetPath) && filesize($targetPath) > self::LOG_CHUNK_SIZE) { // 100M
                    $targetPath = $absolutePath . '.'. $i;
                    $i ++;
                } else {
                    break;
                }
            }

            $log->logFile = fopen($targetPath, 'a');
        }

        fwrite( $log->logFile, $str );
    }
}

//End of script
