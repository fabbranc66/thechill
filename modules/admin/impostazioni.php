<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

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

  $SETTINGS[$key] = $value;
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
    $destDir = ROOT_PATH . '/public/assets/img/';
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

      $SETTINGS[$key] = $file;
      $msg = ucfirst($key) . ' aggiornato';
    }
  }
}

$titolo = 'Impostazioni ambiente';
require ROOT_PATH . '/themes/semplice/header.php';

/* ===============================
   MAPPA TAVOLI
================================ */
$mappa = $SETTINGS['tavoli_mappa'] ?? '';
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

  <!-- SCANNER DA DESKTOP -->
  <tr>
    <td>Scanner da PC/Tablet</td>
    <td>
      <form method="post">
        <input type="hidden" name="key" value="scanner_desktop">
        <select name="value">
          <option value="0"
            <?= (($SETTINGS['scanner_desktop'] ?? '0') === '0') ? 'selected' : '' ?>>
            Solo da cellulare
          </option>
          <option value="1"
            <?= (($SETTINGS['scanner_desktop'] ?? '0') === '1') ? 'selected' : '' ?>>
            Abilitato anche su PC/Tablet
          </option>
        </select>
    </td>
    <td>
        <button type="submit">💾 Salva</button>
      </form>
    </td>
  </tr>

</table>
<?php
/* ===============================
   ALTRE IMPOSTAZIONI DINAMICHE
================================ */
$stmt = $pdo->query(
  "SELECT nome, valore
   FROM settings
   WHERE nome NOT IN ('site_name','logo','favicon','scanner_desktop','tavoli_mappa')
   ORDER BY nome"
);
$altre = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($altre): ?>
<br>
<h3>Altre impostazioni</h3>

<table class="settings-table">
  <tr>
    <th>Chiave</th>
    <th>Valore</th>
    <th>Azione</th>
  </tr>

  <?php foreach ($altre as $s): ?>
    <tr>
      <td><?= htmlspecialchars($s['nome']) ?></td>
      <td>
        <form method="post">
          <input type="hidden" name="key" value="<?= htmlspecialchars($s['nome']) ?>">
          <input type="text" name="value"
                 value="<?= htmlspecialchars($s['valore']) ?>">
      </td>
      <td>
          <button type="submit">💾 Salva</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<!-- ======================================================
     EDITOR GRAFICO MAPPA TAVOLI
====================================================== -->

<h3 style="margin-top:30px">🪑 Mappa tavoli (drag & drop)</h3>

<div id="editor-mappa"></div>

<form method="post" style="margin-top:15px">
  <input type="hidden" name="key" value="tavoli_mappa">
  <textarea id="mappa-json" name="value"
    style="width:100%;height:120px;font-family:monospace"></textarea>
  <button type="submit" style="margin-top:10px">💾 Salva mappa</button>
</form>

<style>
#editor-mappa {
  position: relative;
  width: 100%;
  max-width: 700px;
  height: 420px;
  border: 2px dashed #ccc;
  background:
    repeating-linear-gradient(
      0deg,
      #f9f9f9,
      #f9f9f9 68px,
      #eaeaea 69px,
      #eaeaea 70px
    ),
    repeating-linear-gradient(
      90deg,
      #f9f9f9,
      #f9f9f9 68px,
      #eaeaea 69px,
      #eaeaea 70px
    );
  margin: 10px 0;
  border-radius: 10px;
}

.tavolo-box {
  position: absolute;
  width: 60px;
  height: 60px;
  background: #0b3d2e;
  color: #fff;
  border-radius: 10px;
  text-align: center;
  line-height: 60px;
  font-weight: bold;
  cursor: grab;
  user-select: none;
}
</style>

<script>
const GRID = 70;
const OFFSET = 10;

const mappa = <?= $mappa ?: '{}' ?>;
const editor = document.getElementById('editor-mappa');

function creaTavolo(id, x, y) {
  const el = document.createElement('div');
  el.className = 'tavolo-box';
  el.textContent = id;
  el.dataset.id = id;
  setPos(el, x, y);

  let offsetX, offsetY, dragging = false;

  el.addEventListener('mousedown', e => {
    dragging = true;
    offsetX = e.offsetX;
    offsetY = e.offsetY;
    el.style.cursor = 'grabbing';
  });

  document.addEventListener('mouseup', () => {
    if (!dragging) return;
    dragging = false;
    el.style.cursor = 'grab';

    const x = Math.round((el.offsetLeft - OFFSET) / GRID);
    const y = Math.round((el.offsetTop - OFFSET) / GRID);
    setPos(el, x, y);

    salvaJson();
  });

  document.addEventListener('mousemove', e => {
    if (!dragging) return;

    const rect = editor.getBoundingClientRect();
    let x = e.clientX - rect.left - offsetX;
    let y = e.clientY - rect.top - offsetY;

    el.style.left = x + 'px';
    el.style.top  = y + 'px';
  });

  editor.appendChild(el);
}

function setPos(el, x, y) {
  el.style.left = (x * GRID + OFFSET) + 'px';
  el.style.top  = (y * GRID + OFFSET) + 'px';
}

function salvaJson() {
  const tavoli = {};
  document.querySelectorAll('.tavolo-box').forEach(el => {
    const id = el.dataset.id;
    const x = Math.round((el.offsetLeft - OFFSET) / GRID);
    const y = Math.round((el.offsetTop - OFFSET) / GRID);
    tavoli[id] = {x, y};
  });

  document.getElementById('mappa-json').value =
    JSON.stringify(tavoli, null, 2);
}

/* inizializzazione */
if (Object.keys(mappa).length === 0) {
  for (let i = 1; i <= 20; i++) {
    const x = (i - 1) % 5;
    const y = Math.floor((i - 1) / 5);
    creaTavolo(i, x, y);
  }
} else {
  for (let id in mappa) {
    creaTavolo(id, mappa[id].x, mappa[id].y);
  }
}

salvaJson();
</script>

<?php
require ROOT_PATH . '/themes/semplice/footer.php';
