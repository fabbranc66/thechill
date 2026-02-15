<?php
declare(strict_types=1);

$errore = null;
$messaggio = null;

// messaggio logout
if (isset($_GET['logout'])) {
    $messaggio = 'Logout effettuato con successo';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT id, nome, email, password, ruolo
         FROM utenti
         WHERE email = ?
         LIMIT 1"
    );
    $stmt->execute([$email]);

    $utente = $stmt->fetch();

    if (!$utente || !password_verify($password, $utente['password'])) {
        $errore = 'Credenziali non valide';
    } else {
        // login
        login_utente($utente);

        header('Location: ' . BASE_URL . '/');
        exit;
    }
}
