<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Environment-aware database config for the API application.
 *
 * On production servers, the main application's production database.php
 * already exists at ../application/config/production/database.php and contains
 * the correct per-server credentials. We load it directly so we never have to
 * maintain duplicate credential files.
 *
 * When that file doesn't exist (local dev), fall back to XAMPP root credentials.
 */

$query_builder = true;

$parent_db_config = FCPATH . '../application/config/production/database.php';
if (file_exists($parent_db_config)) {
    // Production server — credentials already defined there
    require_once $parent_db_config;
} else {
    // Local development — XAMPP root/mcekknagar
    $db['default'] = array(
        'dsn'          => '',
        'hostname'     => 'localhost',
        'username'     => 'root',
        'password'     => '',
        'database'     => 'mcekknagar',
        'dbdriver'     => 'mysqli',
        'dbprefix'     => '',
        'pconnect'     => false,
        'db_debug'     => (ENVIRONMENT !== 'production'),
        'cache_on'     => false,
        'cachedir'     => '',
        'char_set'     => 'utf8',
        'dbcollat'     => 'utf8_general_ci',
        'swap_pre'     => '',
        'encrypt'      => false,
        'compress'     => false,
        'stricton'     => false,
        'failover'     => array(),
        'save_queries' => true,
    );
}

$active_group = 'default';
