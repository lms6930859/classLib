<?php
session_start();

include 'Ucpaas.php';
$phone=$_POST['phone'];
//$dx=new Ucpaas();
$str='0123456789';
$str1=str_shuffle($str);
$str2=substr($str1,1,4);
$_SESSION['dx']=$str2;
//var_dump($_SESSION);die;
$options['accountsid']='01282a1b7e2586ed35b0d12453f726b8';
$options['token']='1df20fbea00e1b78818ca920f2522328
';
$ucpass = new Ucpaas($options);
$appId = "5944f611142d4b838f26b15a24beb795";
$to = $phone;
$templateId = "186734";
$param=$str2;
return $ucpass->templateSMS($appId,$to,$templateId,$param);
