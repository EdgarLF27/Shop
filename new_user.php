<DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registrarse</title>
        <h1> Bienvenido!, Crea tu usuario</h1>
    </head>

    <body>
    </body>
    <form action="new_user.php" method="POST">
        <label for="name">¿Cuál es tu nombre?</label>
        <input type="text" name="name" id="name" required>
        <br><br>
        <label for="last_names">¿Cuáles son tus apellidos?</label>
        <input type="text" name="last_names" id="last_names" required>
        <br><br>
        <label for="username">¿Cuál es tu nombre de usuario?</label>
        <input type="text" name="user_name" id="user_name" required>
        <br><br>
        <label for="password">¿Cuál es tu contraseña?</label>
        <input type="password" name="password" id="password" required>
        <br><br>
        <label for="email">¿Cuál es tu correo?</label>
        <input type="email" name="email" id="email" required>
        <br><br>
        <input type="submit" value="Registrarse">
    </form>


    </html>
    <?php
    require "db/connection.php";
    //check if the form has been submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST["name"];
        $last_names = $_POST["last_names"];
        $user_name = $_POST["user_name"];
        $password = $_POST["password"];
        $email = $_POST["email"];
        
        $sql = "INSERT INTO users (name, last_names, user_name, password, email) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $last_names, $username, $password, $email);
        $stmt -> execute();
        echo "Registro exitoso ";
    }
    ?>