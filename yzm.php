<?php
namespace Framework;
include 'verify.php';

$verify=new Verify();
$verify->getImg();


session_start();

$_SESSION['verify']=Verify::$getCode;
// var_dump($_SESSION);