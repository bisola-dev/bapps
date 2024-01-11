<?php
$serverName        = "213.171.204.36";
$connectionOptions = array(
    "Database" => "EBPORTAL",
    "Uid"      => "Bisola_new",
    "PWD"      => "eiu947qwbjgf@#455",
    "TrustServerCertificate"=> 'Yes',
    "Encrypt"=>'Yes',
);

$connectionOptions2 = array(
    "Database" => "erp",
    "Uid"      => "Bisola_new",
    "PWD"      => "eiu947qwbjgf@#455",
    "TrustServerCertificate"=> 'Yes',
    "Encrypt"=>'Yes',
);
//Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);
$conn_v = sqlsrv_connect($serverName, $connectionOptions2);

if (!$conn) {
    die(FormatErrors(sqlsrv_errors()));
  
}
//else
//{
   // echo "CONNECTION SUCCESSFUL TO EBPORTAL<br>";
//}

if (!$conn_v) {
    die(FormatErrors(sqlsrv_errors()));
  
}

function FormatErrors($errors)
{
    /* Display errors. */
    echo "Error information: ";

    foreach ($errors as $error) {
        echo "SQLSTATE: " . $error['SQLSTATE'] . "";
        echo '<br>';
        echo "Code: " . $error['code'] . "";
        echo '<br>';

        echo "Message: " . $error['message'] . "";

    }
    

}
$tstamp= date('Y-m-d');
session_start();
?>
