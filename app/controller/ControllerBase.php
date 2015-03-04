<?php

// Base controller
class ControllerBase extends \Prefab
{
    
    protected $f3;

    //! Instantiate class
    function __construct()
    {
        $this->f3 = Base::instance();
    }

}
