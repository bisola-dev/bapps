<?php
require_once('connection.php');

// $crumb='HttpContext.Current.Request.Cookies("userId").Value.ToString';



 //$crumb=6;
 $crumb=$_COOKIE['userId'];




try {

if (isset($_POST['submit'])) {

$mno = trim(strip_tags($_POST['mno']));
$amt = trim(strip_tags($_POST['amt']));
$sidd = trim(strip_tags($_POST['sidd']));
$stat =trim(strip_tags($_POST['stat']));
$pidd = trim(strip_tags($_POST['pidd']));

//echo $mno.' '.$amt.' '.$sidd.' '.$pidd;

   
$Amount = (float) str_replace(",", "", $amt); 
  // For displaying the formatted amount with commas

$formattedAmount = number_format($Amount, 2, '.', ',');  


if ($mno == "" || $amt==""||$sidd==""||$pidd=="") {
   echo '<script type ="text/javascript">
   alert("please do not submit empty form. All fields are compulsory.")
   </script>';} 
  

 $queryy21 = "SELECT Session from Sessions where SessionID=$sidd";
 $user_query21 = sqlsrv_query($conn, $queryy21);
while ($row = sqlsrv_fetch_array($user_query21))  {
   $session=$row['Session'];

  // echo $session ;

}


 $query34 = "SELECT PaymentName from YCTPAY_Payments WHERE PaymentID=$pidd";
  $user_query34 = sqlsrv_query($conn, $query34);
 while ($row2 = sqlsrv_fetch_array($user_query34))  {
    $pnames=$row2['PaymentName'];

  $description= 'payment of '.$pnames;}



$dre = "SELECT [Uname] FROM [erp].[dbo].[Users] WHERE id=$crumb";
$gran = sqlsrv_query($conn, $dre);
while ($til = sqlsrv_fetch_array($gran))  {
    $Uname = $til['Uname'];

}      


$query3 = "SELECT * FROM [EBPORTAL].[dbo].vw_biodata WHERE Matricnum = ? OR Appnum = ?";
$params = array($mno, $mno);

$user12 = sqlsrv_query($conn, $query3, $params);

if ($user12 === false) {
    die(print_r(sqlsrv_errors(), true)); // Print errors if query fails
}

$rowz = sqlsrv_fetch_array($user12, SQLSRV_FETCH_ASSOC);

//echo $rowz;

if ($rowz !== null) {
    $mno2 = $rowz['Matricnum'];
    $Phone = $rowz['Phone'];
    $Email = $rowz['Email'];
    $sun2 = trim($rowz['Surname']);
    $fir = trim($rowz['Firstname']);

    $name = $sun2 . ' ' . $fir;
} 

else if ($pidd == 1 || $pidd == 2 || $pidd == 3|| $pidd == 4) {
   echo '<script type ="text/javascript">
   alert("Sorry, Payment cant be made on the selected item.")
   </script>';} 

else {
    echo '<script type="text/javascript">
    alert("Matricnumber or Appnumber not found, please input correctly.");
    </script>';
}

//echo $mno.' '.$amt.' '.$sidd.' '.$pidd;
// Posting Values to REST WebService

if (isset($rowz['Matricnum'])) {
   $mno2 = $rowz['Matricnum'];
   $Phone = $rowz['Phone'];
   $Email = $rowz['Email'];
   $sun2 = trim($rowz['Surname']);
   $fir = trim($rowz['Firstname']);
   $name = $sun2 . ' ' . $fir;


$user='anty';
$token='antymi';
$xml = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
<soap:Body>
<geninvAsync xmlns="http://paymentsys.portal.yabatech.edu.ng/">
<amount>'.$Amount.'</amount>
<name>'.$name.'</name>
<phone>'.$Phone.'</phone>
<email>'.$Email.'</email>
<description>'.$description.'</description>
<matno>'.$mno.'</matno>
<paymentid>'.$pidd.'</paymentid>
<session>'.$session.'</session>
<user>'.$user.' </user>
<token>'.$token.'</token>
</geninvAsync>
</soap:Body>
</soap:Envelope>';

//echo $xml;
//exit;


//The URL that you want to send your XML to.
$url = 'https://portal.yabatech.edu.ng/paymentsys/webservice1.asmx?op=geninvAsync';
//Initiate cURL
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type:text/xml"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
// Retune your response from CURL here
$result = curl_exec($curl);

// To load get your response in JSON format the below code is required
$cleanData = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3' , $result); 
$convertToString=simplexml_load_string($cleanData);
$encodingToJson=json_encode($convertToString); 
//$responseArray=json_decode($json, true); 
if (curl_errno($curl)) { 
throw newException(curl_error($curl)); 
} 
curl_close($curl); //echo $json; 
$decodeJson=json_decode($encodingToJson); 
$data=($decodeJson->soapBody->geninvAsyncResponse->geninvAsyncResult);

//echo $data;
//exit;

if ($data==""){
 echo '<script type="text/javascript">
 alert("Error processing request, please Try again Later.");
    </script>'; 
   }

else if($data =='0' || $data =='1'){
  echo '<script type="text/javascript">
  alert("Invalid Credentials! You are not eligible to proceed with this payment, Please contact CITM for further assistance.");
  </script>';  
 }

  
 else{
   $name = $sun2 . ' ' . $fir;
   $description= 'payment of '.$pnames;
   $redirectUrl = "numdisplay.php?data=" . urlencode($data) . "&name=" . urlencode($name) . "&description=" . urlencode($description);


$show=sqlsrv_query($conn,"INSERT INTO [EBPORTAL].[dbo].[PendingSchoolFees] (matricnum, amount, status, paymentid, datecreated, sessionid) values ('$mno', $formattedAmount, 1, $pidd, '$tstamp', $sidd)");
 $reen=sqlsrv_query($conn_v, "INSERT INTO [erp].[dbo].[erpLog] (user_id, log_date, action, user_ip, studnum)  values ('$Uname', getdate(), 'registered pending payment', 'service user', '$mno')");  
 
 echo '<script type="text/javascript">
 alert("Details submitted. Please click OK to view the remitta number.");
window.location.href = "'.$redirectUrl.'";
</script>';

}

 
}


}


} catch (Exception $e) {
   // Handle the exception here
   echo "An error occurred: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
   
<!-- Mirrored from thememinister.com/crm/add-customer.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 27 Aug 2019 13:28:08 GMT -->
<head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>pending payment</title>
      <!-- Favicon and touch icons -->
      <link rel="shortcut icon" href="assets/dist/img/ico/favicon.png" type="image/x-icon">
      <!-- Start Global Mandatory Style
         =====================================================================-->
      <!-- jquery-ui css -->
      <link href="assets/plugins/jquery-ui-1.12.1/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
      <!-- Bootstrap -->
      <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
      <!-- Bootstrap rtl -->
      <!--<link href="assets/bootstrap-rtl/bootstrap-rtl.min.css" rel="stylesheet" type="text/css"/>-->
      <!-- Lobipanel css -->
      <link href="assets/plugins/lobipanel/lobipanel.min.css" rel="stylesheet" type="text/css"/>
      <!-- Pace css -->
      <link href="assets/plugins/pace/flash.css" rel="stylesheet" type="text/css"/>
      <!-- Font Awesome -->
      <link href="assets/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
      <!-- Pe-icon -->
      <link href="assets/pe-icon-7-stroke/css/pe-icon-7-stroke.css" rel="stylesheet" type="text/css"/>
      <!-- Themify icons -->
      <link href="assets/themify-icons/themify-icons.css" rel="stylesheet" type="text/css"/>
      <!-- End Global Mandatory Style
         =====================================================================-->
      <!-- Start Theme Layout Style
         =====================================================================-->
      <!-- Theme style -->
      <link href="assets/dist/css/stylecrm.css" rel="stylesheet" type="text/css"/>
      <!-- Theme style rtl -->
      <!--<link href="assets/dist/css/stylecrm-rtl.css" rel="stylesheet" type="text/css"/>-->
      <!-- End Theme Layout Style
         =====================================================================-->
   </head>
  
            <!-- Main content -->
            <section class="content">
               <div class="row">
                  <!-- Form controls -->
                  <div class="col-sm-12">
                     <div class="panel panel-bd ">
                        <div class="panel-heading">
                           <div class="btn-group" > 
                             <h4> Pending payments</h4>   
                           </div>
                        </div>
                        <div class="panel-body">
                           <form class="col-sm-6" method="POST" action="" >
                                
                              <div class="form-group">
                                 <label>Application / Matric number</label>        
                                 <input type="text" name="mno" class="form-control" placeholder="enter matric number here " required>
                              </div>


                               <div class="form-group">
                                 <label> Amount (₦)</label>        
                                 <input type="text" name="amt" class="form-control" placeholder="Enter pending amount to be paid" required>
                              </div>

                              
                               <div class="form-group">  
                                 <input type="hidden" name="stat" class="form-control" value= "1" >
                              </div>

                            <div class="form-group">
                          <label>Payment </label>        
                          <select class="form-control"  name="pidd" required> 
                         <option value="">-- Select --</option>

                         <?php                    
                    $query3 = "SELECT PaymentID,PaymentName from YCTPAY_Payments";
                  $user_query3 = sqlsrv_query($conn, $query3);
                 while ($row2 = sqlsrv_fetch_array($user_query3))  {
                 $pid = $row2['PaymentID'];
                 $pname=$row2['PaymentName'];

             echo "<option value = '" . $pid . "'>" . $pname . "</option>";}
           ?>
         </select>
          </div>

                 <div class="form-group">
                <label> Session</label>        
          <select class="form-control"  name="sidd" required> 
         <option value="">-- Select --</option>

         <?php                  
      $queryy = "SELECT SessionID,Session from Sessions";
     $user_query32 = sqlsrv_query($conn, $queryy);
     while ($row = sqlsrv_fetch_array($user_query32))  {
    $sid = $row['SessionID'];
    $sname=$row['Session'];

    echo "<option value = '" . $sid . "'>" . $sname . "</option>";}
     ?>
       </select>
        </div>
                      <div>
                            <button type="submit" name="submit" class="btn btn-warning">Submit</button>   
                           </div>

            
                            </div>
                              </div>
                              </div>
                              </div>
                           </form>
                        </div>
                     </div>
                  </div>
               </div>
            </section>
            <!-- /.content -->


               
                                 </tbody>
                              </table> 
                           </div>
                        </div>
                   
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>

         </div>
         <!-- /.content-wrapper -->
        <footer class="main-footer">
        <strong> Copyright &copy; <?php echo date("Y"); ?> <a href="#">Yaba college of technology</a>.</strong> All rights reserved.
         </footer>
      </div>
      <!-- ./wrapper -->
      <!-- Start Core Plugins
         =====================================================================-->
      <!-- jQuery -->
      <script src="assets/plugins/jQuery/jquery-1.12.4.min.js" type="text/javascript"></script>
      <!-- jquery-ui --> 
      <script src="assets/plugins/jquery-ui-1.12.1/jquery-ui.min.js" type="text/javascript"></script>
      <!-- Bootstrap -->
      <script src="assets/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
      <!-- lobipanel -->
      <script src="assets/plugins/lobipanel/lobipanel.min.js" type="text/javascript"></script>
      <!-- Pace js -->
      <script src="assets/plugins/pace/pace.min.js" type="text/javascript"></script>
      <!-- SlimScroll -->
      <script src="assets/plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
      <!-- FastClick -->
      <script src="assets/plugins/fastclick/fastclick.min.js" type="text/javascript"></script>
      <!-- CRMadmin frame -->
      <script src="assets/dist/js/custom.js" type="text/javascript"></script>
      <!-- End Core Plugins
         =====================================================================-->
      <!-- Start Theme label Script
         =====================================================================-->
      <!-- Dashboard js -->
      <script src="assets/dist/js/dashboard.js" type="text/javascript"></script>
      <!-- End Theme label Script
         =====================================================================-->
   </body>

<!-- Mirrored from thememinister.com/crm/add-customer.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 27 Aug 2019 13:28:08 GMT -->
</html>







