<?php
header('Content-Type: text/html; charset=utf-8');
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
ini_set('display_errors', true);
//master server
define('MASTER_SERVER',''); // set database host
define('MASTER_USER',''); // set database user
define('MASTER_PASS',''); // set database password
define('MASTER_DB',''); // set database name
//init
if((!empty($_GET))&&($_GET['in']=="1")){
    //con
    $master = new mysqli(MASTER_SERVER,MASTER_USER,MASTER_PASS,MASTER_DB);
    //time
    $now = date("Y-m-d H:i:s");
    /*
77.77.29.214 	192.168.0.90
		0000000031ad39d6
	47.8 	Ka-Wash 	1.0 	r1 	Active
	*/
	//?in=1&ipaddr=77.77.29.214&eth0=192.168.0.90&wlan0=::1&serial=0000000031ad39d6&cpu_temp=47.8&app=Ka-Wash&version=1.0&revision=r1&status=Active
	$ipaddr = $_SERVER['REMOTE_ADDR'];
	$eth0 = $_GET['eth0'];
	$wlan0 = $_GET['wlan0'];
	$serial = $_GET['serial'];
	$cpu_temp = $_GET['cpu_temp'];
	$app = $_GET['app'];
	$version = $_GET['version'];
	$revision = $_GET['revision'];
	$status = $_GET['status'];
    //build sql string
    $sql="INSERT INTO `raspi_status` (datetime,ipaddr,eth,wlan,serial,cpu_temp,app,version,revision,status) 
          VALUES ('".$now."','".$ipaddr."','".$eth0."','".$wlan0."','".$serial."','".$cpu_temp."','".$app."','".$version."','".$revision."','".$status."')";
    //sql query
    if($master->query($sql) === false) {
        trigger_error('Wrong SQL: '.$sql.' Error: '.$master->error,E_USER_ERROR);
    } else {
        echo "Resource id #".$master->insert_id. " inserted.";
    }
    //end
} else {
	echo "die;";die;
}
?>