<?php

date_default_timezone_set('Asia/Jakarta');
define('DATE_NOW', date('Y-m-d H:i:s', time()));

// $base_url_ = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http');
// $base_url_ .= '://'.$_SERVER['HTTP_HOST'];
// $base_url_ .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
// define('BASE_URL', $base_url_);
define('ADMIN_PATH', '/app');
define('API_PATH', '/api');
// define('SALT', 'hehehehe');
setlocale(LC_ALL, 'IND');

$dirArray = explode(DIRECTORY_SEPARATOR, __DIR__);
$filesDir = '';

for ($i = 0; $i < count($dirArray) - 2; ++$i) {
    $filesDir .= $dirArray[$i] . DIRECTORY_SEPARATOR;
}

define('FILESDIR', $filesDir . 'public_html');
// define('FILESDIR', ROOTPATH.'public');

function getUrlParam($param, $default = '')
{
    return isset($_REQUEST[$param]) ? ($_REQUEST[$param] == '' ? $default : $_REQUEST[$param]) : $default;
}

/*
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the frameworks
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @link: https://codeigniter4.github.io/CodeIgniter4/
 */