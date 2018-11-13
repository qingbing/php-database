<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-28
 * Version      :   1.0
 */

namespace Db;

use Helper\Base;

class ColumnSchema extends Base
{
    /**
     * 列名（不带引用）
     * @var string
     */
    public $name;
    /**
     * 列名（带引用）
     * @var string
     */
    public $rawName;
    /**
     * 是否允许为空
     * @var bool
     */
    public $allowNull;
    /**
     * 数据表设计类型
     * @var string
     */
    public $dbType;
    /**
     * 字段对应的 PHP 类型
     * @var string
     */
    public $type;
    /**
     * 字段默认值
     * @var mixed
     */
    public $defaultValue;
    /**
     * 字段的长短
     * @var int
     */
    public $size;
    /**
     * 数字类型时，数字类型的精度
     * @var int
     */
    public $precision;
    /**
     * 数字类型时,标度
     * @var int
     */
    public $scale;
    /**
     * 该字段是否为主键
     * @var bool
     */
    public $isPrimaryKey;
    /**
     * 该字段是否为外键
     * @var bool
     */
    public $isForeignKey;
    /**
     * 该字段是否为自增
     * @var bool
     */
    public $autoIncrement = false;
    /**
     * 该字段的备注信息
     * @var string
     */
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
        } else if (0 === strpos($dbType, 'int') && false === strpos($dbType, 'unsigned') || preg_match('/(bit|tinyint|smallint|mediumint)/', $dbType)) {
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
        } else if ('timestamp' === $this->dbType && 'CURRENT_TIMESTAMP' === $defaultValue) {
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