<?php

require_once('vendor/autoload.php');

use Foolz\SphinxQL\Drivers\Mysqli\Connection;

class db_sphinx {

    protected $sphinx;

    public function __construct()
    {
        $this->sphinx = new Connection();
        $this->sphinx->setParams(array('host' => 'localhost', 'port' => 9306));
    }

}