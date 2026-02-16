<?php
session_start();
require "db.php";

//Checks login. 
if (!isset($_SESSION["user"]["UID"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user"]["UID"];

//Grabs every order this user has ever made.
$stmt = $pdo->prepare("SELECT * FROM orders WHERE UID = ? ORDER BY orderDate DESC");
$stmt->execute([$uid]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once("template.php");
template_header("Welcome!");?>

<h2>Your Order History</h2>

<?php if (empty($orders)): ?>
    <p>You have not placed any orders yet.</p>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <h3>Order #<?= $order['OID'] ?> - <?= $order['orderDate'] ?></h3>
        <?php
        // Grabs the product names along with regular order info.
        $stmtItems = $pdo->prepare("
            SELECT oi.productID, oi.quantity, oi.priceEach, p.productName
            FROM orderitems oi
            INNER JOIN products p ON oi.productID = p.PID
            WHERE oi.orderID = ?
        ");

        $stmtItems->execute([$order['OID']]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);        
        ?>

        <?php if (!empty($items)): ?>
            <table border="1" cellpadding="10" cellspacing="0">
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
                <?php
                $orderTotal = 0;
                foreach ($items as $item):
                    $subtotal = $item['quantity'] * $item['priceEach'];
                    $orderTotal += $subtotal;
                ?>
                    <tr>
                        <td><?= $item['productName'] ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>$<?= number_format($item['priceEach'], 2) ?></td>
                        <td>$<?= number_format($subtotal, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <p><strong>Order Total:</strong> $<?= number_format($orderTotal, 2) ?></p>
        <?php else: ?>
        <!--Here solely to prevent tomfoolery.-->
            <p>No products found for this order.</p>
        <?php endif; ?>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>

<?php template_footer(); ?>