<?php
declare(strict_types=1);

require __DIR__ . '/query.php';
require __DIR__ . '/actions.php';

$utenti = clienti_lista($pdo);
?>

<h2>Clienti</h2>

<table border="1" cellpadding="8" cellspacing="0">
  <tr>
    <th>Nome</th>
    <th>Email</th>
    <th>Telefono</th>
    <th>QR</th>
    <th>Azioni</th>
  </tr>

  <?php foreach ($utenti as $u): ?>
    <?php
      $link_cliente = BASE_URL . '/?mod=clienti&azione=cliente&t=' . $u['token_accesso'];
    ?>
    <tr>
      <td><?= htmlspecialchars($u['nome']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= htmlspecialchars($u['telefono']) ?></td>

      <td>
        <?php if (!empty($u['token_accesso'])): ?>
          <img src="<?= BASE_URL ?>/assets/qr/<?= htmlspecialchars($u['token_accesso']) ?>.png"
               width="60" alt="QR">
        <?php endif; ?>
      </td>

<td>
  <!-- Modifica cliente -->
  <a href="<?= BASE_URL ?>/?mod=clienti&azione=edit&id=<?= $u['id'] ?>">✏️</a>

  <!-- Apri pagina cliente -->
  <?php if (!empty($u['token_accesso'])): ?>
    <a href="<?= BASE_URL ?>/?mod=clienti&azione=cliente&t=<?= $u['token_accesso'] ?>">🔗</a>
  <?php endif; ?>

  <!-- Premi riscattati -->
  <a href="<?= BASE_URL ?>/?mod=riscatti&azione=cliente&id=<?= $u['id'] ?>">🧾</a>

  <!-- Elimina cliente -->
  <form method="post" style="display:inline"
        onsubmit="return confirm('Eliminare cliente?')">
    <input type="hidden" name="del_cliente" value="<?= $u['id'] ?>">
    <button>🗑</button>
  </form>
</td>
    </tr>
  <?php endforeach; ?>
</table>
