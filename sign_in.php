<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>
    <h2>Bienvenido de nuevo:)</h2>
</head>

<body>
    <form action="sign_in.php" method="POST">
        <input type="text" name="user_name" id="user_name" placeholder="Ingresa tu nombre de usuario" required>
        <br><br>
        <input type="password" name="password" id="password" placeholder="Ingresa tu contraseña" required>
        <br><br>
        <label for="role">Selecciona tu rol:</label>
        <select name="role" id="role" required>
            <option value="Dueño">Dueño</option>
            <option value="Usuario">Usuario</option>
        </select>
        <br><br>
        <input type="submit" value="Iniciar sesión">
    </form>
</body>

</html>

<?php
require "db/connection.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Añadir mensajes de depuración
    echo "Usuario: $user_name<br>";
    echo "Rol: $role<br>";

    $sql = "SELECT * FROM users WHERE user_name = ? AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user_name, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Añadir mensajes de depuración
    if ($user) {
        echo "Usuario encontrado: " . htmlspecialchars($user['user_name']) . "<br>";
    } else {
        echo "Usuario no encontrado<br>";
    }

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_name'] = $user_name;
        $_SESSION['role'] = $user['role'];
        if ($user['role'] == 'Dueño') {
            header("Location: manage_products.php");
        } else if ($user['role'] == 'Usuario') {
            header("Location: index.php");
        }
        exit();
    } else {
        echo "Nombre de usuario, contraseña o rol incorrectos";
    }

    $stmt->close();
    $conn->close();
}
?>