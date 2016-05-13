<?php
// vim: set expandtab cindent tabstop=4 shiftwidth=4 fdm=marker:

/**
 * MySQL操作类
 *
 * Usage:
 * <code>
 *    $db = new Mysql();
 *    $db->query($sql = 'select * from table_a');
 *    echo 'data rows:'.$db->numRows()."\n";
 *    $result = $db->fetchAll();
 *    print_r($result);
 *
 *    $db->update($table='table_a', $data=array('name'=>'data1'), $where='id=1');
 *    echo 'update rows:'.$db->affectedRows()."\n";
 *
 *    $db->query($sql = 'select * from table_a where id=1');
 *    $result = $db->fetchRow();
 *    print_r($result);
 *
 *    $db->insert($table='table_a', $data=array('name'=>'data2'));
 *    $lastId = $db->insertId();
 *    echo 'last id:'.$lastId."\n";
 *    echo 'insert rows:'.$db->affectedRows()."\n";
 *
 *    $db->delete($table='table_a', $where='id='.$lastId);
 *    echo $db->getSql()."\n";
 *    echo 'delete rows:'.$db->affectedRows()."\n";
 *
 *    echo $db->total('table_a')."\n";
 * </code>
 *
 */
class Mysql {
    /**
     * 当前查询SQL语句
     * @var string
     */
    protected $_sql = '';
    /**
     * mysqli 对象
     * @var mixed
     */
    protected $_mysqli = null;
    /**
     * 当前结果集
     * @var mixed
     */
    protected $_result = false;
    /**
     * 错误信息
     * @var string
     */
    protected $_error = '';
    /**
     * 错误日志位置
     * @var string
     */
    protected $_log = '/tmp/mysql-error.log';
    /**
     * 配置信息 //< HOST||PORT||DATABASENAME||USERNAME||PASSWORD||CHARSET
     * @var string
     */
    protected $_config = '';
    /** Method Constructor()
     *
     * @param   string  $config  配置字串 $_SERVER配置变量名，配置设置格式：HOST||PORT||DBNAME||USERNAME||PASSWORD||CHARSET
     * @return  void
     */
    public function __construct($config = '') {
        if(empty($config)){
            $this->_config = @$_SERVER['PROJECT_MYSQL_SERVER'];
        }else{
            $this->_config =  $config;
        }
        if(!preg_match('/^[^\|]+(?:\|\|[^\|]+){5}$/', $this->_config)){
            throw new Exception('Mysql config type is error!');
        }
    }

    /** Method Destructor()
     *
     */
    public function __destruct() {
        $this->freeResult();
    }

