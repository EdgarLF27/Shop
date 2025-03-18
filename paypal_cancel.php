<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

// Mostrar un mensaje de cancelación al usuario
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Cancelado</title>
</head>
<body>
    <h1>Pago cancelado</h1>
    <p>El pago ha sido cancelado. Si tienes alguna pregunta, no dudes en contactarnos.</p>
    <a href="index.php">Volver a la tienda</a>
</body>
</html>