<?php
declare(strict_types=1);

/* ==========================================================
   INIZIALIZZAZIONE
========================================================== */
require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../lib/phpqrcode/qrlib.php';

richiedi_ruolo('amministratore');

$errore = null;

/* ==========================================================
   CARICAMENTO CLIENTI ESISTENTI
========================================================== */
$clienti = $pdo->query(
  "SELECT id, nome, email
   FROM utenti
   WHERE ruolo = 'cliente'
   ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

/* ==========================================================
   GESTIONE FORM
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $cliente_id = (int)($_POST['cliente_id'] ?? 0);
  $nome       = trim($_POST['nome'] ?? '');
  $email      = trim($_POST['email'] ?? '');
  $telefono   = trim($_POST['telefono'] ?? '');

  if ($cliente_id === 0 && $nome === '') {
    $errore = 'Seleziona un cliente o inserisci un nuovo nome';
  } else {

    try {
      $pdo->beginTransaction();

      /* CREAZIONE NUOVO CLIENTE */
      if ($cliente_id === 0) {

        $token = bin2hex(random_bytes(16));

        $stmt = $pdo->prepare(
          "INSERT INTO utenti (nome, email, telefono, ruolo, token_accesso)
           VALUES (?, ?, ?, 'cliente', ?)"
        );
        $stmt->execute([
          $nome,
          $email ?: null,
          $telefono ?: null,
          $token
        ]);

        $cliente_id = (int)$pdo->lastInsertId();
      }

      /* RECUPERO DATI CLIENTE */
      $stmt = $pdo->prepare(
        "SELECT nome, token_accesso
         FROM utenti
         WHERE id = ?
         LIMIT 1"
      );
      $stmt->execute([$cliente_id]);
      $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

      $nome_cliente = $cliente['nome'] ?? 'Cliente';
      $token = $cliente['token_accesso'] ?? '';

      if (!$token) {
        $token = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare(
          "UPDATE utenti SET token_accesso = ? WHERE id = ?"
        );
        $stmt->execute([$token, $cliente_id]);
      }

      /* VERIFICA CARTA ESISTENTE */
      $stmt = $pdo->prepare(
        "SELECT id, codice_carta, punti
         FROM carte_fedelta
         WHERE utente_id = ?
         LIMIT 1"
      );
      $stmt->execute([$cliente_id]);
      $carta = $stmt->fetch();

      if ($carta) {
        $codice_carta = $carta['codice_carta'];
        $punti = (int)$carta['punti'];
      } else {
        $codice_carta = 'CARD' . strtoupper(bin2hex(random_bytes(4)));
        $punti = 0;

        $stmt = $pdo->prepare(
          "INSERT INTO carte_fedelta (utente_id, codice_carta, punti)
           VALUES (?, ?, 0)"
        );
        $stmt->execute([$cliente_id, $codice_carta]);
      }

      $pdo->commit();

      /* =====================================================
         CREAZIONE IMMAGINE TESSERA
      ===================================================== */

      if (!function_exists('imagecreatetruecolor')) {
        throw new Exception('GD non attiva');
      }

      $root = realpath(__DIR__ . '/../');
      $dir = $root . '/upload/tessere/';

      if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
      }

      $filename = $token . '.png';
      $filepath = $dir . $filename;

      /* URL tessera */
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
      $host = $_SERVER['HTTP_HOST'];
      $basePath = dirname($_SERVER['SCRIPT_NAME'], 2);

      $qr_url = $protocol . $host . $basePath . '/tessera_view.php?t=' . $token;

      /* GENERAZIONE QR */
      $tempQr = $dir . 'qr_' . $token . '.png';
      QRcode::png($qr_url, $tempQr, QR_ECLEVEL_L, 5, 2);

      /* DIMENSIONI CARTA */
      $width = 850;
      $height = 536;

      $image = imagecreatetruecolor($width, $height);

      /* Colori */
      $bg = imagecolorallocate($image, 0, 122, 94);   // verde smeraldo
      $white = imagecolorallocate($image, 255, 255, 255);
      $accent = imagecolorallocate($image, 255, 220, 120);

      imagefill($image, 0, 0, $bg);

      /* FONT */
      $font = $root . '/assets/fonts/ArchivoBlack-Regular.ttf';

      /* MARGINI */
      $margin = 40;
      $rightMargin = 40;

      /* LOGHI IN ALTO */
      $logoPath = $root . '/assets/img/logo.png';
      $fedePath = $root . '/assets/img/fedechill.png';

      if (file_exists($logoPath)) {
        $logo = imagecreatefrompng($logoPath);
        $lw = imagesx($logo);
        $lh = imagesy($logo);

        $newW = 120;
        $newH = intval($lh * ($newW / $lw));

        imagecopyresampled($image, $logo, $margin, $margin, 0, 0, $newW, $newH, $lw, $lh);
        imagedestroy($logo);
      }

      if (file_exists($fedePath)) {
        $fede = imagecreatefrompng($fedePath);
        $fw = imagesx($fede);
        $fh = imagesy($fede);

        $newW = 200;
        $newH = intval($fh * ($newW / $fw));

        imagecopyresampled(
          $image,
          $fede,
          $width - $newW - $margin,
          $margin,
          0,
          0,
          $newW,
          $newH,
          $fw,
          $fh
        );
        imagedestroy($fede);
      }

      /* QR CENTRALE */
      if (file_exists($tempQr)) {
        $qr = imagecreatefrompng($tempQr);

        $qrSize = 240;
        $qrX = ($width - $qrSize) / 2;
        $qrY = ($height - $qrSize) / 2 - 10;

        imagecopyresampled(
          $image,
          $qr,
          $qrX,
          $qrY,
          0,
          0,
          $qrSize,
          $qrSize,
          imagesx($qr),
          imagesy($qr)
        );

        imagedestroy($qr);
        unlink($tempQr);
      }

      /* TESTI */
      if (file_exists($font)) {

        // Separazione nome e cognome
        $nome_parts = explode(' ', trim($nome_cliente), 2);
        $nome = strtoupper($nome_parts[0] ?? '');
        $cognome = strtoupper($nome_parts[1] ?? '');

        /* NOME E COGNOME A SINISTRA */
        imagettftext($image, 28, 0, $margin, $height - 150, $white, $font, $nome);
        imagettftext($image, 28, 0, $margin, $height - 110, $white, $font, $cognome);

        /* PUNTI A DESTRA */
        $puntiLabel = 'PUNTI';
        $puntiValue = (string)$punti;

        $bboxLabel = imagettfbbox(20, 0, $font, $puntiLabel);
        $labelWidth = $bboxLabel[2] - $bboxLabel[0];

        $bboxValue = imagettfbbox(42, 0, $font, $puntiValue);
        $valueWidth = $bboxValue[2] - $bboxValue[0];

        $labelX = $width - $labelWidth - $rightMargin;
        $valueX = $width - $valueWidth - $rightMargin;

        imagettftext($image, 20, 0, $labelX, $height - 60, $white, $font, $puntiLabel);
        imagettftext($image, 42, 0, $valueX, $height - 15, $accent, $font, $puntiValue);

        /* CODICE CARTA */
        imagettftext($image, 16, 0, $width - 260, $height - 20, $white, $font, $codice_carta);
      }

      imagepng($image, $filepath);
      imagedestroy($image);

      /* REDIRECT */
      header('Location: ../tessera_view.php?t=' . urlencode($token));
      exit;

    } catch (Throwable $e) {
      if ($pdo->inTransaction()) {
        $pdo->rollBack();
      }
      $errore = $e->getMessage();
    }
  }
}

