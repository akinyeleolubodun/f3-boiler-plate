<?php

// Base Model
class ModelBase {

    protected $db;
    protected $f3;

    //Model Constructor
    function __construct()
    {
        $this->f3 = Base::instance();
        
        $this->db = \Registry::get('db');
    }

}
