<?php
declare(strict_types=1);

/* ==========================================================
   MODULO PREMI - VIEW
========================================================== */

richiedi_ruolo('amministratore');

/* ==========================================================
   ROUTER INTERNO
========================================================== */

/* RISCATTO PREMIO */
if (isset($_GET['riscatto'])) {
    require __DIR__ . '/riscatto.php';
    return;
}

/* ==========================================================
   LOGICA STANDARD
========================================================== */
require __DIR__ . '/actions.php';
require __DIR__ . '/query.php';

$premi = premi_lista($pdo);

$titolo = 'Gestione Premi';
require ROOT_PATH . '/themes/semplice/header.php';
?>

<h2>Gestione Premi</h2>

<?php if (!empty($messaggio)): ?>
<div style="color:green;margin-bottom:15px">
    <?= htmlspecialchars($messaggio) ?>
</div>
<?php endif; ?>

<?php if (!empty($errore)): ?>
<div style="color:#900;margin-bottom:15px">
    <?= htmlspecialchars($errore) ?>
</div>
<?php endif; ?>

<!-- =======================================================
     FORM PREMIO
======================================================= -->
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
       value="<?= (int)($edit['punti_necessari'] ?? 0) ?>"><br><br>

<label>
<input type="checkbox" name="attivo"
       <?= (!isset($edit) || ($edit['attivo'] ?? 0)) ? 'checked' : '' ?>>
 Attivo
</label><br><br>

<button type="submit" name="salva">💾 Salva</button>

<?php if ($edit): ?>
<a href="<?= BASE_URL ?>/?mod=premi" style="margin-left:10px">Annulla</a>
<?php endif; ?>
</form>
</div>

<!-- =======================================================
     LISTA PREMI
======================================================= -->
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

<a href="<?= BASE_URL ?>/?mod=premi&edit=<?= $p['id'] ?>"
   class="btn-edit"
   style="background:none;border:none;cursor:pointer;padding:4px">
   ✏️
</a>

<form method="post" style="display:inline"
      onsubmit="return confirm('Eliminare il premio?')">
<input type="hidden" name="elimina" value="<?= $p['id'] ?>">
<button class="btn-delete"
        style="background:none;border:none;cursor:pointer;padding:4px">
    🗑
</button>
</form>

</td>
</tr>
<?php endforeach; ?>
</table>

<style>
table { width:100%; border-collapse:collapse; }
td,th { border:1px solid #ccc; padding:6px; }
button { border:0; background:none; cursor:pointer; }
input[type=text],
input[type=number] { padding:6px; }
</style>

<?php
require ROOT_PATH . '/themes/semplice/footer.php';
