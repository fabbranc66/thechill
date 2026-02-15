<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

require __DIR__ . '/query.php';
require __DIR__ . '/actions.php';

$id = (int)($_GET['id'] ?? 0);

$cliente = [
    'id' => 0,
    'nome' => '',
    'email' => '',
    'telefono' => ''
];

if ($id > 0) {
    $res = clienti_by_id($pdo, $id);
    if ($res) {
        $cliente = $res;
    }
}

$titolo = $id > 0 ? 'Modifica cliente' : 'Nuovo cliente';
require ROOT_PATH . '/themes/semplice/header.php';
?>

<h2><?= $titolo ?></h2>

<form method="post" style="max-width:500px">

<?php if ($id > 0): ?>
    <input type="hidden" name="mod_cliente" value="1">
    <input type="hidden" name="id" value="<?= $cliente['id'] ?>">
<?php else: ?>
    <input type="hidden" name="crea_cliente" value="1">
<?php endif; ?>

<label>Nome</label>
<input name="nome"
       value="<?= htmlspecialchars($cliente['nome']) ?>"
       required
       style="width:100%;padding:8px">

<label>Email</label>
<input name="email"
       value="<?= htmlspecialchars($cliente['email']) ?>"
       style="width:100%;padding:8px">

<label>Telefono</label>
<input name="telefono"
       value="<?= htmlspecialchars($cliente['telefono']) ?>"
       style="width:100%;padding:8px">

<br><br>
<button>💾 Salva</button>
<a href="<?= BASE_URL ?>/?mod=admin&tab=clienti">Annulla</a>

</form>

<?php
require ROOT_PATH . '/themes/semplice/footer.php';
