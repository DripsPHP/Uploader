<?php

define("VENDOR_SRC", __DIR__."/vendor");
define("AUTOLOAD_FILE", VENDOR_SRC."/autoload.php");

require_once AUTOLOAD_FILE;

use Drips\Uploader\Uploader;
use Drips\HTTP\Request;

$request = new Request();

$uploader = new Uploader();

$path_parts = explode("/", $_SERVER['SCRIPT_FILENAME']);
array_pop($path_parts);
$upload_dir = implode("/", $path_parts)."/uploaded_files";

if($request->isPut()) {
    
    //Pretend $name is the filename specified in the put url
    $name = "test.txt";
    $uploader->upload($name, $upload_dir);
    
} elseif($request->isPost()) {
    $uploader->upload("data", $upload_dir);
}

