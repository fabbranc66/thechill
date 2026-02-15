<?php
declare(strict_types=1);

require_once __DIR__ . '/query.php';

richiedi_ruolo('amministratore');

$errore = null;
$messaggio = null;

/* ==========================================================
   AZIONI POST
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* CREA / MODIFICA */
    if (isset($_POST['salva'])) {

        $id     = (int)($_POST['id'] ?? 0);
        $nome   = trim($_POST['nome'] ?? '');
        $punti  = (int)($_POST['punti_necessari'] ?? 0);
        $attivo = isset($_POST['attivo']) ? 1 : 0;

        if ($nome === '' || $punti <= 0) {
            $errore = 'Inserire nome e punti validi';
        } else {

            if ($id > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE premi
                     SET nome = ?, punti_necessari = ?, attivo = ?
                     WHERE id = ?"
                );
                $stmt->execute([$nome, $punti, $attivo, $id]);

                $messaggio = 'Premio aggiornato';
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO premi (nome, punti_necessari, attivo)
                     VALUES (?, ?, ?)"
                );
                $stmt->execute([$nome, $punti, $attivo]);

                $messaggio = 'Premio creato';
            }
        }
    }

    /* ELIMINA */
    if (isset($_POST['elimina'])) {
        $id = (int)$_POST['elimina'];

        $stmt = $pdo->prepare("DELETE FROM premi WHERE id = ?");
        $stmt->execute([$id]);

        $messaggio = 'Premio eliminato';
    }
}

/* ==========================================================
   MODIFICA
========================================================== */
$edit = null;
if (isset($_GET['edit'])) {
    $edit = premi_carica($pdo, (int)$_GET['edit']);
}
/* ==========================================================
   RISCATTO PREMIO
========================================================== */
if (isset($_POST['riscatta_premio'])) {

    $carta_id  = (int)($_POST['carta_id'] ?? 0);
    $premio_id = (int)($_POST['premio_id'] ?? 0);

    try {
        $pdo->beginTransaction();

        $carta = premi_carica_carta($pdo, $carta_id);
        if (!$carta) {
            throw new RuntimeException('Carta non trovata');
        }

        $stmt = $pdo->prepare(
            "SELECT id, punti_necessari
             FROM premi
             WHERE id = ? AND attivo = 1
             FOR UPDATE"
        );
        $stmt->execute([$premio_id]);
        $premio = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$premio) {
            throw new RuntimeException('Premio non valido');
        }

        if ((int)$premio['punti_necessari'] > (int)$carta['punti']) {
            throw new RuntimeException('Punti insufficienti');
        }

        /* SCALA PUNTI */
        $stmt = $pdo->prepare(
            "UPDATE carte_fedelta
             SET punti = punti - ?
             WHERE id = ?"
        );
        $stmt->execute([
            (int)$premio['punti_necessari'],
            $carta_id
        ]);

        /* REGISTRA RISCATTO */
        $stmt = $pdo->prepare(
            "INSERT INTO riscatti_premi
             (carta_id, premio_id, punti_scalati, admin_id, riscattato)
             VALUES (?,?,?,?,1)"
        );
        $stmt->execute([
            $carta_id,
            $premio_id,
            (int)$premio['punti_necessari'],
            (int)$_SESSION['utente']['id']
        ]);

        $pdo->commit();

        header('Location: ' . BASE_URL . '/?mod=admin&tab=carte');
        exit;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errore = 'Errore riscatto: ' . $e->getMessage();
    }
}
