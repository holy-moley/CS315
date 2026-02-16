<?php
require_once("template.php");
template_header("Welcome!");
session_start();
require "db.php";
require "validate.php"; 

$message = "";
$emailErr = $passwordErr = "";
$email = $password = "";
//Validation/sanitation
if ($_SERVER["REQUEST_METHOD"] === "POST") {


    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = testInput($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = testInput($_POST["password"]);
    }

    // Attempts login if the username and password are both there.
    if ($emailErr === "" && $passwordErr === "") {
        $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $password]);

        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $_SESSION["user"] = [
                "UID" => $user["UID"],
                "username" => $user["username"],
                "email" => $user["email"],
                "expires" => time() + 86400
            ];
                //Boots you to the main page if you login successfully.
            header("Location: index.php");
            exit();
        } else {
            $message = "Invalid email or password.";
        }
    }
}
?>
<h2>Login</h2>

<?php if ($message): ?>
    <p class="error"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form id="loginForm" method="POST" onsubmit="return validateRequiredFields(event);">
    Email:
    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
    <span class="error" id="email_Err"><?php echo $emailErr; ?></span>
    <br><br>

    Password:
    <input type="text" name="password" value="" required>
    <span class="error" id="password_Err"><?php echo $passwordErr; ?></span>
    <br><br>

    <button type="submit">Login</button>
</form>
<?php template_footer(); ?>