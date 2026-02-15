<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

/* importante: carica actions per gestire il POST */
require __DIR__ . '/actions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT 
        c.id,
        c.codice_carta,
        c.punti,
        u.nome
     FROM carte_fedelta c
     JOIN utenti u ON u.id = c.utente_id
     WHERE c.id = ?"
);
$stmt->execute([$id]);
$carta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$carta) {
    die('Carta non trovata');
}

$titolo = 'Modifica carta';
require ROOT_PATH . '/themes/semplice/header.php';
?>

<h2>Modifica carta</h2>

<p><strong>Cliente:</strong> <?= htmlspecialchars($carta['nome']) ?></p>

<form method="post" style="max-width:400px">

<input type="hidden" name="mod_carta" value="1">
<input type="hidden" name="id" value="<?= $carta['id'] ?>">

<label>Codice carta</label>
<input name="codice_carta"
       value="<?= htmlspecialchars($carta['codice_carta']) ?>"
       required
       style="width:100%;padding:8px">

<label>Punti</label>
<input name="punti"
       type="number"
       min="0"
       value="<?= (int)$carta['punti'] ?>"
       required
       style="width:100%;padding:8px">

<br><br>
<button>💾 Salva</button>
<a href="<?= BASE_URL ?>/?mod=admin&tab=carte">Annulla</a>

</form>

<?php
require ROOT_PATH . '/themes/semplice/footer.php';
