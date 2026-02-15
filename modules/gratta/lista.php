<?php
declare(strict_types=1);

/* ==========================================================
   MODULO GRATTA
   LISTA
========================================================== */

require __DIR__ . '/actions.php';
require __DIR__ . '/query.php';

/* ==========================================================
   CARICAMENTO DATI
========================================================== */
$gratta = gratta_lista($pdo);

$titolo = 'Gratta e vinci';
?>

<h2>Gratta e vinci</h2>

<table border="1" cellpadding="8" cellspacing="0">
  <tr>
    <th>Cliente</th>
    <th>Premio</th>
    <th>Data</th>
    <th>Stato</th>
  </tr>

  <?php foreach ($gratta as $g): ?>
    <tr>
      <td><?= htmlspecialchars($g['cliente']) ?></td>

      <td>
        <?php if ($g['vincente']): ?>
          🎁 <?= (int)$g['premio_punti'] ?> punti
        <?php else: ?>
          ❌ Nessuna vincita
        <?php endif; ?>
      </td>

      <td><?= htmlspecialchars($g['creato_il']) ?></td>

      <td>
        <?php
        if (!$g['grattato']) {
            echo '🎲 Non grattato';
        } elseif ($g['riscattato']) {
            echo '✅ Riscattato';
        } elseif ($g['vincente']) {
            echo '🏆 Vincente';
        } else {
            echo '❌ Perdente';
        }
        ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php
