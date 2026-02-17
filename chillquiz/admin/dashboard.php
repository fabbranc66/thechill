<?php
include "../config.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Admin</title>
<style>
body {
    font-family: Arial;
    background: #46178f;
    color: white;
    margin: 0;
}
header {
    background: #2d0f5f;
    padding: 15px;
    text-align: center;
}
.container {
    padding: 20px;
}
.card {
    background: #2d0f5f;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}
input, button, select {
    padding: 10px;
    margin: 5px 0;
    border: none;
    border-radius: 8px;
    font-size: 16px;
}
button {
    background: #ff3355;
    color: white;
    cursor: pointer;
}
a.button {
    display: inline-block;
    padding: 10px 15px;
    background: #ff3355;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}
table {
    width: 100%;
    background: #2d0f5f;
    border-radius: 10px;
    overflow: hidden;
}
td, th {
    padding: 10px;
    text-align: left;
}
</style>
</head>
<body>

<header>
    <h1>Dashboard Admin</h1>
    <a href="logout.php" class="button">Logout</a>
</header>

<div class="container">

    <!-- CREA QUIZ -->
    <div class="card">
        <h2>Crea nuovo quiz</h2>
        <form method="post">
            <input type="text" name="titolo" placeholder="Titolo quiz" required>
            <input type="number" name="tempo" placeholder="Tempo domanda (sec)" value="15">
            <button name="crea_quiz">Crea quiz</button>
        </form>

        <?php
        if (isset($_POST["crea_quiz"])) {
            $titolo = $_POST["titolo"];
            $tempo = $_POST["tempo"];

            $stmt = $conn->prepare("INSERT INTO quiz (titolo, tempo_domanda) VALUES (?, ?)");
            $stmt->bind_param("si", $titolo, $tempo);
            $stmt->execute();

            echo "<p>Quiz creato!</p>";
        }
        ?>
    </div>

    <!-- LISTA QUIZ -->
    <div class="card">
        <h2>Quiz esistenti</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Titolo</th>
                <th>Azioni</th>
            </tr>

            <?php
            $res = $conn->query("SELECT * FROM quiz ORDER BY id DESC");

            while ($q = $res->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo $q["id"]; ?></td>
                <td><?php echo $q["titolo"]; ?></td>
                <td>
                    <a class="button" href="domande.php?quiz_id=<?php echo $q["id"]; ?>">
                        Domande
                    </a>

                    <a class="button" href="avvia.php?quiz_id=<?php echo $q["id"]; ?>">
                        Avvia partita
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

</div>

</body>
</html>
