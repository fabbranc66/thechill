<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

/* ==========================================================
   CREAZIONE CLIENTE
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['crea_cliente'])) {

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if ($nome !== '' && $email !== '') {

            $token = bin2hex(random_bytes(16));

            $stmt = $pdo->prepare(
                "INSERT INTO utenti
                 (nome, email, telefono, ruolo, token_accesso)
                 VALUES (?, ?, ?, 'cliente', ?)"
            );

            $stmt->execute([
                $nome,
                $email,
                $telefono,
                $token
            ]);

            /* generazione QR */
            $lib = ROOT_PATH . '/lib/phpqrcode/qrlib.php';
            $dir = ROOT_PATH . '/assets/qr/';

            require_once $lib;

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $url = BASE_URL . '/modules/clienti/cliente.php?t=' . $token;
            $file = $dir . $token . '.png';

            QRcode::png($url, $file, QR_ECLEVEL_L, 6);
        }

            header('Location: ' . BASE_URL . '/?mod=clienti');
            exit;
    }
}

/* ==========================================================
   MODIFICA CLIENTE
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['mod_cliente'])) {

        $id        = (int)($_POST['id'] ?? 0);
        $nome      = trim($_POST['nome'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telefono  = trim($_POST['telefono'] ?? '');

        if ($id > 0 && $nome !== '') {

            $stmt = $pdo->prepare(
                "UPDATE utenti
                 SET nome = ?, email = ?, telefono = ?
                 WHERE id = ?
                 AND ruolo = 'cliente'"
            );

            $stmt->execute([
                $nome,
                $email ?: null,
                $telefono ?: null,
                $id
            ]);
        }

            header('Location: ' . BASE_URL . '/?mod=admin&tab=clienti');
        exit;
    }
}

/* ==========================================================
   ELIMINAZIONE CLIENTE
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['del_cliente'])) {

        $id = (int)$_POST['del_cliente'];

        $stmt = $pdo->prepare(
            "DELETE FROM utenti
             WHERE id = ?
             AND ruolo = 'cliente'"
        );
        $stmt->execute([$id]);

        header('Location: ' . BASE_URL . '/?mod=admin&tab=clienti');
        exit;
    }
}
