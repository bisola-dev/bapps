<?php
require_once('connection.php');
require_once('auth.php');

// Validate user
$userData = validateUserToken();
$_SESSION['verified_userid'] = $userData['userId'];
$_SESSION['verified_username'] = $userData['userName'];

$crumb = $_SESSION['verified_userid'];
$Uname = $_SESSION['verified_username'];

$crumb=6;


if (isset($_POST['submit'])) {
    // Sanitize inputs
    $mno = trim(strip_tags($_POST['mno']));
    $amt = trim(strip_tags($_POST['amt']));
    $sidd = trim(strip_tags($_POST['sidd']));
    $pidd = trim(strip_tags($_POST['pidd']));

    // Validate required fields
    if (empty($mno) || empty($amt) || empty($sidd) || empty($pidd)) {
        echo '<script>alert("Please fill all required fields.");</script>';
        return;
    }

    // Validate amount
    $Amount = (float) str_replace(",", "", $amt);
    if ($Amount <= 0) {
        echo '<script>alert("Please enter a valid amount.");</script>';
        return;
    }
    $formattedAmount = number_format($Amount, 2, '.', ',');

    // Get session
    $querySession = "SELECT Session FROM Sessions WHERE SessionID = ?";
    $paramsSession = [$sidd];
    $resultSession = sqlsrv_query($conn, $querySession, $paramsSession);
    if ($resultSession === false) {
        echo '<script>alert("Error retrieving session.");</script>';
        return;
    }
    $sessionRow = sqlsrv_fetch_array($resultSession, SQLSRV_FETCH_ASSOC);
    $session = $sessionRow['Session'] ?? '';

    // Get payment name
    $queryPayment = "SELECT PaymentName FROM YCTPAY_Payments WHERE PaymentID = ?";
    $paramsPayment = [$pidd];
    $resultPayment = sqlsrv_query($conn, $queryPayment, $paramsPayment);
    if ($resultPayment === false) {
        echo '<script>alert("Error retrieving payment details.");</script>';
        return;
    }
    $paymentRow = sqlsrv_fetch_array($resultPayment, SQLSRV_FETCH_ASSOC);
    $pnames = $paymentRow['PaymentName'] ?? '';
    $description = 'payment of ' . $pnames;

    // Get student biodata
    $queryBiodata = "SELECT * FROM [EBPORTAL].[dbo].vw_biodata WHERE Matricnum = ? OR Appnum = ?";
    $paramsBiodata = [$mno, $mno];
    $resultBiodata = sqlsrv_query($conn, $queryBiodata, $paramsBiodata);
    if ($resultBiodata === false) {
        echo '<script>alert("Database error. Please try again.");</script>';
        return;
    }
    $rowz = sqlsrv_fetch_array($resultBiodata, SQLSRV_FETCH_ASSOC);

    if ($rowz !== null) {
        $Phone = $rowz['Phone'];
        $Email = $rowz['Email'];
        $sun2 = trim($rowz['Surname']);
        $fir = trim($rowz['Firstname']);
        $name = $sun2 . ' ' . $fir;
    } elseif (in_array($pidd, [1, 2, 3, 4])) {
        echo '<script>alert("Sorry, Payment cannot be made on the selected item.");</script>';
        return;
    } else {
        echo '<script>alert("Matric number or Application number not found, please input correctly.");</script>';
        return;
    }

    // Proceed with SOAP call if biodata found
    if ($rowz !== null) {
        // SOAP credentials
        $user = 'anty';
        $token = 'antymi';

        // Build SOAP XML
        $xml = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <GenerateInvoice xmlns="http://portal.yabatech.edu.ng/">
      <amount>' . $Amount . '</amount>
      <name>' . htmlspecialchars($name) . '</name>
      <phone>' . htmlspecialchars($Phone) . '</phone>
      <email>' . htmlspecialchars($Email) . '</email>
      <description>' . htmlspecialchars($description) . '</description>
      <matno>' . htmlspecialchars($mno) . '</matno>
      <paymentid>' . $pidd . '</paymentid>
      <session>' . htmlspecialchars($session) . '</session>
      <user>' . $user . '</user>
      <token>' . $token . '</token>
    </GenerateInvoice>
  </soap:Body>
</soap:Envelope>';

        // SOAP endpoint and action
        $url = "https://payware.yabatech.edu.ng/repay/ourservice.asmx";
        $soapAction = "http://portal.yabatech.edu.ng/GenerateInvoice";

        // Headers
        $headers = [
            "Content-Type: text/xml; charset=utf-8",
            "SOAPAction: \"$soapAction\"",
            "Content-Length: " . strlen($xml)
        ];

        // Send SOAP request
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            echo '<script>alert("SOAP request failed: ' . curl_error($curl) . '");</script>';
            curl_close($curl);
            return;
        }
        curl_close($curl);

        // Parse SOAP response
        $cleanData = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result);
        $xmlResponse = simplexml_load_string($cleanData);
        if ($xmlResponse === false) {
            echo '<script>alert("Failed to parse SOAP response.");</script>';
            return;
        }

        $decodeJson = json_decode(json_encode($xmlResponse));
        $data = $decodeJson->soapBody->GenerateInvoiceResponse->GenerateInvoiceResult ?? "";

        // Handle response
        if (empty($data)) {
            echo '<script>alert("Error processing request, please try again later.");</script>';
        } elseif (in_array($data, ['0', '1'])) {
            echo '<script>alert("Invalid Credentials! You are not eligible to proceed with this payment. Please contact CITM for further assistance.");</script>';
        } elseif (!preg_match('/^\d{12}$/', $data)) {
            echo '<script>alert("Invalid RRR format received. Please try again.");</script>';
        } else {
            // Success: Insert into database and log
            $insertQuery = "INSERT INTO [EBPORTAL].[dbo].[PendingSchoolFees] (matricnum, amount, status, paymentid, datecreated, sessionid) VALUES (?, ?, 1, ?, GETDATE(), ?)";
            $insertParams = [$mno, $formattedAmount, $pidd, $sidd];
            $insertResult = sqlsrv_query($conn, $insertQuery, $insertParams);
            if ($insertResult === false) {
                echo '<script>alert("Failed to save pending payment.");</script>';
                return;
            }

            $logQuery = "INSERT INTO [erp].[dbo].[erpLog] (user_id, log_date, action, user_ip, studnum) VALUES (?, GETDATE(), 'registered pending payment', 'service user', ?)";
            $logParams = [$Uname, $mno];
            sqlsrv_query($conn_v, $logQuery, $logParams); // Log even if fails

            $redirectUrl = "numdisplay.php?data=" . urlencode($data) . "&name=" . urlencode($name) . "&description=" . urlencode($description);
            echo '<script>alert("Details submitted. Please click OK to view the remitta number."); window.location.href="' . $redirectUrl . '";</script>';
        }


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
      <style>
          /* YabaTech Color Scheme */
          :root {
              --yabatech-green: #006400;
              --yabatech-yellow: #FFD700;
              --yabatech-light-green: #90EE90;
              --yabatech-dark-green: #004400;
          }

          body {
              background-color: #f8f9fa;
              font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          }

          .yabatech-header {
              background: linear-gradient(135deg, var(--yabatech-green), var(--yabatech-dark-green));
              color: white;
              padding: 20px;
              text-align: center;
              margin-bottom: 30px;
              border-radius: 8px;
              box-shadow: 0 4px 6px rgba(0,0,0,0.1);
          }

          .yabatech-header h1 {
              margin: 0;
              font-size: 2.5em;
              font-weight: bold;
          }

          .yabatech-header p {
              margin: 5px 0 0 0;
              font-size: 1.2em;
              opacity: 0.9;
          }

          .panel-bd {
              border: 2px solid var(--yabatech-green);
              border-radius: 10px;
              box-shadow: 0 6px 12px rgba(0,0,0,0.15);
              background: white;
          }

          .panel-heading {
              background: linear-gradient(135deg, var(--yabatech-yellow), #FFA500);
              color: var(--yabatech-dark-green);
              border-bottom: 2px solid var(--yabatech-green);
              border-radius: 8px 8px 0 0;
              padding: 15px;
          }

          .panel-heading h4 {
              margin: 0;
              font-weight: bold;
              font-size: 1.5em;
          }

          .panel-body {
              padding: 30px;
          }

          .form-group label {
              color: var(--yabatech-dark-green);
              font-weight: 600;
              margin-bottom: 8px;
          }

          .form-control {
              border: 2px solid #ddd;
              border-radius: 6px;
              padding: 12px;
              font-size: 1em;
              transition: border-color 0.3s ease;
          }

          .form-control:focus {
              border-color: var(--yabatech-green);
              box-shadow: 0 0 0 3px rgba(0, 100, 0, 0.1);
              outline: none;
          }

          .btn-warning {
              background: linear-gradient(135deg, var(--yabatech-yellow), #FFA500);
              border: 2px solid var(--yabatech-dark-green);
              color: var(--yabatech-dark-green);
              font-weight: bold;
              padding: 12px 30px;
              border-radius: 6px;
              font-size: 1.1em;
              transition: all 0.3s ease;
          }

          .btn-warning:hover {
              background: linear-gradient(135deg, #FFA500, var(--yabatech-yellow));
              transform: translateY(-2px);
              box-shadow: 0 4px 8px rgba(0,0,0,0.2);
          }

          .main-footer {
              background: var(--yabatech-green);
              color: white;
              text-align: center;
              padding: 20px;
              margin-top: 50px;
          }

          .main-footer a {
              color: var(--yabatech-yellow);
              text-decoration: none;
          }

          .main-footer a:hover {
              text-decoration: underline;
          }

          /* Responsive design */
          @media (max-width: 768px) {
              .yabatech-header h1 {
                  font-size: 2em;
              }
              .panel-body {
                  padding: 20px;
              }
          }
      </style>
   </head>

   <body>
       <!-- YabaTech Header -->
       <div class="container">
           <div class="yabatech-header">
               <h1>Yaba College of Technology</h1>
               <p>Pending Payment Portal</p>
           </div>
       </div>

       <!-- Main content -->
       <section class="content">
           <div class="container">
               <div class="row justify-content-center">
                   <!-- Form controls -->
                   <div class="col-md-8 col-lg-6">
                       <div class="panel panel-bd">
                           <div class="panel-heading">
                               <h4>Pending Payment Form</h4>
                           </div>
                           <div class="panel-body">
                               <form method="POST" action="">
                                   <div class="form-group">
                                       <label for="mno">Application / Matric Number</label>
                                       <input type="text" id="mno" name="mno" class="form-control" placeholder="Enter matric number here" required>
                                   </div>

                                   <div class="form-group">
                                       <label for="amt">Amount (₦)</label>
                                       <input type="text" id="amt" name="amt" class="form-control" placeholder="Enter pending amount to be paid" required>
                                   </div>

                                   <input type="hidden" name="stat" value="1">

                                   <div class="form-group">
                                       <label for="pidd">Payment Type</label>
                                       <select class="form-control" id="pidd" name="pidd" required>
                                           <option value="">-- Select Payment Type --</option>
                                           <?php
                                           $query3 = "SELECT PaymentID, PaymentName FROM YCTPAY_Payments";
                                           $user_query3 = sqlsrv_query($conn, $query3);
                                           while ($row2 = sqlsrv_fetch_array($user_query3)) {
                                               $pid = $row2['PaymentID'];
                                               $pname = $row2['PaymentName'];
                                               echo "<option value='" . $pid . "'>" . $pname . "</option>";
                                           }
                                           ?>
                                       </select>
                                   </div>

                                   <div class="form-group">
                                       <label for="sidd">Academic Session</label>
                                       <select class="form-control" id="sidd" name="sidd" required>
                                           <option value="">-- Select Session --</option>
                                           <?php
                                           $queryy = "SELECT SessionID, Session FROM Sessions";
                                           $user_query32 = sqlsrv_query($conn, $queryy);
                                           while ($row = sqlsrv_fetch_array($user_query32)) {
                                               $sid = $row['SessionID'];
                                               $sname = $row['Session'];
                                               echo "<option value='" . $sid . "'>" . $sname . "</option>";
                                           }
                                           ?>
                                       </select>
                                   </div>

                                   <div class="form-group text-center">
                                       <button type="submit" name="submit" class="btn btn-warning btn-lg">
                                           <i class="fa fa-paper-plane"></i> Submit Payment
                                       </button>
                                   </div>
                               </form>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       </section>
       <!-- /.content -->

       <!-- /.content-wrapper -->
       <footer class="main-footer">
           <div class="container">
               <strong>Copyright &copy; <?php echo date("Y"); ?> <a href="#">Yaba College of Technology</a>.</strong> All rights reserved.
           </div>
       </footer>
   </body>
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




