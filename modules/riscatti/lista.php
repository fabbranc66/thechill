<?php
declare(strict_types=1);

/* ==========================================================
   MODULO RISCATTI
   LISTA
========================================================== */

require __DIR__ . '/query.php';
require __DIR__ . '/actions.php';

$riscatti = riscatti_lista($pdo);

$titolo = 'Riscatti';
?>

<h2>Riscatti premi</h2>

<table border="1" cellpadding="8" cellspacing="0">
  <tr>
    <th>Cliente</th>
    <th>Carta</th>
    <th>Premio</th>
    <th>Data</th>
    <th>Stato</th>
  </tr>

  <?php foreach ($riscatti as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['cliente']) ?></td>
      <td><?= htmlspecialchars($r['codice_carta']) ?></td>
      <td><?= htmlspecialchars($r['premio']) ?></td>
      <td><?= htmlspecialchars($r['data_riscatto']) ?></td>
      <td>
        <?= $r['riscattato'] ? '✅ Riscattato' : '⏳ In attesa' ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php
