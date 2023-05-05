<?php

/**
 * Plugin Name: Shopify Order Notification
 * Plugin URI: https://agencialaf.com
 * Description: Descrição do Shopify Order Notification.
 * Version: 0.0.1
 * Author: Ingo Stramm
 * Text Domain: son
 * License: GPLv2
 */

defined('ABSPATH') or die('No script kiddies please!');

define('SON_DIR', plugin_dir_path(__FILE__));
define('SON_URL', plugin_dir_url(__FILE__));

function son_debug($debug)
{
    echo '<pre>';
    var_dump($debug);
    echo '</pre>';
}

require_once 'tgm/tgm.php';
require_once 'classes/classes.php';
require_once 'scripts.php';
require_once 'custom-post-type.php';
require_once 'cmb2.php';
require_once 'functions.php';

require 'plugin-update-checker-4.10/plugin-update-checker.php';
$updateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://raw.githubusercontent.com/IngoStramm/shopify-order-notification/master/info.json',
    __FILE__,
    'son'
);
