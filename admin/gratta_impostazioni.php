<?php
// =====================================================
// 1. BOOTSTRAP APPLICAZIONE E CONTROLLO ACCESSI
// Inizializza ambiente, DB, sessione e verifica ruolo
// =====================================================

declare(strict_types=1);

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

richiedi_ruolo('amministratore');


// =====================================================
// 2. GESTIONE SALVATAGGIO IMPOSTAZIONI (POST)
// Valida input, normalizza valori e salva su DB
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $attivo = isset($_POST['attivo']) ? '1' : '0';
    $prob   = max(1, (int) ($_POST['probabilita'] ?? 1));
    $premio = max(0, (int) ($_POST['premio'] ?? 0));

    $stmt = $pdo->prepare("
        INSERT INTO impostazioni (nome, valore)
        VALUES (:nome, :valore)
        ON DUPLICATE KEY UPDATE valore = VALUES(valore)
    ");

    $stmt->execute(['nome' => 'gratta_attivo',          'valore' => $attivo]);
    $stmt->execute(['nome' => 'gratta_probabilita',     'valore' => $prob]);
    $stmt->execute(['nome' => 'gratta_premio_punti',    'valore' => $premio]);

    header('Location: gratta_impostazioni.php?ok=1');
    exit;
}


// =====================================================
// 3. LETTURA IMPOSTAZIONI DAL DATABASE
// Recupera i valori correnti per il rendering del form
// =====================================================

$val = $pdo->query(
    "SELECT nome, valore
     FROM settingss
     WHERE nome IN (
        'gratta_attivo',
        'gratta_probabilita',
        'gratta_premio_punti'
     )"
)->fetchAll(PDO::FETCH_KEY_PAIR);


// =====================================================
// 4. NORMALIZZAZIONE VALORI DI DEFAULT
// Imposta fallback sicuri se le chiavi non esistono
// =====================================================

$attivo = ($val['gratta_attivo'] ?? '1') === '1';
$prob   = (int) ($val['gratta_probabilita'] ?? 5);
$premio = (int) ($val['gratta_premio_punti'] ?? 10);


// =====================================================
// 5. HEADER TEMA
// Imposta titolo pagina e carica layout
// =====================================================

$titolo = 'Impostazioni Gratta e Vinci';
require __DIR__ . '/../themes/semplice/header.php';
?>

<!-- =====================================================
     6. OUTPUT PAGINA HTML
     Form di configurazione lato amministratore
     ===================================================== -->

<h2>🎁 Gratta e Vinci – Impostazioni</h2>

<?php if (isset($_GET['ok'])): ?>
<div class="alert-success">Impostazioni salvate correttamente.</div>
<?php endif; ?>

<form method="post" class="box" style="max-width:420px">

    <label>
        <input type="checkbox" name="attivo" <?= $attivo ? 'checked' : '' ?>>
        Gratta e Vinci attivo
    </label>

    <hr>

    <label>
        Probabilità di vincita<br>
        <small>1 vincita ogni N gratta</small><br>
        <input type="number" name="probabilita" min="1" value="<?= $prob ?>" required>
    </label>

    <br><br>

    <label>
        Premio (punti)<br>
        <input type="number" name="premio" min="0" value="<?= $premio ?>" required>
    </label>

    <br><br>

    <button class="btn-nuova-carta">💾 Salva impostazioni</button>

</form>

<?php
// =====================================================
// 7. FOOTER TEMA
// =====================================================

require __DIR__ . '/../themes/semplice/footer.php';
?>
