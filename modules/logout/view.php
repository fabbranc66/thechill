<?php
declare(strict_types=1);

/* ==========================================================
   LOGOUT UTENTE
========================================================== */

// svuota sessione
$_SESSION = [];

// elimina cookie sessione
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

// distrugge sessione
session_destroy();

/* ==========================================================
   REDIRECT AL LOGIN
========================================================== */
header('Location: ' . BASE_URL . '/?mod=login&logout=1');
exit;
