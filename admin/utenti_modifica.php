<?php
// admin/utenti_modifica.php
// MODE: CODEX — FILE BASE MODIFICA UTENTE

declare(strict_types=1);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

richiedi_ruolo('amministratore');

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('ID UTENTE NON VALIDO');
}

$utente_id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT id, nome, email, telefono
    FROM utenti
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$utente_id]);
$utente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utente) {
    die('UTENTE NON TROVATO');
}

$titolo = 'Modifica utente';
require __DIR__ . '/../themes/semplice/header.php';
?>

<h2>Modifica utente</h2>

<form method="post" action="utenti_salva.php">
    <input type="hidden" name="id" value="<?= $utente['id'] ?>">

    <label>Nome</label><br>
    <input type="text" name="nome" value="<?= htmlspecialchars($utente['nome']) ?>" required><br><br>

    <label>Email</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($utente['email'] ?? '') ?>"><br><br>

    <label>Telefono</label><br>
    <input type="text" name="telefono" value="<?= htmlspecialchars($utente['telefono'] ?? '') ?>"><br><br>

    <button type="submit">💾 Salva</button>
</form>

<?php require __DIR__ . '/../themes/semplice/footer.php'; ?>