<?php
session_start();
require "db.php";
require_once "cart_functions.php";
require "validate.php"; 

define("TAX_RATE", 0.07);
define("MEMBER_DISCOUNT", 0.10); // 10% off for members

// redirect if not logged in
if (!isset($_SESSION["user"]["UID"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user"]["UID"];

// Sync cookie to DB
syncCartToDatabase($pdo, $uid);

// Fetch cart items with product info
$stmt = $pdo->prepare("
    SELECT c.PID, c.quantity, c.price, p.productName
    FROM cart c
    JOIN products p ON c.PID = p.PID
    WHERE c.UID = ?
");
$stmt->execute([$uid]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Apply membership pricing
foreach ($cartItems as &$item) {
    $item['memberPrice'] = $item['price'] * (1 - MEMBER_DISCOUNT);
}
unset($item); 

// Initialize error messages
$addressErr = $cityErr = $stateErr = $zipErr = $cardNumErr = $cardSecErr = $message = "";

// Initialize form fields
$address = $city = $state = $zip = $cardNum = $cardSec = "";

// Validation
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Shipping & payment validations
    $address = testInput($_POST["shipAddress"] ?? "");
    if (strlen($address) < 3) $addressErr = "Address too short";

    $city = testInput($_POST["shipCity"] ?? "");
    if (!preg_match("/^[A-Za-z .'-]{2,}$/", $city)) $cityErr = "Invalid city name";

    $state = testInput($_POST["shipState"] ?? "");
    if (!preg_match("/^[A-Za-z]{2,20}$/", $state)) $stateErr = "Invalid state";

    $zip = testInput($_POST["shipZip"] ?? "");
    if (!preg_match("/^[0-9]{5}$/", $zip)) $zipErr = "ZIP must be 5 digits";

    $cardNum = testInput($_POST["cardNum"] ?? "");
    if (!preg_match("/^[0-9]{16}$/", $cardNum)) $cardNumErr = "Card number must be 16 digits";

    $cardSec = testInput($_POST["cardSec"] ?? "");
    if (!preg_match("/^[0-9]{3}$/", $cardSec)) $cardSecErr = "CVV must be 3 digits";

    $hasErrors = $addressErr || $cityErr || $stateErr || $zipErr || $cardNumErr || $cardSecErr;

    if (!$hasErrors && !empty($cartItems)) {

        $orderDate = date('Y-m-d H:i:s');

        // Insert order — full card number, no CVV
        $stmt = $pdo->prepare("
            INSERT INTO orders (UID, shipAddress, shipCity, shipZip, shipState, orderDate, cardNum)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$uid, $address, $city, $zip, $state, $orderDate, $cardNum]);

        $orderID = $pdo->lastInsertId();

        // Insert order items with member pricing
        $stmtItem = $pdo->prepare("
            INSERT INTO orderitems (orderID, productID, quantity, priceEach)
            VALUES (?, ?, ?, ?)
        ");
        foreach ($cartItems as $item) {
            $stmtItem->execute([
                $orderID,
                $item['PID'],
                $item['quantity'],
                $item['memberPrice'] // use member price
            ]);
        }

        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE UID = ?");
        $stmt->execute([$uid]);
        clearCartCookie();

        $message = "Order placed successfully!";
        $cartItems = [];
    }
}

require_once("template.php");
template_header("Welcome!");?>

<h2>Checkout</h2>
<p><?php echo htmlspecialchars($message); ?></p>

<?php if (empty($cartItems)): ?>
<p>Your cart is empty.</p>
<?php else: ?>

<table border="1" cellpadding="10" cellspacing="0">
<tr>
    <th>Product</th>
    <th>Quantity</th>
    <th>Unit Price</th>
    <th>Member Price</th>
    <th>Subtotal</th>
</tr>

<?php
$subtotal = 0;
foreach ($cartItems as $item):
    $itemTotal = $item['memberPrice'] * $item['quantity'];
    $subtotal += $itemTotal;
?>
<tr>
    <td><?php echo htmlspecialchars($item['productName']); ?></td>
    <td><?php echo $item['quantity']; ?></td>
    <td>$<?php echo number_format($item['price'],2); ?></td>
    <td>$<?php echo number_format($item['memberPrice'],2); ?></td>
    <td>$<?php echo number_format($itemTotal,2); ?></td>
</tr>
<?php endforeach; ?>
</table>

<?php
$tax = $subtotal * TAX_RATE;
$total = $subtotal + $tax;
?>
<p><strong>Subtotal:</strong> $<?php echo number_format($subtotal,2); ?></p>
<p><strong>Tax (<?php echo TAX_RATE*100; ?>%):</strong> $<?php echo number_format($tax,2); ?></p>
<p><strong>Total:</strong> $<?php echo number_format($total,2); ?></p>

<h3>Shipping & Payment Info</h3>

<form method="POST" onsubmit="return validateRequiredFields(event);">

    <label>Address:
        <input type="text" name="shipAddress" value="<?php echo htmlspecialchars($address); ?>" required>
    </label>
    <span class="error" id="shipAddress_Err"><?php echo $addressErr; ?></span><br><br>

    <label>City:
        <input type="text" name="shipCity" value="<?php echo htmlspecialchars($city); ?>" required>
    </label>
    <span class="error" id="shipCity_Err"><?php echo $cityErr; ?></span><br><br>

    <label>State:
        <input type="text" name="shipState" value="<?php echo htmlspecialchars($state); ?>" required>
    </label>
    <span class="error" id="shipState_Err"><?php echo $stateErr; ?></span><br><br>

    <label>ZIP:
        <input type="text" name="shipZip" value="<?php echo htmlspecialchars($zip); ?>" required>
    </label>
    <span class="error" id="shipZip_Err"><?php echo $zipErr; ?></span><br><br>

    <label>Card Number:
        <input type="text" name="cardNum" maxlength="16" value="<?php echo htmlspecialchars($cardNum); ?>" required>
    </label>
    <span class="error" id="cardNum_Err"><?php echo $cardNumErr; ?></span><br><br>

    <label>CVV:
        <input type="text" name="cardSec" maxlength="3" value="" required>
    </label>
    <span class="error" id="cardSec_Err"><?php echo $cardSecErr; ?></span><br><br>

    <button type="submit">Place Order</button>
</form>

<?php endif; ?>

<?php template_footer(); ?>