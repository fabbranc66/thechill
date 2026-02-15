<?php
declare(strict_types=1);

/* ==========================================================
   INIT
   - carica configurazione
   - avvia sessione
   - prepara DB ($pdo)
========================================================== */
require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

/* ==========================================================
   SICUREZZA
   - accesso consentito solo agli amministratori
========================================================== */
richiedi_ruolo('amministratore');

/* ==========================================================
   TEMPLATE
   - titolo pagina<?php
// ==========================================================
// FILE: admin/scanner_cassa.php
// RUOLO:
// - interfaccia scanner QR lato cassa
// - avvia fotocamera su mobile
// - reindirizza a scan_whatsapp.php
// ==========================================================

declare(strict_types=1);


// ==========================================================
// 1. BOOTSTRAP APPLICAZIONE
// - carica configurazione
// - avvia sessione
// - rende disponibile $pdo
// ==========================================================

require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';


// ==========================================================
// 2. SICUREZZA
// - accesso consentito solo agli amministratori
// ==========================================================

richiedi_ruolo('amministratore');


// ==========================================================
// 3. TEMPLATE HEADER
// - titolo pagina
// - layout grafico
// ==========================================================

$titolo = 'Scanner Cassa';
require __DIR__ . '/../themes/semplice/header.php';
?>

<!-- ======================================================
     4. TITOLO PAGINA
     ====================================================== -->
<h2 style="text-align:center;margin-bottom:6px">
    📷 Scanner cassa
</h2>

<!-- ======================================================
     5. STATO / TIMER SCANSIONE (UI)
     ====================================================== -->
<div id="scan-timer"
     style="text-align:center;font-weight:bold;color:#0b3d2e;min-height:22px">
    Pronto per la scansione
</div>

<?php if (is_mobile_device()): ?>

<!-- ======================================================
     6. CONTENITORE SCANNER QR (SOLO MOBILE)
     ====================================================== -->
<div id="scanner-wrapper">
    <div id="qr-reader"></div>
</div>

<!-- ======================================================
     7. MESSAGGIO FEEDBACK OPERATORE
     ====================================================== -->
<div id="msg"
     style="text-align:center;font-weight:600;margin-top:10px"></div>

<!-- ======================================================
     8. AUDIO FEEDBACK
     ====================================================== -->
<audio id="beep"
       src="<?= BASE_URL ?>/assets/beep.mp3"
       preload="auto"></audio>

<!-- ======================================================
     9. LIBRERIA HTML5 QR SCANNER
     ====================================================== -->
<script src="<?= BASE_URL ?>/assets/js/html5-qrcode.min.js"></script>

<script>
(function () {

    // ======================================================
    // 10. RIFERIMENTI DOM
    // ======================================================
    const msg   = document.getElementById('msg');
    const beep  = document.getElementById('beep');
    const timer = document.getElementById('scan-timer');

    // ======================================================
    // 11. BLOCCO ANTI-DOPPIA SCANSIONE
    // ======================================================
    let busy = false;

    // ======================================================
    // 12. ESTRAZIONE CODICE QR
    // - supporta URL con ?qr=
    // - oppure codice diretto
    // ======================================================
    function extractQr(text) {
        try {
            if (text.startsWith('http')) {
                const u = new URL(text);
                return u.searchParams.get('qr') || '';
            }
        } catch (e) {}
        return text.trim();
    }

    // ======================================================
    // 13. CALLBACK SCANSIONE RIUSCITA
    // ======================================================
    function onScanSuccess(decodedText) {

        if (busy) return;
        busy = true;

        const qr = extractQr(decodedText);
        if (!qr) {
            msg.textContent = '❌ QR non valido';
            busy = false;
            return;
        }

        // --------------------------------------------------
        // FEEDBACK UTENTE
        // --------------------------------------------------
        timer.textContent = '⏳ Invio in corso…';
        msg.textContent   = '📲 Apertura WhatsApp…';

        // feedback sonoro + vibrazione
        beep.play().catch(() => {});
        if (navigator.vibrate) navigator.vibrate(100);

        // --------------------------------------------------
        // REDIRECT SERVER-SIDE
        // --------------------------------------------------
        location.href =
            'scan_whatsapp.php?qr=' + encodeURIComponent(qr);
    }

    // ======================================================
    // 14. INIZIALIZZAZIONE SCANNER
    // ======================================================
    const scanner = new Html5QrcodeScanner(
        'qr-reader',
        {
            fps: 10,
            qrbox: { width: 160, height: 160 },
            rememberLastUsedCamera: true,
            videoConstraints: {
                facingMode: "environment",
                advanced: [{ zoom: 1.2 }]
            }
        },
        false
    );

    scanner.render(onScanSuccess, () => {});

})();
</script>

<?php else: ?>

<!-- ======================================================
     15. FALLBACK DESKTOP
     ====================================================== -->
<div
    style="
        max-width:420px;
        margin:40px auto;
        padding:20px;
        text-align:center;
        background:#f7f7f7;
        border:2px dashed #ccc;
        border-radius:12px;
        font-size:16px
    "
>
    🖥️ <strong>Scanner non disponibile</strong><br><br>
    Usa uno smartphone per scansionare le carte.
</div>

<?php endif; ?>

<?php
// ==========================================================
// 16. FOOTER
// ==========================================================

require __DIR__ . '/../themes/semplice/footer.php';
?>

   - header grafico
========================================================== */
$titolo = 'Scanner Cassa';
require __DIR__ . '/../themes/semplice/header.php';
?>

