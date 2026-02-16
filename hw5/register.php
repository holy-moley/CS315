<?php
session_start();
require "db.php";
require "validate.php";

$message = "";
$emailErr = $usernameErr = $passwordErr = "";
$email = $username = $password = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // EMAIL
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        //Thankful this is in the slides, I don't like regular expressions.
        $email = testInput($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    //Letters, numbers, underscores, 3-32 chars
    if (empty($_POST["username"])) {
        $usernameErr = "Username is required";
    } else {
        $username = testInput($_POST["username"]);
        if (!preg_match("/^[A-Za-z0-9_]{3,32}$/", $username)) {
            $usernameErr = "Invalid username (3-32 chars, letters/numbers/underscore)";
        }
    }

// 4-32 characters, no whitespace.
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = testInput($_POST["password"]);

        // 4–32 characters, no spaces
        if (!preg_match("/^[\S]{4,32}$/", $password)) {
            $passwordErr = "Password must be 4-32 characters and have no spaces.";
        }
    }


    // If validation passed, check duplicates and insert
    if ($emailErr === "" && $usernameErr === "" && $passwordErr === "") {

        // Check for duplicate emails or usernames
        $sql = "SELECT 1 FROM users WHERE email = ? OR username = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $username]);

        if ($stmt->rowCount() > 0) {
            $message = "Email or username already exists.";
        } else {
            $sql = "INSERT INTO users (email, username, password) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email, $username, $password]); // per your instructor, plain

            // Logs you in after registration, expires in 30 days..
            $userId = $pdo->lastInsertId();
            $_SESSION["user"] = [
                "UID" => $userId,
                "username" => $username,
                "email" => $email,
                "expires" => time() + 86400
            ];

            header("Location: index.php");
            exit;
        }
    }
}
?>
<?php require_once("template.php");
template_header("Welcome!"); ?>

<h2>Register</h2>

<?php if ($message): ?>
    <p class="error"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form id="registerForm" method="POST" onsubmit="return validateRequiredFields(event);">
    Email:
    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
    <span class="error" id="email_Err"><?php echo $emailErr; ?></span>
    <br><br>

    Username:
    <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
    <span class="error" id="username_Err"><?php echo $usernameErr; ?></span>
    <br><br>

    Password:
    <input type="text" name="password" value="" required>
    <span class="error" id="password_Err"><?php echo $passwordErr; ?></span>
    <br><br>

    <button type="submit">Register</button>
</form>
<?php template_footer(); ?>