/* ==========================================================
   TEMPLATE
========================================================== */
$titolo = 'Nuova tessera cliente';
require __DIR__ . '/../themes/semplice/header.php';
?>

<h2>Nuova tessera cliente</h2>

<?php if ($errore): ?>
  <div style="color:#900;margin-bottom:15px">
    <?= htmlspecialchars($errore) ?>
  </div>
<?php endif; ?>

<form method="post" style="max-width:500px">

  <h3>Cliente esistente</h3>

  <select name="cliente_id" style="width:100%;padding:8px">
    <option value="0">— Seleziona —</option>
    <?php foreach ($clienti as $c): ?>
      <option value="<?= $c['id'] ?>">
        <?= htmlspecialchars($c['nome']) ?>
        <?= $c['email'] ? ' ('.$c['email'].')' : '' ?>
      </option>
    <?php endforeach; ?>
  </select>

  <hr>

  <h3>Oppure nuovo cliente</h3>

  <label>Nome</label>
  <input name="nome" style="width:100%;padding:8px">

  <label>Email</label>
  <input name="email" type="email" style="width:100%;padding:8px">

  <label>Telefono</label>
  <input name="telefono" style="width:100%;padding:8px">

  <br><br>

  <button>📇 Crea tessera</button>
</form>

<?php require __DIR__ . '/../themes/semplice/footer.php'; ?>
