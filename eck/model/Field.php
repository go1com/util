<?php

namespace go1\util\eck\model;

class Field
{
    private $id;
    private $columns;

    public function __construct($id, array $columns)
    {
        $this->id = $id ? intval($id) : null;
        $this->columns = $columns;
    }

    public function columns()
    {
        return $this->columns;
    }
}
