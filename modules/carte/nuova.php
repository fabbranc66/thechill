<?php
declare(strict_types=1);

richiedi_ruolo('amministratore');

$errore = null;

/* carica clienti */
$clienti = $pdo->query(
     "SELECT id, nome, email
      FROM utenti
      WHERE ruolo = 'cliente'
      ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

/* ==========================================================
     GESTIONE FORM
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

     $cliente_id = (int)($_POST['cliente_id'] ?? 0);
     $nome       = trim($_POST['nome'] ?? '');
     $email      = trim($_POST['email'] ?? '');
     $telefono   = trim($_POST['telefono'] ?? '');

     if ($cliente_id === 0 && $nome === '') {
          $errore = 'Seleziona un cliente o inserisci un nuovo nome';
     } else {

          try {
               $pdo->beginTransaction();

               /* nuovo cliente */
               if ($cliente_id === 0) {

                    $token = bin2hex(random_bytes(16));

                    $stmt = $pdo->prepare(
                         "INSERT INTO utenti
                          (nome, email, telefono, ruolo, token_accesso)
                          VALUES (?, ?, ?, 'cliente', ?)"
                    );
                    $stmt->execute([
                         $nome,
                         $email ?: null,
                         $telefono ?: null,
                         $token
                    ]);

                    $cliente_id = (int)$pdo->lastInsertId();

                    /* ==================================================
                       GENERAZIONE QR CLIENTE
                    ================================================== */

                    $lib = ROOT_PATH . '/lib/phpqrcode/qrlib.php';
                    $dir = ROOT_PATH . '/assets/qr/';

                    if (!file_exists($lib)) {
                         throw new Exception('Libreria QR non trovata: ' . $lib);
                    }

                    require_once $lib;

                    if (!is_dir($dir)) {
                         mkdir($dir, 0755, true);
                    }

                    if (!is_writable($dir)) {
                         throw new Exception('Cartella QR non scrivibile: ' . $dir);
                    }

                    $url = BASE_URL . '/modules/clienti/cliente.php?t=' . $token;
                    $file = $dir . $token . '.png';

                    QRcode::png($url, $file, QR_ECLEVEL_L, 6);
               }

               /* controlla carta esistente */
               $stmt = $pdo->prepare(
                    "SELECT id FROM carte_fedelta
                     WHERE utente_id = ?
                     LIMIT 1"
               );
               $stmt->execute([$cliente_id]);
               $carta = $stmt->fetch();

               if (!$carta) {

                    $codice = 'CARD' . strtoupper(bin2hex(random_bytes(4)));

                    $stmt = $pdo->prepare(
                         "INSERT INTO carte_fedelta
                          (utente_id, codice_carta, punti)
                          VALUES (?, ?, 0)"
                    );
                    $stmt->execute([$cliente_id, $codice]);
               }

               $pdo->commit();

               header('Location: ' . BASE_URL . '/?mod=admin&tab=carte');
               exit;

          } catch (Throwable $e) {
               if ($pdo->inTransaction()) {
                    $pdo->rollBack();
               }
               $errore = $e->getMessage();
          }
     }
}

$titolo = 'Nuova tessera';
require ROOT_PATH . '/themes/semplice/header.php';
?>

<h2>Nuova tessera cliente</h2>

<?php if ($errore): ?>
<div style="color:#900;margin-bottom:15px">
     <?= htmlspecialchars($errore) ?>
</div>
<?php endif; ?>

<form method="post" style="max-width:500px">

<h3>Cliente esistente</h3>
<select name="cliente_id" style="width:100%;padding:8px">
     <option value="0">— Seleziona —</option>
     <?php foreach ($clienti as $c): ?>
     <option value="<?= $c['id'] ?>">
          <?= htmlspecialchars($c['nome']) ?>
     </option>
     <?php endforeach; ?>
</select>

<hr>

<h3>Oppure nuovo cliente</h3>

<label>Nome</label>
<input name="nome" style="width:100%;padding:8px">

<label>Email</label>
<input name="email" type="email" style="width:100%;padding:8px">

<label>Telefono</label>
<input name="telefono" style="width:100%;padding:8px">

<br><br>
<button>📇 Crea tessera</button>

</form>

<?php
require ROOT_PATH . '/themes/semplice/footer.php';
