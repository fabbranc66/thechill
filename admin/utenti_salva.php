<?php
// admin/utenti_salva.php
// MODE: CODEX — FILE COMPLETO SALVATAGGIO UTENTE

declare(strict_types=1);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

richiedi_ruolo('amministratore');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('METODO NON CONSENTITO');
}

if (
    !isset($_POST['id']) ||
    !ctype_digit($_POST['id']) ||
    empty($_POST['nome'])
) {
    die('DATI NON VALIDI');
}

$utente_id = (int)$_POST['id'];
$nome      = trim($_POST['nome']);
$email     = trim($_POST['email'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');

/* =========================
   UPDATE UTENTE
========================= */
$stmt = $pdo->prepare("
    UPDATE utenti
    SET nome = ?, email = ?, telefono = ?
    WHERE id = ?
");
$stmt->execute([
    $nome,
    $email !== '' ? $email : null,
    $telefono !== '' ? $telefono : null,
    $utente_id
]);

header('Location: index.php?tab=clienti');
exit;