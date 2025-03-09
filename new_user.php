<?php
require "db/connection.php";
//check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $last_names = $_POST["last_names"];
    $user_name = $_POST["user_name"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    $number_phone = $_POST["number_phone"];
    $role = $_POST["role"];

    $sql = "INSERT INTO users (name, last_names, user_name, password, email, number_phone, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $name, $last_names, $user_name, $password, $email, $number_phone, $role);
    $stmt->execute();
    echo "Registro exitoso ";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
    <h1> Bienvenido!, Crea tu usuario</h1>
</head>

<body>
    <form action="new_user.php" method="POST">
        <input type="text" name="name" id="name" placeholder="Escribe tu nombre" required>
        <br><br>
        <input type="text" name="last_names" id="last_names" placeholder="Escribe tus apellidos" required>
        <br><br>
        <input type="text" name="user_name" id="user_name" placeholder="¿Cuál será tu nombre de usuario?" required>
        <br><br>
        <input type="password" name="password" id="password" placeholder="¿Cuál será tu contraseña?" required>
        <br><br>
        <input type="email" name="email" id="email" placeholder="Escribe tu correo" required>
        <br><br>
        <input type="number" name="number_phone" id="number_phone" placeholder="Escribe tu número de teléfono" required>
        <br><br>
        <label for="role">Selecciona tu rol:</label>
        <select name="role" id="role" required>
            <option value="Dueño">Dueño</option>
            <option value="Usuario">Usuario</option>
        </select>
        <br><br>
        <input type="submit" value="Registrarse">
    </form>
</body>

</html>