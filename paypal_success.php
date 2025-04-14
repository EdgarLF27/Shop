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

$ch = curl_init(); //Inicia una sesion cURL
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

if (isset($response['status']) && $response['status'] == "COMPLETED") {
    // Calcular el total del carrito
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Insertar el pedido en la tabla `orders`
    $sql = "INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'Completado')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id; // Obtener el ID del pedido recién creado

    // Insertar los productos del pedido en la tabla `order_items`
    foreach ($_SESSION['cart'] as $item) {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }

    // Limpiar el carrito
    unset($_SESSION['cart']);

    // Redirigir al usuario a la página de confirmación
    header("Location: order_confirmation.php?order_id=$order_id");
    exit();
} else {
    echo "Error al capturar el pago: " . json_encode($response);
}
?>