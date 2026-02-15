<?php
declare(strict_types=1);

/* ==========================================================
   MODULO ADMIN - AZIONI
========================================================== */

richiedi_ruolo('amministratore');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

/* --- ELIMINA SCANSIONE --- */
if (isset($_POST['del_scansione'])) {
    $id = (int)$_POST['del_scansione'];
    try {
        $pdo->beginTransaction();
        $s = $pdo->prepare(
            'SELECT carta_id,punti FROM log_scansioni WHERE id=?'
        );
        $s->execute([$id]);
        if ($row = $s->fetch()) {
            $pdo->prepare(
                'UPDATE carte_fedelta
                 SET punti = GREATEST(punti-?,0)
                 WHERE id=?'
            )->execute([(int)$row['punti'], (int)$row['carta_id']]);

            $pdo->prepare(
                'DELETE FROM log_scansioni WHERE id=?'
            )->execute([$id]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
    }
    header('Location: ?mod=admin&tab=scansioni');
    exit;
}

/* --- ELIMINA CARTA --- */
if (isset($_POST['del_carta'])) {
    $id = (int)$_POST['del_carta'];
    try {
        $pdo->beginTransaction();
        $pdo->prepare(
            'DELETE FROM log_scansioni WHERE carta_id=?'
        )->execute([$id]);

        $pdo->prepare(
            'DELETE FROM carte_fedelta WHERE id=?'
        )->execute([$id]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
    }
    header('Location: ?mod=admin&tab=carte');
    exit;
}

/* --- ELIMINA CLIENTE --- */
if (isset($_POST['del_cliente'])) {
    $id = (int)$_POST['del_cliente'];
    try {
        $pdo->beginTransaction();

        $pdo->prepare(
            'DELETE l FROM log_scansioni l
             JOIN carte_fedelta c ON c.id=l.carta_id
             WHERE c.utente_id=?'
        )->execute([$id]);

        $pdo->prepare(
            'DELETE FROM carte_fedelta WHERE utente_id=?'
        )->execute([$id]);

        $pdo->prepare(
            'DELETE FROM utenti WHERE id=?'
        )->execute([$id]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
    }
    header('Location: ?mod=admin&tab=clienti');
    exit;
}
