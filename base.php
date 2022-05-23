<?php

$folder_level = "./";
while (!file_exists($folder_level."base.php")) {$folder_level .= "../";}
define("BASEDIR", $folder_level);
include(BASEDIR . "vendor/autoload.php");

include(BASEDIR . "controller/Request.php");
include(BASEDIR . "controller/IPP.php");
include(BASEDIR . "controller/IPPPayments.php");
include(BASEDIR . "controller/IPPCurrency.php");
include(BASEDIR . "controller/IPPPartner.php");
include(BASEDIR . "controller/IPPPlugins.php");

if (file_exists(BASEDIR . "ipp-config.php")) {
    include BASEDIR . "ipp-config.php";
} else {
    echo "Rename ipp-config-sample.php to ipp-config.php and insert your Partner ID.";
    die();
}
$_ENV           = $IPP_CONFIG;
$id             = isset($_COOKIE["ipp_id"]) ? $_COOKIE["ipp_id"] : "";
$session_id     = isset($_COOKIE["ipp_session_id"]) ? $_COOKIE["ipp_session_id"] : "";
$login_type     = isset($_COOKIE["ipp_type"]) ? $_COOKIE["ipp_type"] : "";

$request    = new IPPRequest();
$ipp        = new IPP($request,$id,$session_id);
$partner    = new IPPPartner($request,$id,$session_id);

$plugins    = new IPPPlugins($request);
$currency   = new IPPCurrency();
$RequestP   = new RequestParams($request);
$mcc        = new MCC();

$REQ        = $RequestP->getRequestParams($_SERVER["REQUEST_METHOD"]);

$inline_css = [];
$inline_script = [];
$load_script = [];

if(isset($partner_page) && $partner_page == 1) {
    $data = $partner->checkLogin();
    if(!$data->success) {
        header("Location: /");
        die();
    }
    require_once("theme/".$_ENV["THEME"]."/partner/head.php");
    require_once("theme/".$_ENV["THEME"]."/partner/foot.php");
}
elseif(!isset($public_page) || (isset($public_page) && !$public_page)) {
    $data = $ipp->checkLogin();
    if(!$data->success) {
        header("Location: /");
        die();
    }
    require_once("theme/".$_ENV["THEME"]."/head.php");
    require_once("theme/".$_ENV["THEME"]."/foot.php");
}
$plugins->loadPlugins();


require_once(BASEDIR . "language/en-gb.php");
