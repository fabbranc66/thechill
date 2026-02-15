<?php
declare(strict_types=1);

/* ==========================================================
   MODULO SCANSIONI
   ACTIONS
========================================================== */

richiedi_ruolo('amministratore');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['del_scansione'])) {

        $id = (int)$_POST['del_scansione'];

        try {
            $pdo->beginTransaction();

            $s = $pdo->prepare(
                'SELECT carta_id, punti
                 FROM log_scansioni
                 WHERE id=?'
            );
            $s->execute([$id]);

            if ($row = $s->fetch()) {

                $pdo->prepare(
                    'UPDATE carte_fedelta
                     SET punti = GREATEST(punti-?,0)
                     WHERE id=?'
                )->execute([
                    (int)$row['punti'],
                    (int)$row['carta_id']
                ]);

                $pdo->prepare(
                    'DELETE FROM log_scansioni WHERE id=?'
                )->execute([$id]);
            }

            $pdo->commit();

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }

        header('Location: ' . BASE_URL . '/?mod=scansioni');
        exit;
    }
}