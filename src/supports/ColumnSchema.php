<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-12
 * Version      :   1.0
 */

namespace DbSupports;


use Abstracts\Base;

class ColumnSchema extends Base
{
    /* @var string 列名（不带引用） */
    public $name;
    /* @var string 列名（带引用） */
    public $rawName;
    /* @var bool 是否允许为空 */
    public $allowNull;
    /* @var string 数据表设计类型 */
    public $dbType;
    /* @var string 字段对应的 PHP 类型 */
    public $type;
    /* @var mixed 字段默认值 */
    public $defaultValue;
    /* @var int 字段的长短 */
    public $size;
    /* @var int 数字类型时，数字类型的精度 */
    public $precision;
    /* @var int 数字类型时,标度 */
    public $scale;
    /* @var bool 该字段是否为主键 */
    public $isPrimaryKey;
    /* @var bool 该字段是否为外键 */
    public $isForeignKey;
    /* @var bool 该字段是否为自增 */
    public $autoIncrement = false;
    /* @var string 该字段的备注信息 */
    public $comment = '';

    /**
     * 使用数据库类型和默认值初始化列
     * 设置列的PHP类型，大小，精度，缩放，默认值
     * @param string $dbType
     * @param mixed $defaultValue
     */
    public function init($dbType, $defaultValue)
    {
        $this->dbType = $dbType;
        $this->extractType($dbType);
        $this->extractLimit($dbType);
        if (null !== $defaultValue) {
            $this->extractDefault($defaultValue);
        }
    }

    /**
     * 将 db-type 转换成 php-type
     * @param string $dbType
     */
    protected function extractType($dbType)
    {
        if (0 === strncmp($dbType, 'enum', 4)) {
            $this->type = 'string';
        } else if (false !== strpos($dbType, 'float') || false !== strpos($dbType, 'double')) {
            $this->type = 'double';
        } else if (false !== strpos($dbType, 'bool')) {
            $this->type = 'boolean';
//        } else if (0 === strpos($dbType, 'int') && false === strpos($dbType, 'unsigned') || preg_match('/(bit|tinyint|smallint|mediumint|bigint)/', $dbType)) {
        } else if (0 === strpos($dbType, 'int') || preg_match('/(bit|tinyint|smallint|mediumint|bigint)/', $dbType)) {
            $this->type = 'integer';
        } else {
            $this->type = 'string';
        }
    }

    /**
     * 从列的数据库类型中提取大小、精度、标度信息
     * @param string $dbType
     */
    protected function extractLimit($dbType)
    {
        if (0 === strncmp($dbType, 'enum', 4) && preg_match('/\(([\'"])(.*)\\1\)/', $dbType, $matches)) {
            // explode by (single or double) quote and comma (ENUM values may contain commas)
            $values = explode($matches[1] . ',' . $matches[1], $matches[2]);
            $size = 0;
            foreach ($values as $value) {
                if (($n = strlen($value)) > $size) {
                    $size = $n;
                }
            }
            $this->size = $this->precision = $size;
        } else {
            if (strpos($dbType, '(') && preg_match('/\((.*)\)/', $dbType, $matches)) {
                $values = explode(',', $matches[1]);
                $this->size = $this->precision = (int)$values[0];
                if (isset($values[1])) {
                    $this->scale = (int)$values[1];
                }
            }
        }
    }

    /**
     * 提取列的默认值
     * @param mixed $defaultValue
     */
    protected function extractDefault($defaultValue)
    {
        if (0 === strncmp($this->dbType, 'bit', 3)) {
            $this->defaultValue = bindec(trim($defaultValue, 'b\''));
        } elseif (('timestamp' === $this->dbType || 'datetime' === $this->dbType) && ('CURRENT_TIMESTAMP' === $defaultValue || 'current_timestamp()' === $defaultValue)) {
            $this->defaultValue = null;
        } else {
            $this->defaultValue = $this->typecast($defaultValue);
        }
    }

    /**
     * 将 php值 转换或 db-type
     * @param mixed $value
     * @return mixed converted value
     */
    public function typecast($value)
    {
        if (gettype($value) === $this->type || null === $value) {
            return $value;
        }
        if ('' === $value && $this->allowNull) {
            return $this->type === 'string' ? '' : null;
        }
        switch ($this->type) {
            case 'string':
                return (string)$value;
            case 'integer':
                return (integer)$value;
            case 'boolean':
                return (boolean)$value;
            case 'double':
            default:
                return $value;
        }
    }
}