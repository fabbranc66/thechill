<?php
declare(strict_types=1);

/* ==========================================================
   MODULO SCANSIONI
   LISTA
========================================================== */

require __DIR__ . '/actions.php';
require __DIR__ . '/query.php';

$scansioni = scansioni_lista($pdo);
?>

<h3>Scansioni</h3>

<table border="1" cellpadding="8" cellspacing="0">
  <tr>
    <th>Cliente</th>
    <th>Punti</th>
    <th>Origine</th>
    <th>Data</th>
    <th>Azioni</th>
  </tr>

  <?php foreach ($scansioni as $s): ?>
    <tr>
      <td><?= htmlspecialchars($s['nome']) ?></td>
      <td><?= (int)$s['punti'] ?></td>
      <td><?= htmlspecialchars($s['origine']) ?></td>
      <td><?= htmlspecialchars($s['data_scansione']) ?></td>
      <td>
        <form method="post" style="display:inline"
              onsubmit="return confirm('Eliminare scansione?')">
          <input type="hidden" name="del_scansione" value="<?= $s['id'] ?>">
          <button>🗑</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
