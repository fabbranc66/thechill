<?php
declare(strict_types=1);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

if (($_SESSION['utente']['ruolo'] ?? '') !== 'amministratore') {
  http_response_code(403);
  exit('Accesso negato');
}

$msg = '';

/* ===============================
   SALVATAGGIO PARAMETRI TESTUALI
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['key'])) {

  $key = $_POST['key'];
  $value = trim($_POST['value'] ?? '');

  $stmt = $pdo->prepare(
    "INSERT INTO settings (nome, valore)
     VALUES (?, ?)
     ON DUPLICATE KEY UPDATE valore = VALUES(valore)"
  );
  $stmt->execute([$key, $value]);

  $msg = 'Impostazione aggiornata';
}

/* ===============================
   UPLOAD FILE (LOGO / FAVICON)
================================ */
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  isset($_POST['upload']) &&
  isset($_FILES['file'])
) {
  $key = $_POST['upload'];
  $allowed = $key === 'favicon'
    ? ['ico','png','svg']
    : ['png','jpg','jpeg','webp','svg'];

  $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

  if (!in_array($ext, $allowed, true)) {
    $msg = 'Formato file non valido';
  } else {

    $file = $key . '.' . $ext;
    $destDir = __DIR__ . '/../assets/img/';
    $destPath = $destDir . $file;

    if (!is_dir($destDir)) {
      mkdir($destDir, 0755, true);
    }

    if (move_uploaded_file($_FILES['file']['tmp_name'], $destPath)) {

      $stmt = $pdo->prepare(
        "INSERT INTO settings (nome, valore)
         VALUES (?, ?)
         ON DUPLICATE KEY UPDATE valore = VALUES(valore)"
      );
      $stmt->execute([$key, $file]);

      $msg = ucfirst($key) . ' aggiornato';
    }
  }
}

$titolo = 'Impostazioni ambiente';
require __DIR__ . '/../themes/semplice/header.php';
?>

<h2>⚙️ Impostazioni ambiente</h2>

<?php if ($msg): ?>
  <p style="color:green"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<table class="settings-table">
  <tr>
    <th>Descrizione</th>
    <th>Campo</th>
    <th>Azione</th>
  </tr>

  <!-- NOME SITO -->
  <tr>
    <td>Nome del sito</td>
    <td>
      <form method="post">
        <input type="hidden" name="key" value="site_name">
        <input type="text" name="value"
          value="<?= htmlspecialchars($SETTINGS['site_name'] ?? '') ?>">
    </td>
    <td>
        <button type="submit">💾 Salva</button>
      </form>
    </td>
  </tr>

  <!-- LOGO -->
  <tr>
    <td>Logo sito</td>
<td>
  <form method="post" enctype="multipart/form-data" class="inline-form">
    <input type="hidden" name="upload" value="logo">

    <div class="field-inline">
      <input type="file" name="file" accept="image/*">

      <?php if (!empty($SETTINGS['logo'])): ?>
        <img
          src="<?= BASE_URL ?>/assets/img/<?= htmlspecialchars($SETTINGS['logo']) ?>"
          class="preview preview-logo"
          alt="Logo"
        >
      <?php endif; ?>
    </div>
</td>
    <td>
        <button type="submit">💾 Carica</button>
      </form>
    </td>
  </tr>

  <!-- FAVICON -->
  <tr>
    <td>Favicon</td>
<td>
  <form method="post" enctype="multipart/form-data" class="inline-form">
    <input type="hidden" name="upload" value="favicon">

    <div class="field-inline">
      <input type="file" name="file" accept=".ico,.png,.svg">

      <?php if (!empty($SETTINGS['favicon'])): ?>
        <img
          src="<?= BASE_URL ?>/assets/img/<?= htmlspecialchars($SETTINGS['favicon']) ?>"
          class="preview preview-favicon"
          alt="Favicon"
        >
      <?php endif; ?>
    </div>
</td>
    <td>
        <button type="submit">💾 Carica</button>
      </form>
    </td>
  </tr>

  <!-- FINE GIORNATA -->
  <tr>
    <td>Fine giornata (orario)</td>
    <td>
      <form method="post">
        <input type="hidden" name="key" value="session_end_hour">
        <input type="time" name="value"
          value="<?= htmlspecialchars($SETTINGS['session_end_hour'] ?? '03:00') ?>">
    </td>
    <td>
        <button type="submit">💾 Salva</button>
      </form>
    </td>
  </tr>

</table>

<style>
.settings-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}

.settings-table th,
.settings-table td {
  border-bottom: 1px solid #ddd;
  padding: 10px;
  vertical-align: middle;
}

.settings-table th {
  text-align: left;
  background: #f5f5f5;
}

.settings-table form {
  margin: 0;
}

.settings-table button {
  padding: 6px 10px;
}
/* ===============================
   SETTINGS – ANTEPRIME COMPATTE
================================ */
.settings-table td {
  vertical-align: middle;
}

/* contenitore inline */
.field-inline {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* anteprime immagini */
.preview {
  max-height: 28px;      /* altezza riga */
  width: auto;
  object-fit: contain;
  padding: 0;
  margin: 0;
  background: transparent;
}

/* specifiche (se servono differenze) */
.preview-logo {
  max-height: 28px;
}

.preview-favicon {
  max-height: 20px;
}
/* ===============================
   PREVIEW – ZOOM ON HOVER
================================ */
.preview {
  max-height: 28px;
  width: auto;
  object-fit: contain;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  transform-origin: center center;
  cursor: zoom-in;
  z-index: 1;
}

/* zoom vero e proprio */
.preview:hover {
  transform: scale(2.2);
  z-index: 999;
  background: #fff;
  padding: 6px;
  border-radius: 8px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.25);
}

/* favicon leggermente meno zoom */
.preview-favicon:hover {
  transform: scale(3);
}
</style>

<?php
require __DIR__ . '/../themes/semplice/footer.php';