<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../../core/init.php';

/* ==========================================================
   INPUT
========================================================== */
$tavoloId = (int)($_GET['tavolo'] ?? 0);
if ($tavoloId <= 0) {
  http_response_code(400);
  exit('Tavolo non valido');
}

/* ==========================================================
   RECUPERO TAVOLO
========================================================== */
$stmt = $pdo->prepare(
  "SELECT id, nome, posti
   FROM v2_tavoli
   WHERE id = ? AND attivo = 1"
);
$stmt->execute([$tavoloId]);
$tavolo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tavolo) {
  http_response_code(404);
  exit('Tavolo non trovato');
}

/* ==========================================================
   STATO FORM
========================================================== */
$errore = null;
$successo = null;

/* ==========================================================
   POST → PRENOTAZIONE
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nome = trim($_POST['nome'] ?? '');
  $ora  = trim($_POST['ora'] ?? '');

  if ($nome === '' || $ora === '') {
    $errore = 'Compila tutti i campi';
  } else {

    $chk = $pdo->prepare(
      "SELECT COUNT(*)
       FROM v2_prenotazioni
       WHERE tavolo_id = ?
         AND data = CURDATE()
         AND stato IN ('prenotata','arrivato')"
    );
    $chk->execute([$tavoloId]);

    if ($chk->fetchColumn() > 0) {
      $errore = 'Tavolo non disponibile';
    } else {

      $codice = bin2hex(random_bytes(8));

      $ins = $pdo->prepare(
        "INSERT INTO v2_prenotazioni
         (tavolo_id, codice_accesso, nome_cliente, data, ora_inizio, ora_fine)
         VALUES (?, ?, ?, CURDATE(), ?, ADDTIME(?, '02:00:00'))"
      );
      $ins->execute([$tavoloId, $codice, $nome, $ora, $ora]);

      $successo = $codice;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<title>Prenota tavolo</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
* { box-sizing: border-box; }

body {
  margin: 0;
  font-family: system-ui, Arial, sans-serif;
  background: #f4f4f4;
  color: #222;
}

.card {
  max-width: 420px;
  margin: 30px auto;
  background: #fff;
  border-radius: 14px;
  padding: 18px;
  box-shadow: 0 4px 14px rgba(0,0,0,.1);
}

h1 {
  text-align: center;
  margin-top: 0;
  font-size: 20px;
}

label {
  display: block;
  font-weight: 600;
  margin-bottom: 6px;
}

input {
  width: 100%;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
  margin-bottom: 14px;
  font-size: 15px;
}

button {
  width: 100%;
  padding: 12px;
  background: #2ecc71;
  border: 0;
  color: #fff;
  font-size: 16px;
  font-weight: bold;
  border-radius: 10px;
  cursor: pointer;
}

.error {
  background: #fdecea;
  color: #c0392b;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 12px;
  text-align: center;
  font-weight: bold;
}

.success {
  background: #e8f5e9;
  color: #2e7d32;
  padding: 14px;
  border-radius: 10px;
  text-align: center;
  font-weight: bold;
}

code {
  display: block;
  margin-top: 10px;
  padding: 8px;
  background: #f4f4f4;
  border-radius: 6px;
  font-size: 14px;
}
</style>
</head>

<body>

<div class="card">

<?php if ($successo): ?>

  <div class="success">
    ✅ Prenotazione confermata<br><br>
    <strong><?= htmlspecialchars($tavolo['nome']) ?></strong><br><br>
    Codice accesso:<br>
    <code><?= htmlspecialchars($successo) ?></code>
  </div>

<?php else: ?>

  <h1><?= htmlspecialchars($tavolo['nome']) ?></h1>

  <?php if ($errore): ?>
    <div class="error"><?= htmlspecialchars($errore) ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Nome</label>
    <input type="text" name="nome" required>

    <label>Orario di arrivo</label>
    <input type="time" name="ora" required>

    <button type="submit">Prenota</button>
  </form>

<?php endif; ?>

</div>

</body>
</html>