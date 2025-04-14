<?php
session_start();
require "db/connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener los pedidos del usuario
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pedidos</title>
</head>
<body>
    <h1>Historial de Pedidos</h1>
    <a href="index.php">Volver a la Tienda</a>
    <table border="1">
        <tr>
            <th>ID del Pedido</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><?php echo $order['id']; ?></td>
            <td>$<?php echo number_format($order['total'], 2); ?></td>
            <td><?php echo $order['status']; ?></td>
            <td><?php echo $order['created_at']; ?></td>
            <td>
                <a href="order_confirmation.php?order_id=<?php echo $order['id']; ?>">Ver Detalles</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>