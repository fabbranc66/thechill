<?php
// =====================================================
// 1. BOOTSTRAP APPLICAZIONE E CONTROLLO ACCESSI
// Inizializza l’ambiente, carica le dipendenze e
// verifica il ruolo amministratore
// =====================================================

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../lib/phpqrcode/qrlib.php';

richiedi_ruolo('amministratore');


// =====================================================
// 2. VALIDAZIONE INPUT HTTP
// Controlla che l’ID carta sia presente e numerico
// =====================================================

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    echo 'ID CARTA NON VALIDO';
    exit;
}

$carta_id = (int) $_GET['id'];


// =====================================================
// 3. RECUPERO DATI DAL DATABASE
// Legge il codice carta associato all’ID richiesto
// =====================================================

$stmt = $pdo->prepare("
    SELECT codice_carta
    FROM carte_fedelta
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$carta_id]);

$carta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$carta) {
    echo 'CARTA NON TROVATA';
    exit;
}


// =====================================================
// 4. NORMALIZZAZIONE CODICE CARTA
// Pulisce il valore letto dal database
// =====================================================

$codice = trim($carta['codice_carta']);


// =====================================================
// 5. COSTRUZIONE URL DI SCANSIONE
// Genera l’URL assoluto da codificare nel QR
// =====================================================

$qrUrl = BASE_URL_FULL . '/scan.php?qr=' . urlencode($codice);


// =====================================================
// 6. PREPARAZIONE CACHE FILESYSTEM
// Verifica esistenza e permessi della cartella cache
// =====================================================

$cacheFs = __DIR__ . '/../lib/phpqrcode/cache';

if (!is_dir($cacheFs) || !is_writable($cacheFs)) {
    echo 'CACHE QR NON SCRIVIBILE';
    exit;
}


// =====================================================
// 7. DETERMINAZIONE NOME FILE QR
// Nome deterministico per caching e riuso
// =====================================================

$fileName = 'qr_' . md5($codice) . '.png';
$filePath = $cacheFs . '/' . $fileName;


// =====================================================
// 8. GENERAZIONE IMMAGINE QR
// Crea il PNG tramite phpqrcode
// =====================================================

QRcode::png($qrUrl, $filePath, QR_ECLEVEL_L, 6);


// =====================================================
// 9. ESPOSIZIONE URL PUBBLICO IMMAGINE
// URL HTTP per rendering del QR
// =====================================================

$imgUrl = BASE_URL_FULL . '/lib/phpqrcode/cache/' . $fileName;


// =====================================================
// 10. OUTPUT HTML DI DIAGNOSTICA VISIVA (NO THEME)
// Pagina minimale per verifica immediata
// =====================================================
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>DEBUG QR</title>
    <style>
        body { font-family: monospace; padding: 20px }
        .box { border: 1px solid #ccc; padding: 10px; margin-bottom: 15px }
    </style>
</head>
<body>

<h1>DEBUG GENERAZIONE QR</h1>

<div class="box">
<strong>URL SCAN:</strong><br>
<?= htmlspecialchars($qrUrl) ?>
</div>

<div class="box">
<strong>IMG SRC:</strong><br>
<?= htmlspecialchars($imgUrl) ?>
</div>

<img src="<?= htmlspecialchars($imgUrl) ?>" alt="QR">

</body>
</html>
