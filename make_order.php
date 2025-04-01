<?php
// Establece el tipo de contenido de la respuesta como JSON
header('Content-Type: application/json');

// Credenciales de la API de PayPal
$clientID = getenv('PAYPAL_CLIENT_ID');
$secret = getenv('PAYPAL_SECRET');
$api_url = "https://api-m.sandbox.paypal.com";

// Codifica las credenciales en base64 para la autenticación
$auth = base64_encode("$clientID:$secret");

// Decodifica los datos JSON recibidos en la solicitud
$data = json_decode(file_get_contents("php://input"), true);
$precio = $data['precio'] ?? 10.00; // Precio del producto, por defecto 10.00

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

// Extrae el token de acceso de la respuesta
$accessToken = $response['access_token'] ?? '';

// Verifica si se obtuvo el token de acceso
if (!$accessToken) {
    echo json_encode(["error" => "No se pudo obtener el token de acceso"]);
    exit;
}

// Datos de la orden de pago
$orderData = [
    "intent" => "CAPTURE",
    "purchase_units" => [[
        "amount" => [
            "currency_code" => "USD",
            "value" => $precio
        ]
    ]],
    "application_context" => [
        "return_url" => "http://localhost/Shop/paypal_success.php",
        "cancel_url" => "http://localhost/Shop/paypal_cancel.php"
    ]
];

// Crear la orden de pago
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

// Devuelve la respuesta de la creación de la orden
echo json_encode($response);
?>
