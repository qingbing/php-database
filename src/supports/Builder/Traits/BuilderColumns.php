<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-12-12
 * Version      :   1.0
 */

namespace DbSupports\Builder\Traits;


trait BuilderColumns
{
    /**
     * 单表插入设置记录数据
     * @param array $data
     * @return $this
     */
    public function setColumns(array $data)
    {
        unset($this->query['columns']);
        foreach ($data as $field => $value) {
            $this->addColumn($field, $value);
        }
        return $this;
    }

    /**
     * 单表插入添加数据
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function addColumn($field, $value)
    {
        if (!isset($this->query['columns'])) {
            $this->query['columns'] = [];
        }
        $this->query['columns'][$field] = $value;
        return $this;
    }
}