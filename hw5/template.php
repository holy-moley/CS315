<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function template_header($pageTitle) {
    echo <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$pageTitle}</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style-mobile.css" media="screen and (max-width: 767px)">
    <link rel="stylesheet" href="css/style-tablet.css" media="(min-width: 768px)">
    <link rel="stylesheet" href="css/style-desktop.css" media="(min-width: 1024px)">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="nav-left">
        <a class="logo" href="index.php">Mungo's Magic Emporium</a>
        <button class="hamburger" id="hamburger">&#9776;</button>
    </div>
    <div class="nav-right" id="nav-links">
        <a href="products.php">Products</a>
        <a href="about.php">About</a>
EOT;

    if (isset($_SESSION['user'])) {
        echo '<a href="cart.php">Cart</a>';
        echo '<a href="account.php">Account</a>';
        echo '<a href="logout.php">Logout</a>';
    } else {
        echo '<a href="login.php">Login</a>';
        echo '<a href="register.php">Register</a>';
    }

    echo <<<EOT
    </div>
</nav>

<div class="content">

<!-- Scroll shrink JS -->
<script>
window.addEventListener("scroll", function () {
    const nav = document.querySelector(".navbar");
    if (window.scrollY > 20) {
        nav.classList.add("scrolled");
    } else {
        nav.classList.remove("scrolled");
    }
});
</script>

<!-- Mobile menu toggle JS -->
<script>
const hamburger = document.getElementById('hamburger');
const navLinks = document.getElementById('nav-links');
hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
});
</script>

<script>
// Makes sure all required fields have something. Proper validation occurs via PHP.
function validateRequiredFields(event) {
    const form = event.target || (event.srcElement && event.srcElement.form);
    if (!form) return true;
    let valid = true;

    Array.from(form.elements).forEach(el => {
        if (!el.name) return;
        if (el.type === 'submit' || el.type === 'button' || el.type === 'hidden' || el.disabled) return;

        if (el.hasAttribute('required')) {
            let val = el.value;
            if (typeof val === 'string') val = val.trim();
            const errSpan = document.getElementById(el.name + "_Err");
            if (!val) {
                valid = false;
                if (errSpan) { errSpan.textContent = "* Required"; }
                el.classList.add('field-error');
            } else {
                if (errSpan) errSpan.textContent = "";
                el.classList.remove('field-error');
            }
        }
    });

    if (!valid) {
        const firstMissing = form.querySelector('.field-error');
        if (firstMissing) firstMissing.focus();
    }

    if (!valid) {
        event.preventDefault();
        return false;
    }
    return true;
}
</script>
EOT;
}

function template_footer() {
    echo <<<EOT
    </div> <!-- end content -->

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Mungo's Magic Emporium</p>
    </footer>

    </body>
    </html>

EOT;
}
