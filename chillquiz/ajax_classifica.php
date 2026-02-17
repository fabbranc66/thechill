<?php
include "config.php";
$partita_id = $_SESSION["partita_id"];
$res = $conn->query("SELECT nome, punteggio FROM giocatori WHERE partita_id=$partita_id ORDER BY punteggio DESC LIMIT 10");
while($row = $res->fetch_assoc()){
echo $row["nome"]." - ".$row["punteggio"]."<br>";
}
?>