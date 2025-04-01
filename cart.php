<?php
require "db/connection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener los productos del carrito
$sql = "SELECT cart.id, products.name, products.description, products.price, cart.quantity, products.image 
        FROM cart 
        JOIN products ON cart.product_id = products.id 
        WHERE cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

// Calcular el total del carrito
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Función para actualizar la cantidad de un producto en el carrito
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_quantity') {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];

    $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity, $cart_id);
    $stmt->execute();

    header("Location: cart.php");
    exit();
}

// Función para eliminar un producto del carrito
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'remove_from_cart') {
    $cart_id = $_POST['cart_id'];

    $sql = "DELETE FROM cart WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();

    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <script src="https://www.paypal.com/sdk/js?client-id=AS6rJyOko1SalrUe7SJ5UesRC6_9Q0BQhNnxufdwLXLedOdnMUqTSQ0DfqB9l6ZCuK5DMhuBskW28Zet&currency=USD"></script>
</head>

<body>
    <h1>Carrito de Compras</h1>
    <a href="index.php">Seguir Comprando</a>
    <table border="1">
        <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Imagen</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($cart_items as $item): ?>
        <tr>
            <td><?php echo $item['name']; ?></td>
            <td><?php echo $item['description']; ?></td>
            <td><?php echo $item['price']; ?></td>
            <td>
                <form action="cart.php" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="update_quantity">
                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" required>
                    <input type="submit" value="Actualizar">
                </form>
            </td>
            <td><img src="images/<?php echo $item['image']; ?>" width="100"></td>
            <td>
                <form action="cart.php" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="remove_from_cart">
                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                    <input type="submit" value="Eliminar">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <h2>Total: $<?php echo number_format($total, 2); ?></h2>
    <div id="paypal-button-container"></div>

    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                return fetch('make_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        precio: <?php echo $total; ?> // Total del carrito
                    })
                }).then(response => response.json())
                  .then(order => order.id);
            },
            onApprove: function(data, actions) {
                return fetch('pay_capture.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        orderID: data.orderID
                    })
                }).then(response => response.json())
                  .then(details => {
                      alert('Pago completado por ' + details.payer.name.given_name);
                      window.location.href = 'paypal_success.php?token=' + data.orderID;
                  });
            },
            onCancel: function(data) {
                window.location.href = 'paypal_cancel.php';
            }
        }).render('#paypal-button-container');
    </script>
</body>

</html>