<?php
$serverName        = "77.68.113.42";
$connectionOptions = array(
    "Database" => "EBPORTAL",
    "Uid"      => "OakDev",
    "PWD"      => "oakj4o0Nj@bisoHUBLLH",
    "TrustServerCertificate"=> 'Yes',
    "Encrypt"=>'Yes',
);

$connectionOptions2 = array(
    "Database" => "erp",
    "Uid"      => "OakDev",
    "PWD"      => "oakj4o0Nj@bisoHUBLLH",
    "TrustServerCertificate"=> 'Yes',
    "Encrypt"=>'Yes',
);
//Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);
$conn_v = sqlsrv_connect($serverName, $connectionOptions2);

if (!$conn) {
    // Do NOT log or expose sensitive SQL details
    header("HTTP/1.1 503 Service Unavailable");
    echo "We are experiencing temporary technical issues. Please try again later.";
    exit;
}


if (!$conn_v) {
    // Do NOT log or expose sensitive SQL details
    header("HTTP/1.1 503 Service Unavailable");
    echo "We are experiencing temporary technical issues. Please try again later.";
    exit;
}


$tstamp= date('Y-m-d');
session_start();
?>
