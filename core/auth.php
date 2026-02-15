<?php
declare(strict_types=1);

/* ==========================================================
   FUNZIONI DI AUTENTICAZIONE THECHILL
========================================================== */

/**
 * Restituisce utente loggato o null
 */
function utente(): ?array {
    return $_SESSION['utente'] ?? null;
}

/**
 * Verifica stato login
 */
function utente_loggato(): bool {
    return isset($_SESSION['utente']);
}

/**
 * Richiede login
 */
function richiedi_login(): void {
    if (!utente_loggato()) {
        header('Location: ' . BASE_URL . '/?mod=login');
        exit;
    }
}

/**
 * Richiede ruolo specifico
 */
function richiedi_ruolo(string $ruolo): void {
    richiedi_login();

    if (($_SESSION['utente']['ruolo'] ?? null) !== $ruolo) {
        http_response_code(403);
        exit('Accesso negato');
    }
}

/**
 * Effettua login utente
 */
function login_utente(array $utente): void {
    session_regenerate_id(true);

    $_SESSION['utente'] = [
        'id'    => $utente['id'],
        'nome'  => $utente['nome'],
        'email' => $utente['email'],
        'ruolo' => $utente['ruolo'],
    ];
}

/**
 * Logout utente
 */
function logout_utente(): void {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
