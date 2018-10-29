<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-28
 * Version      :   1.0
 */

namespace Db;

use Helper\Base;

class Connection extends Base
{
    public $dsn; // 数据库链接串 eg：'mysql:dbname=mydatabase;host=127.0.0.1;charset=utf8;'
    public $username = ''; // 数据库连接用户
    public $password = ''; // 数据库连接密码
    public $autoConnect = true; // 数据库是否自动连接
    public $pdoClass = '\PDO'; // 链接数据库使用的类名，默认：\PDO
    public $logFile = false; // 是否记录在文件log上
    /**
     * 数据库连接使用编码
     * 该属性只在 MySql 和 PostgreSQL 中有效
     * PHP 5.3.6+, 'mysql:dbname=mydatabase;host=127.0.0.1;charset=utf8;'
     * @var string
     */
    public $charset;
    /**
     * 数据表前缀
     * 当查询语句中使用 "{{tableName}}" 时，将使用该前缀作为数据表前缀
     * @var string
     */
    public $tablePrefix;

    public function __construct(array $config)
    {
        $this->configure($config);
    }

    public function connect()
    {

    }

    public function findBySql()
    {

    }

    public function findAllBySql()
    {

    }

    public function insertBySql()
    {

    }

    public function deleteBySql()
    {

    }

    public function updateBySql()
    {

    }

    public function insert($table, array $data)
    {

    }

    public function insertData($table, array $data)
    {

    }

    public function delete($table, array $data)
    {

    }

    public function update($table, array $data)
    {

    }

    public function find($table, array $data)
    {

    }

    public function findAll($table, array $data)
    {

    }
}