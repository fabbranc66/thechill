<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../config.php";

if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = md5($_POST["password"]);

    $stmt = $conn->prepare("SELECT id FROM admin WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();

    /* versione compatibile Aruba */
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($admin_id);
        $stmt->fetch();

        $_SESSION["admin_id"] = $admin_id;

        header("Location: dashboard.php");
        exit;
    } else {
        $errore = "Credenziali non valide";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login</title>
<style>
body {
    background: #46178f;
    color: white;
    font-family: Arial;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.box {
    background: #2d0f5f;
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    width: 300px;
}
input, button {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    border: none;
    border-radius: 8px;
    font-size: 16px;
}
button {
    background: #ff3355;
    color: white;
    cursor: pointer;
}
.errore {
    color: #ffaaaa;
}
</style>
</head>
<body>

<div class="box">
    <h2>Admin Login</h2>

    <?php if ($errore): ?>
        <p class="errore"><?php echo $errore; ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Accedi</button>
    </form>
</div>

</body>
</html>
