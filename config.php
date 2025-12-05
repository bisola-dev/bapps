<?php
// Configuration file for constants and settings

// API Constants
define("APP_ID", "7112");
define("VALIDATION_URL", "https://appzone.yabatech.edu.ng/generalservice/api/erpaccess/validate");
define("BEARER_TOKEN", "KCsgXowbRSQLNBTP");

// SOAP API Constants
define("SOAP_USER", "anty");
define("SOAP_TOKEN", "antymi");
define("SOAP_URL", "https://payware.yabatech.edu.ng/repay/ourservice.asmx");
define("SOAP_ACTION", "http://portal.yabatech.edu.ng/GenerateInvoice");

// Database table names
define("TABLE_SESSIONS", "Sessions");
define("TABLE_PAYMENTS", "YCTPAY_Payments");
define("TABLE_BIODATA", "vw_biodata");
define("TABLE_PENDING_FEES", "PendingSchoolFees");
define("TABLE_ERP_LOG", "erpLog");

// Error messages
define("ERROR_EMPTY_FORM", "Please do not submit empty form. All fields are compulsory.");
define("ERROR_PAYMENT_NOT_ALLOWED", "Sorry, Payment can't be made on the selected item.");
define("ERROR_MATRIC_NOT_FOUND", "Matricnumber or Appnumber not found, please input correctly.");
define("ERROR_PROCESSING_REQUEST", "Error processing request, please try again later.");
define("ERROR_INVALID_CREDENTIALS", "Invalid Credentials! You are not eligible to proceed with this payment, Please contact CITM for further assistance.");
define("ERROR_INVALID_RRR", "Invalid RRR format received. Please try again.");
define("SUCCESS_SUBMIT", "Details submitted. Please click OK to view the remitta number.");
?>