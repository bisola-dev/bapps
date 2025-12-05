<?php
require_once('connection.php');
// Check if user is authenticated via session
if (!isset($_SESSION['verified_userid']) || empty($_SESSION['verified_userid'])) {
    header("Location: access_denied.php");
    exit;
}

$crumb = $_SESSION['verified_userid'];
$Uname = $_SESSION['verified_username'];


 if (isset($_GET['data'])) {
    $data = $_GET['data'];
    $name = $_GET['name'];
    $description = $_GET['description'];
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
              padding: 20px;
              text-align: center;
          }

          .panel-heading h4 {
              margin: 0;
              font-weight: bold;
              font-size: 1.5em;
          }

          .remitta-display {
              font-size: 1.8em;
              font-weight: bold;
              color: var(--yabatech-dark-green);
              background: var(--yabatech-light-green);
              padding: 20px;
              border-radius: 8px;
              margin: 20px 0;
              text-align: center;
              border: 2px solid var(--yabatech-green);
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
              .remitta-display {
                  font-size: 1.5em;
              }
          }
      </style>
</head>
   <body>
       <!-- YabaTech Header -->
       <div class="container">
           <div class="yabatech-header">
               <h1>Yaba College of Technology</h1>
               <p>Payment Confirmation</p>
           </div>
       </div>

       <!-- Main content -->
       <section class="content">
           <div class="container">
               <div class="row justify-content-center">
                   <div class="col-md-8 col-lg-6">
                       <div class="panel panel-bd">
                           <div class="panel-heading">
                               <h4>Remitta Number Generated</h4>
                           </div>
                           <div class="panel-body">
                               <?php if (!empty($data) && $data != '1' && $data != '0' && !empty($name)): ?>
                                   <div class="text-center">
                                       <p class="mb-4" style="font-size: 1.2em; color: var(--yabatech-dark-green);">
                                           Dear <strong><?php echo htmlspecialchars($name); ?></strong>,<br>
                                           Your remitta number for <strong><?php echo htmlspecialchars($description); ?></strong> is:
                                       </p>
                                       <div class="remitta-display">
                                           <?php echo htmlspecialchars($data); ?>
                                       </div>
                                       <p style="color: var(--yabatech-dark-green); font-weight: 500;">
                                           Please proceed to make payment using this remitta number.
                                       </p>
                                   </div>
                               <?php else: ?>
                                   <div class="alert alert-warning text-center">
                                       <h5>No valid remitta number found.</h5>
                                       <p>Please go back and try again.</p>
                                   </div>
                               <?php endif; ?>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       </section>
       <!-- /.content -->

       <!-- Footer -->
       <footer class="main-footer">
           <div class="container">
               <strong>Copyright &copy; <?php echo date("Y"); ?> <a href="#">Yaba College of Technology</a>.</strong> All rights reserved.
           </div>
       </footer>
       <!-- End Footer -->

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



