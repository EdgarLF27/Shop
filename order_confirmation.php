<?php
session_start();
require "db/connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

$order_id = $_GET['order_id'] ?? 0;

// Verificar que el pedido pertenece al usuario
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Pedido no encontrado o no tienes permiso para verlo.";
    exit();
}

// Obtener los productos del pedido
$sql = "SELECT products.name, order_items.quantity, order_items.price 
        FROM order_items 
        JOIN products ON order_items.product_id = products.id 
        WHERE order_items.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido</title>
</head>
<body>
    <h1>Confirmación de Pedido</h1>
    <p>Gracias por tu compra. Aquí están los detalles de tu pedido:</p>

    <h2>Pedido #<?php echo $order['id']; ?></h2>
    <p><strong>Total:</strong> $<?php echo number_format($order['total'], 2); ?></p>
    <p><strong>Estado:</strong> <?php echo $order['status']; ?></p>
    <p><strong>Fecha:</strong> <?php echo $order['created_at']; ?></p>

    <h3>Productos:</h3>
    <table border="1">
        <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Total</th>
        </tr>
        <?php foreach ($order_items as $item): ?>
        <tr>
            <td><?php echo $item['name']; ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>$<?php echo number_format($item['price'], 2); ?></td>
            <td>$<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <a href="order_history.php">Ver Historial de Pedidos</a>
    <a href="index.php">Volver a la Tienda</a>
</body>
</html>