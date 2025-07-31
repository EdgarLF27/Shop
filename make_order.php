<?php
header('Content-Type: application/json');

$clientID = getenv('PAYPAL_CLIENT_ID');
$secret = getenv('PAYPAL_SECRET');
$api_url = "https://api-m.sandbox.paypal.com";

$auth = base64_encode("$clientID:$secret");

$data = json_decode(file_get_contents("php://input"), true);
$precio = $data['precio'] ?? 0;

if ($precio <= 0) {
    echo json_encode(["error" => "El precio no es válido"]);
    exit();
}

// Obtener token de acceso
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$api_url/v1/oauth2/token");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic $auth",
    "Content-Type: application/x-www-form-urlencoded"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

$accessToken = $response['access_token'] ?? '';

if (!$accessToken) {
    echo json_encode(["error" => "No se pudo obtener el token de acceso"]);
    exit();
}

// Crear la orden
$orderData = [
    "intent" => "CAPTURE",
    "purchase_units" => [[
        "amount" => [
            "currency_code" => "MXN",
            "value" => $precio
        ]
    ]],
    "application_context" => [
        "return_url" => "http://localhost/Shop/paypal_success.php",
        "cancel_url" => "http://localhost/Shop/paypal_cancel.php"
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$api_url/v2/checkout/orders");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['id'])) {
    echo json_encode(["error" => "No se pudo crear la orden", "details" => $response]);
    exit();
}

echo json_encode($response);
