<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

/* ==========================================================
   RIGENERA QR DIRETTO DA LISTA (POST)
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rigenera_qr'])) {

    $id = (int)($_POST['id'] ?? 0);

    $stmt = $pdo->prepare(
        "SELECT u.token_accesso
         FROM carte_fedelta c
         JOIN utenti u ON u.id = c.utente_id
         WHERE c.id = ?"
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['token_accesso'])) {

        $token = $row['token_accesso'];

        $lib = ROOT_PATH . '/lib/phpqrcode/qrlib.php';
        $dir = ROOT_PATH . '/assets/qr/';

        require_once $lib;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        /* URL cliente interno al sistema */
        $url = BASE_URL . '/?mod=clienti&azione=cliente&t=' . $token;
        $file = $dir . $token . '.png';

        QRcode::png($url, $file, QR_ECLEVEL_L, 6);
    }

    header('Location: ' . BASE_URL . '/?mod=admin&tab=carte');
    exit;
}
/* ==========================================================
   ELIMINAZIONE CARTA
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['del_carta'])) {

        $id = (int)$_POST['del_carta'];

        $stmt = $pdo->prepare(
            "DELETE FROM carte_fedelta
             WHERE id = ?"
        );
        $stmt->execute([$id]);

        header('Location: ' . BASE_URL . '/?mod=admin&tab=carte');
        exit;
    }
}
/* ==========================================================
   LISTA CARTE
========================================================== */

$carte = $pdo->query(
    "SELECT 
        c.id,
        c.codice_carta,
        c.punti,
        u.nome,
        u.email,
        u.token_accesso
     FROM carte_fedelta c
     JOIN utenti u ON u.id = c.utente_id
     ORDER BY u.nome"
)->fetchAll(PDO::FETCH_ASSOC);

$titolo = 'Carte fedeltà';
?>

<h2>Carte fedeltà</h2>

<table border="1" cellpadding="8" cellspacing="0">
  <tr>
    <th>Codice carta</th>
    <th>Punti</th>
    <th>Azioni</th>
  </tr>

  <?php foreach ($carte as $c): ?>
    <?php
      $link_cliente = BASE_URL . '/?mod=clienti&azione=cliente&t=' . $c['token_accesso'];
    ?>
    <tr>
      <td><?= htmlspecialchars($c['codice_carta']) ?></td>
      <td><?= (int)$c['punti'] ?></td>

      <td>
        <!-- EDIT CARTA -->
        <a href="<?= BASE_URL ?>/?mod=carte&azione=edit&id=<?= $c['id'] ?>">
          ✏️
        </a>

        <!-- APRI AREA CLIENTE -->
        <?php if (!empty($c['token_accesso'])): ?>
          <a href="<?= $link_cliente ?>">
            🔗
          </a>
        <?php endif; ?>

        <!-- RISCATTO PREMIO -->
        <a href="<?= BASE_URL ?>/?mod=carte&azione=riscatta&id=<?= $c['id'] ?>">
          🎁
        </a>

        <!-- RIGENERA QR -->
        <form method="post" style="display:inline">
            <input type="hidden" name="rigenera_qr" value="1">
            <input type="hidden" name="id" value="<?= $c['id'] ?>">
            <button type="submit">🔄</button>
        </form>

        <!-- ELIMINA CARTA -->
        <form method="post" style="display:inline"
              onsubmit="return confirm('Eliminare questa carta?')">
            <button type="submit" name="del_carta" value="<?= $c['id'] ?>">🗑</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
