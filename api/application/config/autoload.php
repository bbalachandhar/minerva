<?php

defined('BASEPATH') OR exit('No direct script access allowed');

$autoload['packages'] = array();

/*
 * Auto-load the database library so $this->db is available in all models.
 * Everything else is loaded manually in each controller/library as needed.
 */
$autoload['libraries'] = array('database', 'session');

$autoload['drivers'] = array();

$autoload['helper'] = array('url', 'json_output', 'custom');

$autoload['config'] = array();

$autoload['language'] = array();

$autoload['model'] = array();
