<?php
require "db/connection.php";
session_start();

// Verificar si el usuario es dueño
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Dueño') {
    header("Location: index.php");
    exit();
}

// Función para subir un producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $image = $_FILES['image']['name'];
    $target = "images/" . basename($image);

    $sql = "INSERT INTO products (name, description, price, quantity, image) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdis", $name, $description, $price, $quantity, $image);
    $stmt->execute();

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        echo "Producto subido exitosamente";
    } else {
        echo "Error al subir la imagen";
    }
}

// Función para eliminar un producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['id'];

    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "Producto eliminado exitosamente";
}

// Función para editar un producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $image = $_FILES['image']['name'];
    $target = "images/" . basename($image);

    if ($image) {
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, quantity = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisi", $name, $description, $price, $quantity, $image, $id);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    } else {
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdii", $name, $description, $price, $quantity, $id);
    }
    $stmt->execute();

    echo "Producto editado exitosamente";
}

// Obtener todos los productos
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Productos</title>
</head>

<body>
    <h1>Gestionar Productos</h1>

    <h2>Subir Producto</h2>
    <form action="manage_products.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <input type="text" name="name" placeholder="Nombre del producto" required>
        <br><br>
        <textarea name="description" placeholder="Descripción del producto" required></textarea>
        <br><br>
        <input type="number" step="0.01" name="price" placeholder="Precio del producto" required>
        <br><br>
        <input type="number" name="quantity" placeholder="Cantidad en existencia" required>
        <br><br>
        <input type="file" name="image" required>
        <br><br>
        <input type="submit" value="Subir Producto">
    </form>

    <h2>Productos Existentes</h2>
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
                <form action="manage_products.php" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <input type="submit" value="Eliminar">
                </form>
                <form action="manage_products.php" method="POST" enctype="multipart/form-data" style="display:inline;">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <input type="text" name="name" value="<?php echo $product['name']; ?>" required>
                    <textarea name="description" required><?php echo $product['description']; ?></textarea>
                    <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
                    <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>" required>
                    <input type="file" name="image">
                    <input type="submit" value="Editar">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>