    /**
     * 执行查询
     *
     * @param string $sql   SQL查询语句
     * @return mixed        成功赋值并返回$this->result; 失败返回 false 如果有事务则回滚
     */
    public function query($sql) {
        $this->_connect();
        $this->_sql = $sql;
        $this->_result = $this->_mysqli->query($sql);
        if ($this->_mysqli->error) {
            $this->_error = $this->_mysqli->error;
            $this->log();
            return false;
        }
        return $this->_result;
    }
    /**
     * 查询指定SQL 第一行，第一列 值
     *
     * @param string $sql   SQL查询语句
     * @return mixed        失败返回 false
     */
    public function dataOne($sql) {
        if ($this->_result = $this->query($sql)) {
            return $this->fetchOne();
        } else {
            return false;
        }
    }
    /**
     * 查询指定SQL 第一行记录
     *
     * @param string $sql    SQL查询语句
     * @param  mixed $assoc  true 返回数组; false 返回stdClass对象;默认 false
     * @return 失败返回 false
     */
    public function dataRow($sql, $assoc = false) {
        if ($this->_result = $this->query($sql)) {
            return $this->fetchRow($this->_result, $assoc);
        } else {
            return false;
        }
    }
    /**
     * 查询指定SQL 所有记录
     *
     * @param string $sql           SQL查询语句
     * @param boolean $key_field    指定记录结果键值使用哪个字段,默认为 false 使用 i{0...count}
     * @param mixed  $assoc         true 返回数组; false 返回stdClass对象;默认 true
     * @return 失败返回 false
     */
    public function dataTable($sql, $key_field = false, $assoc = true) {
        if ($this->_result = $this->query($sql)) {
            return $this->fetchAll($key_field, $assoc);
        } else {
            return false;
        }
    }
    /**
     * 取结果($this->result)中第一行，第一列值
     *
     * @return mixed 没有结果返回 false
     */
    public function fetchOne() {
        if (!empty($this->_result)) {
            $row = $this->_result->fetch_array();
            return $row[0];
        } else {
            return false;
        }
    }
    /**
     * 取结果$result中第一行记录
     *
     * @param object $result    查询结果数据集
     * @param  boolean $assoc   true 返回数组; false 返回stdClass对象;默认 true
     * @return mixed            没有结果返回 false
     */
    public function fetchRow($result = null , $assoc = true) {
        if ($result == null) $result = $this->_result;
        if (empty($result)) {
            return false;
        }
        if ($assoc) {
            $_result = $result->fetch_assoc();
            // if($_result) $_result = array_change_key_case($_result, CASE_UPPER);
            return $_result;
        } else {
            return $result->fetch_object();
        }
    }
    /**
     * 取结果($this->result)中所有记录
     *
     * @param string $keyField      指定记录结果键值使用哪个字段,默认为 false 则使用 i{0...count}
     * @param boolean $assoc        true 返回数组; false 返回stdClass对象;默认 true
     * @return  mixed               没有结果返回 false
     */
    public function fetchAll($keyField = false, $assoc = true) {
        $rows = ($assoc) ? array() : new stdClass;
        $i = -1;
        while ($row = $this->fetchRow($this->_result, $assoc)) {
            if ($keyField != false) {
                $i = ($assoc) ? $row[$keyField] : $row->$keyField;
            } else {
                $i++;
            }
            if ($assoc) {
                $rows[$i] = $row;
            } else {
                $rows->{$i} = $row;
            }
        }
        return ($i > -1) ? $rows : false;
    }
    /**
     * 执行更新数据操作
     *
     * @param string    $table  数据库表名称
     * @param array     $data   待更新的数据
     * @param string    $where  更新条件
     * @return boolean          成功 true; 失败 false
     */
    public function update($table, $data, $where) {
        $set = '';
        if (is_object($data) || is_array($data)) {
            foreach ($data as $k => $v) {
                $this->_formatValue($v);
                $set .= empty($set) ? ("`{$k}` = {$v}") : (", `{$k}` = {$v}");
            }
        } else {
            $set = $data;
        }
        return $this->query("UPDATE `{$table}` SET {$set} WHERE {$where}");
    }
    /**
     * 执行插入数据操作
     *
     * @param   string $table       数据库表名称
     * @param   array $data         待插入的数据
     * @param   string $fields      数据库字段，默认为 null。 为空时取 $data的 keys
     * @return  boolean             成功 true; 失败 false
     */
    public function insert($table, $data, $fields = null) {
        if ($fields == null) {
            foreach($data as $v) {
                if (is_array($v)) {
                    $fields = array_keys($v);
                } elseif (is_object($v)) {
                    foreach($v as $k2 => $v2) {
                        $fields[] = $k2;
                    }
                } elseif (is_array($data)) {
                    $fields = array_keys($data);
                } elseif (is_object($data)) {
                    foreach($data as $k2 => $v2) {
                        $fields[] = $k2;
                    }
                }
                break;
            }
        }
        $_fields = '`' . implode('`, `', $fields) . '`';
        $_data = $this->_formatInsertData($data);
        return $this->query("INSERT INTO `{$table}` ({$_fields}) VALUES {$_data}");
    }
    /**
     * 执行替换数据操作
     *
     * @param   string $table       数据库表名称
     * @param   array $data         待更新的数据
     * @param   string $fields      数据库字段，默认为 null。 为空时取 $data的 keys
     * @return  boolean             成功 true; 失败 false
     */
    public function replace($table, $data, $fields = null) {
        if ($fields == null) {
            foreach($data as $v) {
                if (is_array($v)) {
                    $fields = array_keys($v);
                } elseif (is_object($v)) {
                    foreach($v as $k2 => $v2) {
                        $fields[] = $k2;
                    }
                } elseif (is_array($data)) {
                    $fields = array_keys($data);
                } elseif (is_object($data)) {
                    foreach($data as $k2 => $v2) {
                        $fields[] = $k2;
                    }
                }
                break;
            }
        }
        $_fields = '`' . implode('`, `', $fields) . '`';
        $_data = $this->_formatInsertData($data);
        return $this->query("REPLACE INTO `{$table}` ({$_fields}) VALUES {$_data}");
    }
    /**
     * 更新计数器
     *
     * @param  string   $table  数据库表名称
     * @param  array    $field  待更新的字段名
     * @param  string   $where  更新条件
     * @param  int      $step   增加的步长，默认每次+1
     * @return boolean          成功 true; 失败 false
     */
    public function increase($table, $field, $where, $step = 1) {
        return $this->query("UPDATE `{$table}` SET `{$field}`=`{$field}`+{$step} WHERE {$where}");
    }
    /**
     * 格式化插入数据
     *
     * @param mixed $data   [array|stdClass] 待格式化的插入数据
     * @return string        insert 中 values 后的 SQL格式
     */
    protected function _formatInsertData($data) {
        $output = '';
        $is_list = false;
        foreach ($data as $value) {
            if (is_object($value) || is_array($value)) {
                $is_list = true;
                $tmp = '';
                foreach ($value as $v) {
                    $this->format_value($v);
                    $tmp .= !empty($tmp) ? ", {$v}" : $v;
                }
                $tmp = "(" . $tmp . ")";
                $output .= !empty($output) ? ", {$tmp}" : $tmp;
                unset($tmp);
            } else {
                $this->_formatValue($value);
                $output .= !empty($output) ? ", {$value}" : $value;
            }
        }
        if (!$is_list) $output = '(' . $output . ')';
        return $output;
    }
    /**
     * 格式化值
     *
     * @param string  &$value [string] 待格式化的字符串,格式成可被数据库接受的格式
     * @return void
     */
    protected function _formatValue(&$value) {
        $value = trim($value);
        if ($value === null) {
            $value = 'NULL';
        } elseif (preg_match('/\[\w+\]\.\(.*?\)/', $value)) { // mysql函数 格式:[UNHEX].(参数);
            $value = preg_replace('/\[(\w+)\]\.\((.*?)\)/', "$1($2)", $value);
        } else {
            $value = "'" . addslashes(stripslashes($value)) . "'";
        }
    }
    /**
     * 返回最后一次插入的ID
     * return mixed
     */
    public function insertId() {
        return $this->_mysqli->insert_id;
    }
    /**
     * 执行删除数据操作
     *
     * @param string $table 数据库表名称
     * @param string $where 删除条件,默认为删除整个表数据!!
     * @return boolean      成功 true; 失败 false
     */
    public function delete($table, $where = '') {
        return $this->query("DELETE FROM {$table} ".($where ? " WHERE {$where}" : ''));
    }
    /***
     * *返回结果集数量
     *
     * @param  $result [数据集]
     * @return int
     */
    public function numRows($result = null) {
        if (is_null($result)) $result = $this->_result;
        return mysqli_num_rows($result);
    }
    /**
     * 统计表记录
     *
     * @param string $table 数据库表名称
     * @param string $where SQL统计条件,默认为查询整个表
     * @return mixed
     */
    public function total($table, $where = '') {
        $sql = "SELECT count(*) FROM {$table} ".($where ? "WHERE {$where}" : '');
        $this->query($sql);
        return $this->fetchOne();
    }
    /**
     * 返回当前查询SQL语句
     * @return string
     */
    public function getSql() {
        return $this->_sql;
    }
    /**
     * 返回错误信息
     * @return string
     */
    public function getError() {
        return $this->_error;
    }
    /**
     * 记录日志
     * @param string $_msg  日志内容
     * @return void
     */
    public function log($_msg = '') {
        if(!$_msg){
            list($usec, $sec) = explode(' ', microtime());
            $_msg = '[' . date('Y-m-d H:i:s.') . substr($usec, 2, 3) . '][query: ' . $this->_sql . '][error: ' . $this->_error . ']' . PHP_EOL;
        }
        error_log($_msg, 3, $this->_log);
    }
    /**
     * 返回当前SQL影响的记录数
     * @return mixed
     */
    public function affectedRows() {
        return $this->_mysqli-> affected_rows;
    }
    /**
     * 释放数据集
     * return void
     */
    public function freeResult($result = null) {
        if (is_null($result)) $result = $this->_result;
        @mysqli_free_result($result);
    }
    /**
     * 关闭数据库连接
     * return void
     */
    public function close($mysqli = null) {
        if (is_null($mysqli)) $mysqli = $this->_mysqli;
        @mysqli_close($mysqli);
        $this->freeResult();
        $this->_mysqli = null;
    }
    /**
     * 选择数据库
     *
     * @param string $dbname    数据库名称
     * @return resourse
     */
    public function selectDb($dbname) {
        $this->_connect();
        return $this->_mysqli->select_db($dbname);
    }
    /**
     * 连接Mysql
     * @return  boolean 成功true，失败false
     */
    protected function _connect() {
        if (is_null($this->_mysqli)) {
            //Get config of Database
            $_arr = explode('||', $this->_config);
            $_host = $_arr[0];     //< Mysql host
            $_port = $_arr[1];     //< Mysql port
            $_dbName = $_arr[2];   //< Database name
            $_userName = $_arr[3]; //< User name
            $_userPwd = $_arr[4];  //< User password
            $_charset = str_replace('-', '', $_arr[5]);  //< Charset UTF-8->UTF8
            $this->_mysqli = new mysqli($_host, $_userName, $_userPwd, $_dbName, $_port);
            if (mysqli_connect_errno()) {
                $this->_error = "Database Connect failed: ". mysqli_connect_error();
                $this->log();
                return false;
            } else {
                $this->_mysqli->query("
                    SET character_set_connection=" . $_charset .
                    ", character_set_results=" . $_charset .
                    ", character_set_client=binary"
                );
            }
        }
        return true;
    }
}