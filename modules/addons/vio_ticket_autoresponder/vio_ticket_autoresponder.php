<?php
/**
 * Ticket Autoresponder
 *
 * Addon module that autoresponds to tickets submitted afterhours or on holidays.
 *
 * @package    WHMCS
 * @author     Venture I/O <code@ventureio.com>
 * @copyright  Copyright (c) Venture I/O 2015
 * @link       http://ventureio.com
 */

if (!defined("WHMCS")) 
    die("This file cannot be accessed directly");

define("taVersion", "1.0.8");

use Illuminate\Database\Capsule\Manager as DB;

function vio_ticket_autoresponder_config() {
    return array(
        "name" => "VIO Ticket Autoresponder",
        "description" => "Addon module that autoresponds to tickets submitted afterhours or on holidays.",
        "version" => taVersion,
        "author" => "Venture I/O",
        "language" => "english",
        "fields" => array()
    );
}

function vio_ticket_autoresponder_activate() {
    DB::table('tbladdonmodules')->insert(array(
        'module' => 'ticket_autoresponder',
        'setting' => 'weekend',
        'value' => 'a:2:{i:0;s:3:"Sun";i:1;s:3:"Sat";}',
    ));
    DB::table('tbladdonmodules')->insert(array(
        'module' => 'ticket_autoresponder',
        'setting' => 'from_hours',
        'value' => '09',
    ));
    DB::table('tbladdonmodules')->insert(array(
        'module' => 'ticket_autoresponder',
        'setting' => 'from_mins',
        'value' => '00',
    ));
    DB::table('tbladdonmodules')->insert(array(
        'module' => 'ticket_autoresponder',
        'setting' => 'from_ampm',
        'value' => 'am',
    ));
    DB::table('tbladdonmodules')->insert(array(
        'module' => 'ticket_autoresponder',
        'setting' => 'to_hours',
        'value' => '05',
    ));
    DB::table('tbladdonmodules')->insert(array(
        'module' => 'ticket_autoresponder',
        'setting' => 'to_mins',
        'value' => '00',
    ));
    DB::table('tbladdonmodules')->insert(array(
        'module' => 'ticket_autoresponder',
        'setting' => 'to_ampm',
        'value' => 'pm',
    ));
    DB::table('tbladdonmodules')->insert(array(
        'module' => 'ticket_autoresponder',
        'setting' => 'holidays',
        'value' => '[]',
    ));
}

function vio_ticket_autoresponder_output($vars) {
    require_once dirname(__FILE__) . '/include/ticket_autoresponder_addon.php';
    $action = empty($_REQUEST['action']) ? 'index' : $_REQUEST['action'];
    $module = new ticket_autoresponder_addon($vars);
    if (!method_exists($module, $action . 'Action')) {
        throw new Exception('Module hasn\'t method ' . $action . 'Action');
    }
    if (!empty($vars['_lang'])) {
        $module->setLanguage($vars['_lang']);
    }
    $module->setModuleLink($vars['modulelink'])->{$action . 'Action'}();
}
