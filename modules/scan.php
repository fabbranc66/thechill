<?php
declare(strict_types=1);

/* ==========================================================
     FILE: scan.php (ROOT PUBBLICA)
     RUOLO:
     - entry point di TUTTI i QR code
     - decide il flusso in base al ruolo
     - NON modifica mai il database
========================================================== */


/* ==========================================================
     INIT
     - configurazione
     - sessione
     - $pdo disponibile
========================================================== */
require __DIR__ . '/includes/init.php';


/* ==========================================================
     INPUT / PARAMETRI
     - parametro standard: qr=
========================================================== */
$codice = $_GET['qr'] ?? '';

if ($codice === '') {
     http_response_code(404);
     exit('QR non valido');
}


/* ==========================================================
     CONTROLLO RUOLO
========================================================== */
if (
     isset($_SESSION['utente']) &&
     $_SESSION['utente']['ruolo'] === 'amministratore'
) {
     header(
          'Location: ' . BASE_URL . '/admin/scan.php?qr=' . urlencode($codice)
     );
     exit;
}


/* ==========================================================
     MODALITÀ CLIENTE / PUBBLICA (READ-ONLY)
========================================================== */
$stmt = $pdo->prepare(
     'SELECT c.id,
             c.codice_carta,
             c.punti,
             u.nome
      FROM carte_fedelta c
      JOIN utenti u ON u.id = c.utente_id
      WHERE c.codice_carta = ?'
);
$stmt->execute([$codice]);

$carta = $stmt->fetch();

if (!$carta) {
     http_response_code(404);
     exit('Carta non trovata');
}


/* ==========================================================
     STATO PREMI (INFORMATIVO)
========================================================== */
$stmt = $pdo->query(
     'SELECT MIN(punti_necessari)
      FROM premi
      WHERE attivo = 1'
);

$min_punti_premio = (int) $stmt->fetchColumn();

$puo_riscattare = $min_punti_premio > 0
     && $carta['punti'] >= $min_punti_premio;

$punti_mancanti = max(
     0,
     $min_punti_premio - $carta['punti']
);


/* ==========================================================
     URL QR PUBBLICO
========================================================== */
$qr_image_url = BASE_URL . '/qr.php?qr=' . urlencode($carta['codice_carta']);


/* ==========================================================
     TEMPLATE
========================================================== */
$titolo = 'Carta fedeltà';
require __DIR__ . '/themes/semplice/header.php';
?>

<!-- =======================================================
     CONTENUTO
======================================================= -->
<div style="
     max-width:420px;
     margin:40px auto;
     text-align:center;
">

     <!-- ================= INTESTAZIONE ================= -->
     <p style="margin-bottom:4px">Benvenuto</p>

     <h2 style="margin-top:0">
          <?= htmlspecialchars($carta['nome']) ?>
     </h2>

     <p>
          <strong>Codice carta:</strong><br>
          <?= htmlspecialchars($carta['codice_carta']) ?>
     </p>

     <!-- ================= QR CODE ================= -->
     <p style="margin:18px 0">
          <img
               src="<?= htmlspecialchars($qr_image_url) ?>"
               alt="QR Code carta fedeltà"
               style="
                    max-width:170px;
                    border:8px solid #fff;
                    box-shadow:0 0 4px rgba(0,0,0,.2)
               "
          >
     </p>

     <!-- ================= SALDO ================= -->
     <p style="font-size:22px;margin:10px 0">
          ⭐ <strong><?= (int) $carta['punti'] ?></strong> punti
     </p>

     <!-- ================= STATO PREMI ================= -->
     <?php if ($min_punti_premio === 0): ?>

          <p style="opacity:.6">
               🎁 Premi non ancora disponibili
          </p>

     <?php elseif ($puo_riscattare): ?>

          <p style="color:green;font-weight:bold">
               🎉 Hai premi riscattabili in cassa!
          </p>

     <?php else: ?>

          <p style="opacity:.7">
               🎁 Ti mancano
               <strong><?= $punti_mancanti ?></strong>
               punti per riscattare un premio
          </p>

     <?php endif; ?>

     <!-- ================= FOOTER ================= -->
     <p style="opacity:.7;margin-top:20px">
          Mostra questo QR in cassa per accumulare punti
     </p>

</div>

<?php
/* ==========================================================
     FOOTER
========================================================== */
require __DIR__ . '/themes/semplice/footer.php';
?>
