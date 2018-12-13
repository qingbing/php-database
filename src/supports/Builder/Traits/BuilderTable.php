<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-12
 * Version      :   1.0
 */

namespace DbSupports\Builder\Traits;


trait BuilderTable
{
    /**
     * 设置操作表
     * @param string $table
     * @return $this
     */
    public function setTable($table)
    {
        if (!empty($table)) {
            $this->query['table'] = $table;
        }
        return $this;
    }
}