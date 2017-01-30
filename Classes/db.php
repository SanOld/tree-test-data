<?php

class db_pdo {

    protected $db;

    public function __construct()
    {
		$this->db = new PDO('mysql:host=localhost;dbname=test_ocad_tree_data;charset=utf8','root','');
    }

}