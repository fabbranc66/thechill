<?php
declare(strict_types=1);

/* ==========================================================
   MODULO GESTIONE MODULI
   VIEW
========================================================== */

// esegue la logica
require __DIR__ . '/actions.php';

/* ==========================================================
   TEMPLATE HEADER
========================================================== */
$titolo = 'Gestione moduli';
require ROOT_PATH . '/themes/semplice/header.php';

/* ==========================================================
   CARICAMENTO LISTA MODULI
========================================================== */
$available = $moduleManager->getAvailableModules();
$installed = $moduleManager->getInstalledModules();
?>

<h2>Gestione moduli</h2>

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
     TABELLA MODULI
======================================================= -->
<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Modulo</th>
        <th>Versione</th>
        <th>Stato</th>
        <th>Azioni</th>
    </tr>

<?php foreach ($available as $name => $mod): 
    $isInstalled = isset($installed[$name]);
    $isActive = $installed[$name] ?? false;
?>
<tr>
    <td><?= htmlspecialchars($mod['label']) ?></td>
    <td><?= htmlspecialchars($mod['version']) ?></td>
    <td>
        <?php if (!$isInstalled): ?>
            Non installato
        <?php elseif ($isActive): ?>
            Attivo
        <?php else: ?>
            Disattivato
        <?php endif; ?>
    </td>
    <td>

        <!-- INSTALLA -->
        <?php if (!$isInstalled): ?>
            <a href="?mod=moduli&installa=<?= $name ?>">Installa</a>

        <?php else: ?>

            <!-- ATTIVA / DISATTIVA -->
            <?php if ($isActive): ?>
                <a href="?mod=moduli&disattiva=<?= $name ?>">Disattiva</a>
            <?php else: ?>
                <a href="?mod=moduli&attiva=<?= $name ?>">Attiva</a>
            <?php endif; ?>

            |
            <!-- DISINSTALLA -->
            <a href="?mod=moduli&disinstalla=<?= $name ?>"
               onclick="return confirm('Disinstallare il modulo?')">
               Disinstalla
            </a>

        <?php endif; ?>

    </td>
</tr>
<?php endforeach; ?>

</table>

<?php
/* ==========================================================
   TEMPLATE FOOTER
========================================================== */
require ROOT_PATH . '/themes/semplice/footer.php';
