<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-27
 * Version      :   1.0
 */

use Db\Builder\InsertBuilder;
use Db\Builder\UpdateBuilder;
use Db\Command;
use Helper\Timer;

defined('PHP_DEBUG') or define('PHP_DEBUG', false);

/**
 * Class Db
 */
class Db extends \Helper\Base
{
    private static $_instances = [];

    /**
     * 获取Db连接实例
     * @param string $type
     * @return $this
     * @throws Exception
     */
    public static function getInstance($type = 'master')
    {
        if (!isset(self::$_instances[$type])) {
            $config = Config::suffix(Config::getInstance('database', 'master')->getAll());
            return self::$_instances[$type] = new self($config);
        }
        return self::$_instances[$type];
    }

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
    public $charset = 'utf8';
    /**
     * 数据表前缀
     * 当查询语句中使用 "{{tableName}}" 时，将使用该前缀作为数据表前缀
     * @var string
     */
    public $tablePrefix = 'cf_';
    /**
     * 启用或禁用预处理语句的模拟(数据查询参数是否在本地进行转义，该属性在 PHP 5.1.3+ 有效)
     * PDO 默认为 true（在本地进行），设为 false 时转义将在 db 中进行
     * @var bool
     */
    public $emulatePrepare;

    /**
     * @var \PDO PDO链接数据库
     */
    private $_pdo;
    private $_active = false; // 连接是否被激活
    private $_attributes = []; // PDO连接属性

    /**
     * Db constructor.
     * @param array $config
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        $this->configure($config);
        $this->init();
    }

    /**
     * 构造函数之后的初始化
     * @throws Exception
     */
    public function init()
    {
        if ($this->autoConnect) {
            $this->setActive(true);
        }
    }

    /**
     * 开启或关闭 db 链接
     * @param bool $value
     * @throws Exception
     */
    public function setActive($value)
    {
        if ($value != $this->_active) {
            if ($value) {
                $this->open();
            } else {
                $this->close();
            }
        }
    }

    /**
     * SQL 日志推送
     * @param $message
     * @param array $context
     * @throws Exception
     */
    public function pushLog($message, array $context = [])
    {
        if ($this->logFile) {
            Log::getInstance("SQL")->pushInfo($message, $context);
        }
    }

    /**
     * SQL 错误日志推送
     * @param $message
     * @param array $context
     * @throws Exception
     */
    public function pushErrorLog($message, array $context = [])
    {
        if ($this->logFile) {
            Log::getInstance("ERROR-SQL")->pushInfo($message, $context);
        }
    }

    /**
     * 获取 db-connect 是否被连接开启的标志
     * @return bool
     */
    public function getActive()
    {
        return $this->_active;
    }

    /**
     * 如果当前 db-connect 没有实例化，则开启
     * @throws Exception
     */
    protected function open()
    {
        if (null === $this->_pdo) {
            if (empty($this->dsn)) {
                throw new Exception("Db.Connection.connectionString cannot be empty.");
            }
            try {
                Timer::begin('db-connect');

                $this->_pdo = $this->createPdoInstance();
                $this->initConnection($this->_pdo);
                $this->_active = true;

                $this->pushLog('connect-db', [
                    'lastTime' => Timer::end('db-connect'),
                ]);
            } catch (\PDOException $e) {
                if (PHP_DEBUG) {
                    $err_msg = 'db-connection failed to open the DB connection: ' . $e->getMessage();
                } else {
                    $err_msg = 'db-Connection failed to open the DB connection.';
                }
                throw new Exception($err_msg, (int)$e->getCode(), $e->errorInfo);
            }
        }
    }

    /**
     * 关闭、置空当前的 db-connect
     */
    protected function close()
    {
        $this->_pdo = null;
        $this->_active = false;
    }

    /**
     * 创建 PDO 对象实例
     * @return \PDO
     * @throws Exception
     */
    protected function createPdoInstance()
    {
        $pdoClass = $this->pdoClass;
        if (!class_exists($pdoClass)) {
            throw new Exception(str_cover('db-connection is unable to find PDO class "{className}". Make sure PDO is installed correctly.', [
                '{className}' => $pdoClass
            ]));
        }
        // 创建 PDO 实例
        @$instance = new $pdoClass($this->dsn, $this->username, $this->password);
        if (!$instance) {
            throw new Exception('db-connection failed to open the DB connection.\'');
        }
        return $instance;
    }

