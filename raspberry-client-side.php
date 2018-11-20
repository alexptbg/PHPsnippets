<?php
header('Content-Type: text/html; charset=utf-8');
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
ini_set('display_errors', true);
//master server
//define('MASTER_SERVER',''); // set database host
//define('MASTER_USER',''); // set database user
//define('MASTER_PASS',''); // set database password
//define('MASTER_DB',''); // set database name
//con
//$master = new mysqli(MASTER_SERVER,MASTER_USER,MASTER_PASS,MASTER_DB);
//time
//$now = date("Y-m-d H:i:s");
//app name important part
$app = "KA-WALL";
//app version
$version = "1.0";
//revision 
$revision = "r1";
//status Online|Offline|Deactivated|Dead
$status = "Active";
//ip addr
//real ip
//$ipaddr = shell_exec("curl https://ka-ex.net/ip.php");
//if (empty($ipaddr)) {
//    $ipaddr = "::1";
//}
//ethernet
$eth0 = shell_exec("/sbin/ifconfig eth0 | grep 'inet ' | cut -d: -f2 | awk '{ print $2}'");
if (empty($eth0)) {
    $eth0 = "::1";
}
//wireless
$wlan0 = shell_exec("/sbin/ifconfig wlan0 | grep 'inet ' | cut -d: -f2 | awk '{ print $2}'");
if (empty($eth0)) {
    $wlan0 = "::1";
}
//serial
$serial = shell_exec("cat /proc/cpuinfo | grep Serial | cut -d ' ' -f 2");
if (!$serial) {
    $serial = "0";
}
//get cpu temperature
$cpu = shell_exec("cat /sys/class/thermal/thermal_zone0/temp");
$cpufix = 0;
$cpu_temp = number_format($cpu/1000,1);
$cpu_temp = number_format($cpu_temp-$cpufix,1);
//build sql string
/*
$sql="INSERT INTO `raspi_status` (datetime,ipaddr,eth,wlan,serial,cpu_temp,app,version,revision,status) 
      VALUES ('".$now."','".$ipaddr."','".$eth0."','".$wlan0."','".$serial."','".$cpu_temp."','".$app."','".$version."','".$revision."','".$status."')";
echo $sql;

//sql query
if($master->query($sql) === false) {
    trigger_error('Wrong SQL: '.$sql.' Error: '.$master->error,E_USER_ERROR);
} else {
    echo "Resource id #".$master->insert_id. " inserted.";
}
//end
**/
$url = "https://ka-ex.net/raspberry.php?in=1&eth0=".preg_replace('/\s+/','',$eth0)."&wlan0=".preg_replace('/\s+/','',$wlan0)."&serial=".preg_replace('/\s+/','',$serial)."&cpu_temp=$cpu_temp&app=$app&version=$version&revision=$revision&status=$status";
//$url = "https://ka-ex.net/raspberry.php";
//print "<pre>";
print_r(get_web_page($url));
//print "</pre>";
/*
echo $url."<br/>";
//  Initiate curl
$ch = curl_init();
// Disable SSL verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the url
curl_setopt($ch, CURLOPT_URL,$url);
// Execute
$result=curl_exec($ch);
if (curl_errno($ch)) { 
   print curl_error($ch); 
} 
// Closing
curl_close($ch);
//echo($result);
$resp = json_decode($result,true);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($resp);
*/
function get_web_page($url){
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );
    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header['content'];
}
?>
