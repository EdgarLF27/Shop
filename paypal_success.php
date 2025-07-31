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
    echo "Error: Falta el ID de la orden.";
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
    echo "Error: No se pudo obtener el token de acceso.";
    exit();
}

// Consultar el estado de la orden en PayPal
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$api_url/v2/checkout/orders/$orderID");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

$orderStatus = $response['status'] ?? '';

if (!$orderStatus) {
    echo "Error: No se pudo obtener el estado de la orden.";
    exit();
}

// Procesar según el estado de la orden
if ($orderStatus === "COMPLETED") {
    // La orden ya fue capturada, guardar en la base de datos si no se ha hecho
    $total = $response['purchase_units'][0]['amount']['value'] ?? 0;

    // Verificar si ya existe el pedido en la base de datos
    $sql = "SELECT id FROM orders WHERE user_id = ? AND total = ? AND status = 'Completado'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $existingOrder = $stmt->get_result()->fetch_assoc();

    if ($existingOrder) {
        // Redirigir al pedido existente
        header("Location: order_confirmation.php?order_id=" . $existingOrder['id']);
        exit();
    }

    // Si no existe, guardar el pedido
    $sql = "INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'Completado')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insertar los productos en la tabla order_items
    foreach ($_SESSION['cart'] as $item) {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }
    // Limpiar el carrito después de guardar el pedido
    unset($_SESSION['cart']);

    // Redirigir a la confirmación del pedido
    header("Location: order_confirmation.php?order_id=$order_id");
    exit();
} elseif ($orderStatus === "APPROVED") {
    // Capturar el pago
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$api_url/v2/checkout/orders/$orderID/capture");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $captureResponse = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($captureResponse['status']) && $captureResponse['status'] === "COMPLETED") {
        // Guardar el pedido en la base de datos
        $total = $captureResponse['purchase_units'][0]['amount']['value'] ?? 0;

        $sql = "INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'Completado')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $user_id, $total);
        $stmt->execute();
        $order_id = $stmt->insert_id; // Obtener el ID del pedido recién creado

        // Insertar los productos en la tabla order_items
        foreach ($_SESSION['cart'] as $item) {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
        }

        // Limpiar el carrito después de guardar el pedido
        unset($_SESSION['cart']);

        // Redirigir a la confirmación del pedido
        header("Location: order_confirmation.php?order_id=$order_id");
        exit();
    } else {
        echo "Error al capturar el pago: " . json_encode($captureResponse);
        exit();
    }
} else {
    echo "Error: La orden no está en un estado válido para ser procesada. Estado: $orderStatus";
    exit();
}
