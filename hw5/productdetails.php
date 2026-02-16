<?php
require_once("template.php");
template_header("Welcome!");
session_start();
require "db.php";
require_once "cart_functions.php";

if (!isset($_SESSION["user"]["UID"])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION["user"]["UID"];

// Validate product ID (user input)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}
$pid = intval($_GET['id']);

// Fetch product info from DB
$stmt = $pdo->prepare("SELECT * FROM products WHERE PID = ?");
$stmt->execute([$pid]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) die("Product not found.");

// Handle add-to-cart
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
    $qty = max(1, min(99999, $qty)); // clamp to 1–99999

    addToCartDB($pdo, $uid, $pid, $qty, $product['price']);

    $cart = getCartCookie();
    $cart[$pid] = [
        'quantity' => $qty,
        'price' => $product['price']
    ];
    saveCartCookie($cart);

    header("Location: products.php?added=" . urlencode($pid));
    exit;
}

// Escape output for HTML
$productNameSafe = htmlspecialchars($product['productName'], ENT_QUOTES, 'UTF-8');
$descriptionSafe = htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8');
$imageNameSafe = htmlspecialchars($product['imageName'], ENT_QUOTES, 'UTF-8');
$priceSafe = number_format($product['price'], 2);
?>

<h2><?= $productNameSafe ?></h2>

<picture class='prodDetail'>
    <source srcset='assets/<?= $imageNameSafe ?>Max.jpg' media='(min-width: 1024px)'>
    <source srcset='assets/<?= $imageNameSafe ?>Med.jpg' media='(min-width: 768px)'>
    <img src='assets/<?= $imageNameSafe ?>Min.jpg' alt='<?= $productNameSafe ?>'>
</picture>

<p><?= $descriptionSafe ?></p>
<p><strong>Price:</strong> $<?= $priceSafe ?></p>

<form method="POST">
    <label>Quantity:
        <input type="number" name="qty" id="qtyInput" value="1" min="1" max="99999" required>
    </label>
    <button type="submit">Add to Cart</button>
</form>

<script>
// Prevent non-digit input
const qtyInput = document.getElementById('qtyInput');
qtyInput.addEventListener('input', () => {
    let val = qtyInput.value.replace(/\D/g, ''); // remove all non-digits
    if (val === '' || parseInt(val) < 1) val = '1';
    if (parseInt(val) > 99999) val = '99999';
    qtyInput.value = val;
});
</script>

<?php template_footer(); ?>
