<?php
// Get cart from cookie (associative array PID => ['quantity'=>x, 'price'=>y])
function getCartCookie() {
    if (isset($_COOKIE['cart'])) {
        $cart = json_decode($_COOKIE['cart'], true);
        return is_array($cart) ? $cart : [];
    }
    return [];
}

//Cart becomes a cookie, solely here for assignment requirements--it could go directly to the database and be just as functional.
function saveCartCookie($cart) {
    setcookie('cart', json_encode($cart), time() + 86400, '/'); // 1 day
}

//Add item or change item amount.
function addToCartDB($pdo, $uid, $pid, $qty, $price) {
    $stmt = $pdo->prepare("
        INSERT INTO cart (UID, PID, quantity, price)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), price = VALUES(price)
    ");
    $stmt->execute([$uid, $pid, $qty, $price]);
}

// Sync all cookie items to DB
function syncCartToDatabase($pdo, $uid) {
    $cart = getCartCookie();
    foreach ($cart as $pid => $item) {
        addToCartDB($pdo, $uid, $pid, $item['quantity'], $item['price']);
    }
}

// Clear the cart cookie.
function clearCartCookie() {
    setcookie('cart', '', time() - 3600, '/');
}
