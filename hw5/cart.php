<?php
require_once("template.php");
template_header("Welcome!");
session_start();
require "db.php";
require_once "cart_functions.php";

define("TAX_RATE", 0.07);

if (!isset($_SESSION["user"]["UID"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user"]["UID"];

// Sync cookie to DB
syncCartToDatabase($pdo, $uid);

// Removal process.
if (isset($_POST['remove']) && isset($_POST['pid'])) {
    $pid = intval($_POST['pid']);
    $cart = getCartCookie();
    unset($cart[$pid]);
    saveCartCookie($cart);

    $stmt = $pdo->prepare("DELETE FROM cart WHERE UID=? AND PID=?");
    $stmt->execute([$uid, $pid]);

    header("Location: cart.php");
    exit;
}

// Handle quantity update
if (isset($_POST['update']) && isset($_POST['pid']) && isset($_POST['quantity'])) {
    $pid = intval($_POST['pid']);
    //I hate PHP syntax. Takes the quantity, makes it 99999 if it's higher than that. If it's lower than 1, it becomes 1 instead.
    $qty = max(1, min(99999, intval($_POST['quantity'])));
    $cart = getCartCookie();
    if (isset($cart[$pid])) {
        $cart[$pid]['quantity'] = $qty;
        saveCartCookie($cart);

        $stmt = $pdo->prepare("UPDATE cart SET quantity=? WHERE UID=? AND PID=?");
        $stmt->execute([$qty, $uid, $pid]);
    }
    header("Location: cart.php");
    exit;
}

// Fetch cart items
$stmt = $pdo->prepare("
    SELECT c.PID, c.quantity, c.price, p.productName, p.imageName
    FROM cart c
    JOIN products p ON c.PID = p.PID
    WHERE c.UID = ?
");
$stmt->execute([$uid]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h2>Your Cart</h2>

<?php if (empty($cartItems)): ?>
<p>Your cart is empty.</p>
<?php else: ?>
<table id="cartTable" border="1" cellpadding="10" cellspacing="0">
<tr>
    <th>Product</th>
    <th>Image</th>
    <th>Unit Price</th>
    <th>Quantity</th>
    <th>Subtotal</th>
    <th>Update</th>
    <th>Remove</th>
</tr>

<?php foreach ($cartItems as $item):
    $itemTotal = $item['price'] * $item['quantity'];
?>
<tr data-pid="<?php echo $item['PID']; ?>">
    <td><?php echo htmlspecialchars($item['productName']); ?></td>
    <td><img src="assets/<?php echo $item['imageName']; ?>Min.jpg" style="max-width:50px;"></td>
    <td class="unitPrice"><?php echo number_format($item['price'],2); ?></td>
    <td>
        <input type="number" class="quantityInput" value="<?php echo $item['quantity']; ?>" min="1" max="99999" style="width:50px;">
    </td>
    <td class="itemSubtotal"><?php echo number_format($itemTotal,5); ?></td>
    <td>
        <form method="POST" style="display:inline-block;">
            <input type="hidden" name="pid" value="<?php echo $item['PID']; ?>">
            <input type="hidden" class="hiddenQty" name="quantity" value="<?php echo $item['quantity']; ?>">
            <button type="submit" name="update">Update</button>
        </form>
    </td>
    <td>
        <form method="POST" style="display:inline-block;">
            <input type="hidden" name="pid" value="<?php echo $item['PID']; ?>">
            <button type="submit" name="remove">Remove</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

<p><strong>Subtotal:</strong> $<span id="subtotal">0.00</span></p>
<p><strong>Tax (<?php echo TAX_RATE*100; ?>%):</strong> $<span id="tax">0.00</span></p>
<p><strong>Total:</strong> $<span id="total">0.00</span></p>

<a href="checkout.php"><button>Proceed to Checkout</button></a>

<script>
// Update hidden quantity before form submission
document.querySelectorAll('.quantityInput').forEach(input => {
    input.addEventListener('input', () => {
        let val = parseInt(input.value);
        if (isNaN(val) || val < 1) val = 1;
        if (val > 99999) val = 99999;
        input.value = val;

        //Update the corresponding hidden input in the form
        const hidden = input.closest('tr').querySelector('.hiddenQty');
        if (hidden) hidden.value = val;

        updateTotals();
    });
});

// Dynamic total updating.
function updateTotals() {
    let subtotal = 0;

    document.querySelectorAll('#cartTable tr[data-pid]').forEach(row => {
        const priceText = row.querySelector('.unitPrice').textContent.replace(/[^0-9.]/g, '');
        const price = parseFloat(priceText) || 0;
        const quantityText = row.querySelector('.quantityInput').value.replace(/\D/g, '');
        const quantity = Math.max(1, parseInt(quantityText) || 1);
        const rowTotal = price * quantity;
        row.querySelector('.itemSubtotal').textContent = rowTotal.toFixed(2);
        subtotal += rowTotal;
    });

    const taxRate = <?php echo TAX_RATE; ?>;
    const tax = subtotal * taxRate;
    const total = subtotal + tax;

    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('tax').textContent = tax.toFixed(2);
    document.getElementById('total').textContent = total.toFixed(2);
}

updateTotals();
</script>
<?php endif; ?>

<?php template_footer(); ?>