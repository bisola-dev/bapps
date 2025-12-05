<?php
// Authentication constants
define("APP_ID", "7112");
define("VALIDATION_URL", "https://appzone.yabatech.edu.ng/generalservice/api/erpaccess/validate");
define("BEARER_TOKEN", "KCsgXowbRSQLNBTP");

/**
 * Validates the user token via API
 * @return array User data if valid, redirects on failure
 */
function validateUserToken() {
    // Get token from URL
    if (!isset($_GET['token']) || empty($_GET['token'])) {
        header("Location: access_denied.php");
        exit;
    }

    $urlToken = trim($_GET['token']);

    // Prepare the POST data
    $payload = [
        "Appid" => APP_ID,
        "Token" => $urlToken
    ];

    // Initialize cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => VALIDATION_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . BEARER_TOKEN
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    // Execute request
    $response = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        curl_close($ch);
        header("Location: access_denied.php");
        exit;
    }

    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Handle validation result
    if ($httpStatus == 200 && isset($data['message']) && $data['message'] === "Access granted.") {
        return [
            'userId' => $data['data']['Userid'],
            'userName' => $data['data']['Uname']
        ];
    } else {
        header("Location: access_denied.php");
        exit;
    }
}
?>
