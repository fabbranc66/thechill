<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

richiedi_ruolo('amministratore');

$titolo = 'Gestione gallery';
require __DIR__ . '/../themes/semplice/header.php';

$errore = null;

/* ==========================================================
   FILE DI SISTEMA (assets/img)
========================================================== */
$assetDir = __DIR__ . '/../assets/gallery';
$assetFiles = [];

if (is_dir($assetDir)) {
  foreach (scandir($assetDir) as $f) {
    if ($f === '.' || $f === '..') continue;
    if (is_file($assetDir . '/' . $f)) {
      $assetFiles[] = $f;
    }
  }
}

/* ==========================================================
   INSERIMENTO
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $tipo = strtolower($_POST['tipo'] ?? 'image');
  $sezione = $_POST['sezione'] ?? 'foto';

  if (!in_array($tipo, ['image','video','instagram','youtube'], true)) {
    $tipo = 'image';
  }

  if (!in_array($sezione, ['video_loop','foto','instagram','youtube'], true)) {
    $sezione = 'foto';
  }

  $url = trim($_POST['url'] ?? '');
  $existing = trim($_POST['existing_file'] ?? '');

  /* ===== INSTAGRAM / YOUTUBE ===== */
  if (in_array($tipo, ['instagram','youtube'], true)) {

    if ($url === '') {
      $errore = 'URL mancante';
    } else {

      if ($tipo === 'instagram') {
        $url = strtok($url, '?');
      }

      if ($tipo === 'youtube') {
        if (preg_match('~(v=|youtu\.be/|shorts/)([^&?/]+)~', $url, $m)) {
          $url = 'https://www.youtube-nocookie.com/embed/' . $m[2];
        } else {
          $errore = 'URL YouTube non valido';
        }
      }

      if (!$errore) {
        $stmt = $pdo->prepare(
          "INSERT INTO gallery_eventi (tipo, sezione, file, url, attivo)
           VALUES (?, ?, NULL, ?, 1)"
        );
        $stmt->execute([$tipo, $sezione, $url]);
      }
    }

  } else {

    /* ===== IMAGE / VIDEO ===== */
    if ($existing !== '') {

      // file di sistema
      $stmt = $pdo->prepare(
        "INSERT INTO gallery_eventi (tipo, sezione, file, attivo)
         VALUES (?, ?, ?, 1)"
      );
      $stmt->execute([$tipo, $sezione, 'img/' . $existing]);

    } elseif (
      !empty($_FILES['file']['tmp_name']) &&
      is_uploaded_file($_FILES['file']['tmp_name'])
    ) {

      $original = basename($_FILES['file']['name']);
      $file = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original);

      move_uploaded_file(
        $_FILES['file']['tmp_name'],
        __DIR__ . '/../assets/gallery/' . $file
      );

      $stmt = $pdo->prepare(
        "INSERT INTO gallery_eventi (tipo, sezione, file, attivo)
         VALUES (?, ?, ?, 1)"
      );
      $stmt->execute([$tipo, $sezione, 'gallery/' . $file]);

    } else {
      $errore = 'Seleziona un file o caricane uno nuovo';
    }
  }
}

/* ==========================================================
   TOGGLE VISIBILITÀ
========================================================== */
if (isset($_GET['toggle'])) {
  $pdo->prepare(
    "UPDATE gallery_eventi SET attivo = 1 - attivo WHERE id = ?"
  )->execute([(int)$_GET['toggle']]);

  header('Location: gallery.php');
  exit;
}
?>

<h2>🖼️ Gallery</h2>

<?php if ($errore): ?>
  <div style="color:red;margin-bottom:10px">
    <?= htmlspecialchars($errore) ?>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

  <select name="tipo" id="tipo" onchange="toggleFields()">
    <option value="image">Immagine</option>
    <option value="video">Video MP4</option>
    <option value="instagram">Instagram</option>
    <option value="youtube">YouTube</option>
  </select>

  <select name="sezione">
    <option value="video_loop">Video loop</option>
    <option value="foto">Foto</option>
    <option value="instagram">Instagram</option>
    <option value="youtube">YouTube</option>
  </select>

  <div id="file-field">
    <input type="file" name="file">
  </div>

  <div id="existing-field">
    <select name="existing_file">
      <option value="">— seleziona da Galleria —</option>
      <?php foreach ($assetFiles as $f): ?>
        <option value="<?= htmlspecialchars($f) ?>">
          <?= htmlspecialchars($f) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div id="url-field" style="display:none">
    <input type="url" name="url" placeholder="URL">
  </div>

  <button>Salva</button>
</form>

<script>
function toggleFields() {
  const t = document.getElementById('tipo').value;
  const isUrl = (t === 'instagram' || t === 'youtube');

  document.getElementById('file-field').style.display = isUrl ? 'none' : 'block';
  document.getElementById('existing-field').style.display = isUrl ? 'none' : 'block';
  document.getElementById('url-field').style.display = isUrl ? 'block' : 'none';
}
toggleFields();
</script>

<?php
require __DIR__ . '/../themes/semplice/footer.php';