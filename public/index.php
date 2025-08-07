<?php
namespace Net\MJDawson\LiveChat;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.'kernal.php';
$kernal = new kernal;

if($kernal->user()->get() === null){
    $kernal->showLogin();
}else{
    $kernal->showChat();
}
$kernal->quit();