    /**
     * db 连接预处理
     * @param \PDO $pdo
     */
    protected function initConnection($pdo)
    {
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        if (null !== $this->emulatePrepare && constant('\PDO::ATTR_EMULATE_PREPARES')) {
            $this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $this->emulatePrepare);
        }

        if (null !== $this->charset) {
            $pdo->exec('SET NAMES ' . $pdo->quote($this->charset));
        }
    }

    /**
     * 返回 PDO 对象实例
     * @return \PDO|null
     */
    public function getPdoInstance()
    {
        return $this->_pdo;
    }

    /**
     * 获取 db-connect 的特殊属性
     * @param int $name
     * @return mixed
     * @throws Exception
     */
    public function getAttribute($name)
    {
        $this->setActive(true);
        return $this->_pdo->getAttribute($name);
    }

    /**
     * 设置 db-connect 的特殊属性
     * @param int $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        if ($this->_pdo instanceof \PDO) {
            $this->_pdo->setAttribute($name, $value);
        } else {
            $this->_attributes[$name] = $value;
        }
    }

    /**
     * 获取数据库的服务信息
     * @return string
     * @throws Exception
     */
    public function getServerInfo()
    {
        return $this->getAttribute(\PDO::ATTR_SERVER_INFO);
    }

    /**
     * 获取数据库的版本信息
     * @return string
     * @throws Exception
     */
    public function getServerVersion()
    {
        return $this->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    /**
     * 获取 db-driver 的版本信息
     * @return string
     * @throws Exception
     */
    public function getClientVersion()
    {
        return $this->getAttribute(\PDO::ATTR_CLIENT_VERSION);
    }

    /**
     * Sql 命令执行器
     * @return Command
     */
    public function createCommand()
    {
        return new Command($this);
    }

    /**
     * 通过SQL语句插入数据库，返回成功插入的数据条数
     * @param string $sql
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function insertSql($sql, $params = [])
    {
        return $this->createCommand()
            ->setText($sql)
            ->execute($params);

    }

    /**
     * 返回一个 db-insert 的builder
     * @return InsertBuilder
     */
    public function getInsertBuilder()
    {
        return new InsertBuilder($this);
    }

    /**
     * 通过数组插入数据库，返回成功插入的数据条数
     * @param string $table
     * @param array $columns
     * @return int
     * @throws Exception
     */
    public function insert($table, array $columns)
    {
        if (empty($columns)) {
            return 0;
        }
        return $this->getInsertBuilder()
            ->setTable($table)
            ->setColumns($columns)
            ->execute();
    }

    /**
     * 通过多数组插入数据库，返回成功插入的数据条数
     * @param string $table
     * @param array $multiData
     * @return int
     * @throws Exception
     */
    public function insertData($table, array $multiData)
    {
        if (empty($multiData)) {
            return 0;
        }

        return $this->getInsertBuilder()
            ->setTable($table)
            ->setMultiFields(array_keys(array_values($multiData)[0]))
            ->setMultiData($multiData)
            ->execute();
    }

    /**
     * 返回最后一条 insert 语句的自增ID
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->createCommand()
            ->getLastInsertId();
    }

    /**
     * @param string $sql
     * @param $params
     * @return int
     * @throws Exception
     */
    public function updateSql($sql, $params = [])
    {
        return $this->createCommand()
            ->setText($sql)
            ->execute($params);
    }

    /**
     * 返回一个 db-update 的builder
     * @return UpdateBuilder
     */
    public function getUpdateBuilder()
    {
        return new UpdateBuilder($this);
    }

    /**
     * 通过数组更新数据库，返回成功更新的数据条数
     * @param string $table
     * @param array $columns
     * @param string $where
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function update($table, array $columns, $where = '', $params = [])
    {
        if (empty($columns)) {
            return 0;
        }
        return $this->getUpdateBuilder()
            ->setTable($table)
            ->setColumns($columns)
            ->setWhere($where)
            ->execute($params);
    }


    public function deleteSql($sql)
    {

        $builder = $this->createBuilder();

    }

    public function findSql($sql)
    {
        $builder = $this->createBuilder();

    }

    public function findCountSql($sql)
    {
        $builder = $this->createBuilder();

    }

    public function findAllSql($sql)
    {
        $builder = $this->createBuilder();

    }

    public function delete($table, $where)
    {
    }

    public function findCount($table, $where)
    {
    }

    public function findData($table, $where)
    {
    }

    public function findAllData($table, $where)
    {
    }

    public function getDeleteBuilder()
    {

    }

    public function getFindBuilder()
    {

    }

}