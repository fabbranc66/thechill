<?php
declare(strict_types=1);

/* ==========================================================
   AZIONE SCANSIONE
========================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codice'])) {

    $codice = trim($_POST['codice']);

    if ($codice === '') {
        header('Location: ?mod=scansioni&vista=cassa&err=Codice vuoto');
        exit;
    }

    /* estrae token se è un link */
    if (strpos($codice, 't=') !== false) {
        parse_str(parse_url($codice, PHP_URL_QUERY), $params);
        $codice = $params['t'] ?? '';
    }

    if ($codice === '') {
        header('Location: ?mod=scansioni&vista=cassa&err=Token non valido');
        exit;
    }

    /* recupera carta e cliente */
    $stmt = $pdo->prepare(
        "SELECT c.id AS carta_id, u.nome, u.token_accesso
         FROM carte_fedelta c
         JOIN utenti u ON u.id = c.utente_id
         WHERE u.token_accesso = ?
         LIMIT 1"
    );
    $stmt->execute([$codice]);

    $carta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$carta) {
        header('Location: ?mod=scansioni&vista=cassa&err=Carta non trovata');
        exit;
    }

    /* punti da impostazioni */
    $punti_aggiunti = (int)($SETTINGS['incremento_scansione'] ?? 1);

    try {
        $pdo->beginTransaction();

        /* aggiorna punti */
        $pdo->prepare(
            "UPDATE carte_fedelta
             SET punti = punti + ?
             WHERE id = ?"
        )->execute([$punti_aggiunti, $carta['carta_id']]);

        /* log scansione */
        $pdo->prepare(
            "INSERT INTO log_scansioni
             (carta_id, punti, origine, data_scansione)
             VALUES (?, ?, 'scanner', NOW())"
        )->execute([$carta['carta_id'], $punti_aggiunti]);

        $pdo->commit();

        /* ritorna allo scanner con messaggio */
        $msg = $carta['nome'] . ' +' . $punti_aggiunti . ' punto/i';
        header('Location: ?mod=scansioni&vista=cassa&ok=' . urlencode($msg));
        exit;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        header('Location: ?mod=scansioni&vista=cassa&err=Errore scansione');
        exit;
    }
}
