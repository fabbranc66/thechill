<?php
declare(strict_types=1);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

richiedi_ruolo('amministratore');

/* -------------------------
   ELENCO UTENTI CLIENTI
------------------------- */
$stmt = $pdo->query(
    "SELECT u.id,
            u.nome,
            u.email,
            u.telefono,
            COUNT(c.id) AS num_carte
     FROM utenti u
     LEFT JOIN carte_fedelta c ON c.utente_id = u.id
     WHERE u.ruolo = 'cliente'
     GROUP BY u.id
     ORDER BY u.nome"
);

$utenti = $stmt->fetchAll();

$titolo = 'Clienti';
require __DIR__ . '/../themes/semplice/header.php';
?>

<h2>Lista clienti</h2>

<table border="1" cellpadding="6" cellspacing="0" width="100%">
<tr>
    <th>Nome</th>
    <th>Email</th>
    <th>Telefono</th>
    <th>Carte</th>
    <th>Azioni</th>
</tr>

<?php if ($utenti): ?>
    <?php foreach ($utenti as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['nome']) ?></td>
            <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['telefono'] ?? '') ?></td>
            <td style="text-align:center"><?= (int)$u['num_carte'] ?></td>
            <td style="white-space:nowrap">
                <a href="carte_modifica.php?id=<?= $u['id'] ?>"
                   class="btn small">
                    ✏️ Modifica
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="5">Nessun cliente presente</td>
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
</style>

<?php
require __DIR__ . '/../themes/semplice/footer.php';