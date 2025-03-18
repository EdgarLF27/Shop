<?php
session_start();
require "db/connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener el ID de la orden desde la URL
$orderID = $_GET['token'] ?? '';

if (!$orderID) {
    echo "Falta el ID de la orden";
    exit();
}

// Obtener token de acceso
$clientID = getenv('PAYPAL_CLIENT_ID');
$secret = getenv('PAYPAL_SECRET');
$api_url = "https://api-m.sandbox.paypal.com";
$auth = base64_encode("$clientID:$secret");

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
    echo "No se pudo obtener el token de acceso";
    exit();
}

// Capturar el pago
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$api_url/v2/checkout/orders/$orderID/capture");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if ($response['status'] == "COMPLETED") {
    // Actualizar el estado del pedido en la base de datos
    $sql = "UPDATE orders SET status = 'paid' WHERE user_id = ? AND order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $orderID);
    $stmt->execute();

    // Mostrar un mensaje de confirmación al usuario
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pago Exitoso</title>
    </head>
    <body>
        <h1>Pago realizado con éxito</h1>
        <p>Gracias por tu compra. Tu pago ha sido procesado correctamente.</p>
        <a href="index.php">Volver a la tienda</a>
    </body>
    </html>
    <?php
} else {
    echo "Error al capturar el pago: " . json_encode($response);
}
?>