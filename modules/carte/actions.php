<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

/* ==========================================================
   RIGENERA QR (POST)
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rigenera_qr'])) {

    $id = (int)($_POST['id'] ?? 0);

    /* recupera token cliente collegato alla carta */
    $stmt = $pdo->prepare(
        "SELECT u.token_accesso
         FROM carte_fedelta c
         JOIN utenti u ON u.id = c.utente_id
         WHERE c.id = ?"
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['token_accesso'])) {

        $token = $row['token_accesso'];

        $lib = ROOT_PATH . '/lib/phpqrcode/qrlib.php';
        $dir = ROOT_PATH . '/assets/qr/';

        require_once $lib;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

     $url = BASE_URL . '/?mod=clienti&azione=cliente&t=' . $token;
        $file = $dir . $token . '.png';

        QRcode::png($url, $file, QR_ECLEVEL_L, 6);
    }

    header('Location: ' . BASE_URL . '/?mod=admin&tab=carte');
    exit;
}

/* ==========================================================
   MODIFICA CARTA
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mod_carta'])) {

    $id = (int)($_POST['id'] ?? 0);
    $codice = trim($_POST['codice_carta'] ?? '');
    $punti = (int)($_POST['punti'] ?? 0);

    if ($id > 0 && $codice !== '') {

        $stmt = $pdo->prepare(
            "UPDATE carte_fedelta
             SET codice_carta = ?, punti = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $codice,
            $punti,
            $id
        ]);
    }

    header('Location: ' . BASE_URL . '/?mod=admin&tab=carte');
    exit;
}
