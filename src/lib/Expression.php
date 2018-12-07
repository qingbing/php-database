<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-28
 * Version      :   1.0
 */

namespace Db;

use Abstracts\Base;

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