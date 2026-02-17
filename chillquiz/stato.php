<?php
include "config.php";

if (!isset($_SESSION["partita_id"])) {
    echo "errore";
    exit;
}

$partita_id = $_SESSION["partita_id"];

$res = $conn->query("
SELECT stato
FROM partite
WHERE id=$partita_id
");

$row = $res->fetch_assoc();

echo $row["stato"];
