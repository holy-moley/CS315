<?php require_once("template.php");
template_header("Welcome!");
require "db.php";
//Checks login
if (!isset($_SESSION['user']['UID'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user']['UID'];
$username = $_SESSION['user']['username'];
$message = "";

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    //Removes everything in the user's cart, then removes the user itself. 
    $stmtCart = $pdo->prepare("DELETE FROM cart WHERE UID = ?");
    $stmtCart->execute([$uid]);
    //Orders are kept in the database, UID just becomes null. Would have been easier to just cascade the deletion, but I felt like this behavior would be closer to real-world application.
    $stmt = $pdo->prepare("DELETE FROM users WHERE UID = ?");
    $stmt->execute([$uid]);

    // End session, return to home.
    session_destroy();

    header("Location: index.php");
    exit;
}

?>

<h2>Account</h2>
<p>Welcome, <?= $username ?>!</p>

<p><a href="orderHistory.php"><button>View Order History</button></a></p>
<!--Confirmation thing to double check on account deletion.-->
<form method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">
    <button type="submit" name="delete_account" style="background-color:red; color:white;">Delete Account</button>
</form>

<?php template_footer(); ?>
