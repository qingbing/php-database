<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 2018/10/29
 * Time: 下午4:24
 */

namespace Db;


use Helper\Base;

class Expression extends Base
{
    public $expression; // DB expression
    public $params = [];

    /**
     * Constructor.
     * @param string $expression
     * @param array $params
     */
    public function __construct($expression, $params = [])
    {
        $this->expression = $expression;
        $this->params = $params;
    }

    /**
     * String magic method
     * @return string the DB expression
     */
    public function __toString()
    {
        return $this->expression;
    }
}