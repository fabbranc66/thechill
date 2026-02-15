<?php
declare(strict_types=1);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

richiedi_ruolo('amministratore');

/* -------------------------
   CANCELLAZIONE CARTA
------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancella_id'])) {

    $carta_id = (int)$_POST['cancella_id'];

    if ($carta_id > 0) {
        try {
            $pdo->beginTransaction();

            // elimina log scansioni
            $stmt = $pdo->prepare(
                "DELETE FROM log_scansioni WHERE carta_id = ?"
            );
            $stmt->execute([$carta_id]);

            // elimina carta
            $stmt = $pdo->prepare(
                "DELETE FROM carte_fedelta WHERE id = ?"
            );
            $stmt->execute([$carta_id]);

            $pdo->commit();

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }

    header('Location: carte.php');
    exit;
}

/* -------------------------
   ELENCO CARTE
------------------------- */
$stmt = $pdo->query(
    "SELECT c.id,
            c.codice_carta,
            c.punti,
            u.id AS utente_id,
            u.nome,
            u.email,
            u.telefono
     FROM carte_fedelta c
     JOIN utenti u ON u.id = c.utente_id
     ORDER BY u.nome"
);
$carte = $stmt->fetchAll();

$titolo = 'Lista carte';
require __DIR__ . '/../themes/semplice/header.php';
?>

<h2>Lista carte fedeltà</h2>

<table border="1" cellpadding="6" cellspacing="0" width="100%">
<tr>
    <th>Cliente</th>
    <th>Email</th>
    <th>Telefono</th>
    <th>Codice carta</th>
    <th>Punti</th>
    <th>Azioni</th>
</tr>

<?php if ($carte): ?>
    <?php foreach ($carte as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['nome']) ?></td>
            <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($c['telefono'] ?? '') ?></td>
            <td><?= htmlspecialchars($c['codice_carta']) ?></td>
            <td><?= (int)$c['punti'] ?></td>
            <td style="white-space:nowrap">

                <a href="carte_modifica.php?id=<?= $c['utente_id'] ?>"
                   class="btn small">
                    ✏️ Modifica
                </a>

                <form method="post"
                      style="display:inline"
                      onsubmit="return confirm('Cancellare definitivamente la carta?')">

                    <input type="hidden"
                           name="cancella_id"
                           value="<?= $c['id'] ?>">

                    <button class="btn small danger">
                        🗑 Cancella
                    </button>
                </form>

            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6">Nessuna carta presente</td>
    </tr>
<?php endif; ?>
</table>

<style>
.btn{
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
    border:none;
    cursor:pointer;
    font-size:13px;
    font-weight:bold;
}

.btn.small{
    background:#e0e0e0;
    color:#333;
}

.btn.small:hover{
    background:#d0d0d0;
}

.btn.danger{
    background:#c0392b;
    color:#fff;
}

.btn.danger:hover{
    background:#a93226;
}
</style>

<?php
require __DIR__ . '/../themes/semplice/footer.php';