<!-- ======================================================
     TITOLO PAGINA
====================================================== -->
<h2 style="text-align:center;margin-bottom:6px">
  📷 Scanner cassa
</h2>

<!-- ======================================================
     TIMER STATO SCANSIONE
     (solo informativo lato UI)
====================================================== -->
<div id="scan-timer"
     style="text-align:center;font-weight:bold;color:#0b3d2e;min-height:22px">
  Pronto per la scansione
</div>

<?php if (is_mobile_device()): ?>

<!-- ======================================================
     CONTENITORE SCANNER QR
     - visibile solo su mobile
====================================================== -->
<div id="scanner-wrapper">
  <div id="qr-reader"></div>
</div>

<!-- ======================================================
     MESSAGGIO STATO
     - feedback all’operatore
====================================================== -->
<div id="msg" style="text-align:center;font-weight:600;margin-top:10px"></div>

<!-- ======================================================
     AUDIO FEEDBACK
====================================================== -->
<audio id="beep" src="<?= BASE_URL ?>/assets/beep.mp3" preload="auto"></audio>

<!-- ======================================================
     LIBRERIA QR SCANNER
====================================================== -->
<script src="<?= BASE_URL ?>/assets/js/html5-qrcode.min.js"></script>

<script>
(function () {

  /* ======================================================
     RIFERIMENTI DOM
  ====================================================== */
  const msg   = document.getElementById('msg');
  const beep  = document.getElementById('beep');
  const timer = document.getElementById('scan-timer');

  /* ======================================================
     BLOCCO ANTI-DOPPIO SCAN
  ====================================================== */
  let busy = false;

  /* ======================================================
     ESTRAZIONE CODICE QR
     - supporta link con ?qr=
     - oppure codice diretto
  ====================================================== */
  function extractQr(text) {
    try {
      if (text.startsWith('http')) {
        const u = new URL(text);
        return u.searchParams.get('qr') || '';
      }
    } catch (e) {}
    return text.trim();
  }

  /* ======================================================
     CALLBACK SCANSIONE RIUSCITA
  ====================================================== */
  function onScanSuccess(decodedText) {

    // evita doppia scansione
    if (busy) return;
    busy = true;

    // estrazione codice
    const qr = extractQr(decodedText);
    if (!qr) {
      msg.textContent = '❌ QR non valido';
      busy = false;
      return;
    }

    /* ==================================================
       FEEDBACK UTENTE
    ================================================== */
    timer.textContent = '⏳ Invio in corso…';
    msg.textContent   = '📲 Apertura WhatsApp…';

    // feedback sonoro + vibrazione
    beep.play().catch(()=>{});
    if (navigator.vibrate) navigator.vibrate(100);

    /* ==================================================
       REDIRECT SERVER-SIDE
       - niente AJAX
       - niente popup
       - WhatsApp si apre diretto
    ================================================== */
    location.href =
      'scan_whatsapp.php?qr=' + encodeURIComponent(qr);
  }

  /* ======================================================
     INIZIALIZZAZIONE SCANNER
  ====================================================== */
  const scanner = new Html5QrcodeScanner(
    'qr-reader',
    {
      fps: 10,
      qrbox: { width: 160, height: 160 },
      rememberLastUsedCamera: true,
      videoConstraints: {
        facingMode: "environment",
        advanced: [{ zoom: 1.2 }]
      }
    },
    false
  );

  // avvio scanner
  scanner.render(onScanSuccess, () => {});

})();
</script>

<?php else: ?>

<!-- ======================================================
     FALLBACK DESKTOP
====================================================== -->
<div
  style="
    max-width:420px;
    margin:40px auto;
    padding:20px;
    text-align:center;
    background:#f7f7f7;
    border:2px dashed #ccc;
    border-radius:12px;
    font-size:16px
  "
>
  🖥️ <strong>Scanner non disponibile</strong><br><br>
  Usa uno smartphone per scansionare le carte.
</div>

<?php endif; ?>

<?php
/* ==========================================================
   FOOTER
========================================================== */
require __DIR__ . '/../themes/semplice/footer.php';