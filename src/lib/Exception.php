<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-28
 * Version      :   1.0
 */

namespace Db;

class Exception extends \Helper\Exception
{
    /**
     * 具体的错误信息
     * @var mixed
     */
    public $errorInfo;

    /**
     * 构造器
     * @param string $message PDO error message
     * @param integer $code PDO error code
     * @param mixed $errorInfo PDO error info
     */
    public function __construct($message, $code = 0, $errorInfo = null)
    {
        $this->errorInfo = $errorInfo;
        parent::__construct($message, $code);
    }

    /**
     * 返回异常类型
     * @return string
     */
    public function getName()
    {
        return 'Db-Exception';
    }
}