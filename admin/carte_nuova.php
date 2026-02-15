<?php
declare(strict_types=1);

/* ==========================================================
   FILE: admin/carte_nuova.php
   SCOPO:
   - creare una nuova carta fedeltà
   - associarla a un cliente esistente
     oppure creare un nuovo cliente
   - generare il codice carta univoco
   - generare il QR code collegato alla scansione
   ========================================================== */

/* ==========================================================
   INIZIALIZZAZIONE
   - init.php: config, sessione, DB ($pdo), costanti URL
   - auth.php: funzioni di sicurezza
   - qrlib: libreria per generare QR code
   ========================================================== */
require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../lib/phpqrcode/qrlib.php';

/* ==========================================================
   SICUREZZA
   - accesso consentito SOLO agli amministratori
   ========================================================== */
richiedi_ruolo('amministratore');

/* ==========================================================
   VARIABILI
   - $errore: messaggio mostrato a video in caso di problemi
   ========================================================== */
$errore = null;

/* ==========================================================
   CARICAMENTO CLIENTI ESISTENTI
   - usati per il menu a tendina
   - solo utenti con ruolo "cliente"
   ========================================================== */
$clienti = $pdo->query(
  "SELECT id, nome, email
   FROM utenti
   WHERE ruolo = 'cliente'
   ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

/* ==========================================================
   GESTIONE INVIO FORM (CREAZIONE CARTA)
   ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  /* --------------------------------------------------------
     INPUT FORM
     - cliente_id: cliente già esistente (0 = nuovo)
     - nome/email/telefono: usati solo se nuovo cliente
     -------------------------------------------------------- */
  $cliente_id = (int)($_POST['cliente_id'] ?? 0);
  $nome       = trim($_POST['nome'] ?? '');
  $email      = trim($_POST['email'] ?? '');
  $telefono   = trim($_POST['telefono'] ?? '');

  /* --------------------------------------------------------
     VALIDAZIONE MINIMA
     - o selezioni un cliente
     - o inserisci almeno un nome
     -------------------------------------------------------- */
  if ($cliente_id === 0 && $nome === '') {
    $errore = 'Seleziona un cliente o inserisci un nuovo nome';
  } else {

    try {
      /* ----------------------------------------------------
         TRANSAZIONE
         - garantisce coerenza DB (tutto o niente)
         ---------------------------------------------------- */
      $pdo->beginTransaction();

      /* ----------------------------------------------------
         CREAZIONE NUOVO CLIENTE (SE NECESSARIO)
         ---------------------------------------------------- */
      if ($cliente_id === 0) {
        $stmt = $pdo->prepare(
          "INSERT INTO utenti (nome, email, telefono, ruolo)
           VALUES (?, ?, ?, 'cliente')"
        );
        $stmt->execute([
          $nome,
          $email ?: null,
          $telefono ?: null
        ]);

        $cliente_id = (int)$pdo->lastInsertId();
      }

      /* ----------------------------------------------------
         GENERAZIONE CODICE CARTA
         - stringa univoca
         - usata in tutto il sistema (QR, scansioni, WhatsApp)
         ---------------------------------------------------- */
      $codice_carta = 'CARD' . strtoupper(bin2hex(random_bytes(4)));

      /* ----------------------------------------------------
         INSERIMENTO CARTA FEDELTÀ
         ---------------------------------------------------- */
      $stmt = $pdo->prepare(
        "INSERT INTO carte_fedelta (utente_id, codice_carta, punti)
         VALUES (?, ?, 0)"
      );
      $stmt->execute([$cliente_id, $codice_carta]);
      $carta_id = (int)$pdo->lastInsertId();

      /* ----------------------------------------------------
         GENERAZIONE QR CODE
         - il QR punta a /admin/scan.php
         - include il parametro qr=
         ---------------------------------------------------- */
      $qrUrl = BASE_URL_FULL
        . '/scan.php?qr='
        . urlencode($codice_carta);

      /* ----------------------------------------------------
         CARTELLA CACHE QR
         - deve esistere
         - deve essere scrivibile
         ---------------------------------------------------- */
      $cacheDir = __DIR__ . '/../lib/phpqrcode/cache';
      if (!is_dir($cacheDir) || !is_writable($cacheDir)) {
        throw new RuntimeException('Cache QR non scrivibile');
      }

      /* ----------------------------------------------------
         FILE QR
         - nome basato su hash del codice carta
         ---------------------------------------------------- */
      $fileName = 'qr_' . md5($codice_carta) . '.png';
      $filePath = $cacheDir . '/' . $fileName;

      QRcode::png($qrUrl, $filePath, QR_ECLEVEL_L, 6);

      /* ----------------------------------------------------
         COMMIT TRANSAZIONE
         ---------------------------------------------------- */
      $pdo->commit();

      /* ----------------------------------------------------
         REDIRECT ALLA PAGINA DI MODIFICA CARTA
         ---------------------------------------------------- */
      header('Location: carte_modifica.php?id=' . $carta_id);
      exit;

    } catch (Throwable $e) {
      /* ----------------------------------------------------
         ROLLBACK IN CASO DI ERRORE
         ---------------------------------------------------- */
      if ($pdo->inTransaction()) {
        $pdo->rollBack();
      }
      $errore = 'Errore durante la creazione della carta';
    }
  }
}

/* ==========================================================
   TEMPLATE
   ========================================================== */
$titolo = 'Nuova carta';
require __DIR__ . '/../themes/semplice/header.php';
?>

<h2>Nuova carta fedeltà</h2>

<?php if ($errore): ?>
  <div style="color:#900;margin-bottom:15px">
    <?= htmlspecialchars($errore) ?>
  </div>
<?php endif; ?>

<form method="post" style="max-width:500px">

  <!-- ======================================================
       CLIENTE ESISTENTE
       ====================================================== -->
  <h3>Cliente esistente</h3>

  <select name="cliente_id" style="width:100%;padding:8px">
    <option value="0">— Seleziona —</option>
    <?php foreach ($clienti as $c): ?>
      <option value="<?= $c['id'] ?>">
        <?= htmlspecialchars($c['nome']) ?>
        <?= $c['email'] ? ' ('.$c['email'].')' : '' ?>
      </option>
    <?php endforeach; ?>
  </select>

  <hr>

  <!-- ======================================================
       NUOVO CLIENTE
       ====================================================== -->
  <h3>Oppure nuovo cliente</h3>

  <label>Nome</label>
  <input name="nome" style="width:100%;padding:8px">

  <label>Email</label>
  <input name="email" type="email" style="width:100%;padding:8px">

  <label>Telefono</label>
  <input name="telefono" style="width:100%;padding:8px">

  <br><br>

  <button>📇 Crea carta</button>
</form>

<?php require __DIR__ . '/../themes/semplice/footer.php'; ?>