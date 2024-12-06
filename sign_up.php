<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>
    <h2>Bienvenido de nuevo:)</h2>
</head>
<body>
    <form action= "sign_up.php" method= "POST" >
<input type="text" name="user_name" id="user_name" placeholder="Ingresa tu nombre de usuario" required>
<br><br>
<input type="text" name="password" id="password" placeholder="Ingresa tu contraseña" required>
<br><br>
    </form>
</body>
</html>

<?php
require "db/connection.php";
//check if the form has been submitted
if($SERVER["REQUEST_METHOD"] == "POST") {
$user_name = $_POST["user_name"];
$password = $_POST["password"];

}

?>