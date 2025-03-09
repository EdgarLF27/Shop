<?php
require "db/connection.php";
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: sign_in.php");
    exit();
}

// Obtener todos los productos
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);

// Función para añadir un producto al carrito
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: sign_in.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    $stmt->execute();

    echo "Producto añadido al carrito exitosamente";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda</title>
</head>

<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
    <a href="logout.php">Cerrar sesión</a>
    <a href="cart.php" style="float: right;">Ver Carrito</a>

    <h1>Tienda</h1>

    <h2>Productos Disponibles</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Imagen</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($products as $product): ?>
        <tr>
            <td><?php echo $product['id']; ?></td>
            <td><?php echo $product['name']; ?></td>
            <td><?php echo $product['description']; ?></td>
            <td><?php echo $product['price']; ?></td>
            <td><?php echo $product['quantity']; ?></td>
            <td><img src="images/<?php echo $product['image']; ?>" width="100"></td>
            <td>
                <form action="index.php" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" required>
                    <input type="submit" value="Añadir al carrito">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>