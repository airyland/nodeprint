<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

define('STATIC_DOMAIN','');
/*
  |--------------------------------------------------------------------------
  | File and Directory Modes
  |--------------------------------------------------------------------------
  |
  | These prefs are used when checking and setting modes when working
  | with the file system.  The defaults are fine on servers with proper
  | security, but you may wish (or even need) to change the values in
  | certain environments (Apache running a separate process for each
  | user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
  | always be used to set the mode correctly.
  |
 */
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
  |--------------------------------------------------------------------------
  | File Stream Modes
  |--------------------------------------------------------------------------
  |
  | These modes are used when working with fopen()/popen()
  |
 */

define('FOPEN_READ', 'rb');
define('FOPEN_READ_WRITE', 'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 'ab');
define('FOPEN_READ_WRITE_CREATE', 'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
  |--------------------------------------------------------------------------
  | NodePrint setting
  |--------------------------------------------------------------------------
  |
  |
  |
 */


define('NP_PREFIX', APPPATH);
define('SMARTY_TEMPLATE_DIR', NP_PREFIX . '/templates');
define('SMARTY_COMPILE_DIR', NP_PREFIX . '/cache/smarty/compile');
define('SMARTY_CACHE_DIR', NP_PREFIX . '/cache/smarty/cache');
define('SMARTY_CONFIG_DIR', NP_PREFIX . '/config');

define('NP_PLUGINS_DIR', 'application/plugins');


/* End of file constants.php */
/* Location: ./application/config/constants.php */