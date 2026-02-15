<?php
declare(strict_types=1);

/* evita doppia esecuzione */
if (defined('LOGIN_ACTIONS_LOADED')) {
    return;
}
define('LOGIN_ACTIONS_LOADED', true);

$errore = null;
$messaggio = null;

// messaggio logout
if (isset($_GET['logout'])) {
    $messaggio = 'Logout effettuato con successo';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // validazione base
    if ($email === '' || $password === '') {
        $errore = 'Credenziali non valide';
        return;
    }

    $stmt = $pdo->prepare(
        "SELECT id, nome, email, password, ruolo
         FROM utenti
         WHERE email = ?
         LIMIT 1"
    );
    $stmt->execute([$email]);

    $utente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$utente ||
        empty($utente['password']) ||
        !is_string($utente['password']) ||
        !password_verify($password, $utente['password'])
    ) {
        $errore = 'Credenziali non valide';
        return;
    }

    // login
    login_utente($utente);

    header('Location: ' . BASE_URL . '/?mod=admin');
    exit;
}
