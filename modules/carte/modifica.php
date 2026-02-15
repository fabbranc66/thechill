<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
     exit('Carta non valida');
}

$stmt = $pdo->prepare(
     "SELECT
          c.id,
          c.punti,
          u.nome,
          u.email,
          u.telefono
      FROM carte_fedelta c
      JOIN utenti u ON u.id = c.utente_id
      WHERE c.id = ?"
);
$stmt->execute([$id]);

$carta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$carta) {
     exit('Carta non trovata');
}

$titolo = 'Modifica carta';
require ROOT_PATH . '/themes/semplice/header.php';
?>

<h2>Modifica carta</h2>

<form method="post" action="<?= BASE_URL ?>/?mod=carte" style="max-width:500px">

<input type="hidden" name="mod_carta" value="<?= (int)$carta['id'] ?>">

<label>Nome</label>
<input name="nome"
       value="<?= htmlspecialchars($carta['nome']) ?>"
       style="width:100%;padding:8px">

<label>Email</label>
<input name="email"
       value="<?= htmlspecialchars($carta['email']) ?>"
       style="width:100%;padding:8px">

<label>Telefono</label>
<input name="telefono"
       value="<?= htmlspecialchars($carta['telefono']) ?>"
       style="width:100%;padding:8px">

<label>Punti</label>
<input name="punti" type="number"
       value="<?= (int)$carta['punti'] ?>"
       style="width:100%;padding:8px">

<br><br>
<button>💾 Salva</button>

</form>

<?php
require ROOT_PATH . '/themes/semplice/footer.php';
