<?php
declare(strict_types=1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

richiedi_ruolo('amministratore');

/* =========================
   AZIONI POST
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CREA / MODIFICA
    if (isset($_POST['salva'])) {
        $id    = (int)($_POST['id'] ?? 0);
        $nome  = trim($_POST['nome'] ?? '');
        $punti = (int)($_POST['punti_necessari'] ?? 0);
        $attivo = isset($_POST['attivo']) ? 1 : 0;

        if ($nome !== '' && $punti > 0) {
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE premi
                     SET nome=?, punti_necessari=?, attivo=?
                     WHERE id=?"
                );
                $stmt->execute([$nome, $punti, $attivo, $id]);
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO premi (nome, punti_necessari, attivo)
                     VALUES (?,?,?)"
                );
                $stmt->execute([$nome, $punti, $attivo]);
            }
        }

        header('Location: premi.php');
        exit;
    }

    // ELIMINA
    if (isset($_POST['elimina'])) {
        $id = (int)$_POST['elimina'];
        $pdo->prepare("DELETE FROM premi WHERE id=?")->execute([$id]);
        header('Location: premi.php');
        exit;
    }
}

/* =========================
   MODIFICA
========================= */
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM premi WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

/* =========================
   LISTA PREMI
========================= */
$premi = $pdo->query(
    "SELECT *
     FROM premi
     ORDER BY punti_necessari ASC"
)->fetchAll();

$titolo = 'Gestione Premi';
require __DIR__ . '/../themes/semplice/header.php';
?>

<h2>Gestione Premi</h2>

<!-- FORM PREMIO -->
<div style="max-width:420px;margin-bottom:25px">
<h3><?= $edit ? 'Modifica premio' : 'Nuovo premio' ?></h3>

<form method="post">
<input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">

<label>Nome premio</label><br>
<input type="text" name="nome" required
       value="<?= htmlspecialchars($edit['nome'] ?? '') ?>"
       style="width:100%"><br><br>

<label>Punti necessari</label><br>
<input type="number" name="punti_necessari" min="1" required
       value="<?= (int)($edit['punti_necessari'] ?? '') ?>"><br><br>

<label>
<input type="checkbox" name="attivo"
       <?= (!isset($edit) || ($edit['attivo'] ?? 0)) ? 'checked' : '' ?>>
 Attivo
</label><br><br>

<button type="submit" name="salva">💾 Salva</button>

<?php if ($edit): ?>
    <a href="premi.php" style="margin-left:10px">Annulla</a>
<?php endif; ?>
</form>
</div>

<!-- LISTA -->
<h3>Premi esistenti</h3>
<table>
<tr>
    <th>Nome</th>
    <th>Punti</th>
    <th>Stato</th>
    <th>Azioni</th>
</tr>

<?php foreach ($premi as $p): ?>
<tr>
<td><?= htmlspecialchars($p['nome']) ?></td>
<td><?= (int)$p['punti_necessari'] ?></td>
<td><?= $p['attivo'] ? '🟢 Attivo' : '🔴 Disattivo' ?></td>
<td>
<a href="?edit=<?= $p['id'] ?>">✏️</a>
<form method="post" style="display:inline"
      onsubmit="return confirm('Eliminare il premio?')">
<input type="hidden" name="elimina" value="<?= $p['id'] ?>">
<button>🗑</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>

<style>
table{width:100%;border-collapse:collapse}
td,th{border:1px solid #ccc;padding:6px}
button{border:0;background:none;cursor:pointer}
input[type=text],input[type=number]{padding:6px}
</style>

<?php require __DIR__ . '/../themes/semplice/footer.php'; ?>