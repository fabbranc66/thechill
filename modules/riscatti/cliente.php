<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

$id = (int)($_GET['id'] ?? 0);

/* dati cliente */
$stmt = $pdo->prepare(
    "SELECT nome
     FROM utenti
     WHERE id = ?
     AND ruolo = 'cliente'"
);
$stmt->execute([$id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die('Cliente non trovato');
}

/* riscatti del cliente */
$riscatti = $pdo->prepare(
    "SELECT 
        r.punti_scalati,
        r.data_riscatto
     FROM riscatti_premi r
     JOIN carte_fedelta c ON c.id = r.carta_id
     WHERE c.utente_id = ?
     ORDER BY r.data_riscatto DESC"
);
$riscatti->execute([$id]);
$lista = $riscatti->fetchAll(PDO::FETCH_ASSOC);

$titolo = 'Riscatti cliente';
require ROOT_PATH . '/themes/semplice/header.php';
?>

<h2>Riscatti di <?= htmlspecialchars($cliente['nome']) ?></h2>

<a href="<?= BASE_URL ?>/?mod=admin&tab=clienti">← Torna ai clienti</a>

<br><br>

<table border="1" cellpadding="8" cellspacing="0">
  <tr>
    <th>Punti scalati</th>
    <th>Data</th>
  </tr>

  <?php foreach ($lista as $r): ?>
    <tr>
      <td><?= (int)$r['punti_scalati'] ?></td>
      <td><?= htmlspecialchars($r['data_riscatto']) ?></td>
    </tr>
  <?php endforeach; ?>
</table>

<?php
require ROOT_PATH . '/themes/semplice/footer.php';
