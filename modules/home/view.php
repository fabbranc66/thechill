<?php
declare(strict_types=1);

/* ==========================================================
     QUERY DATABASE
========================================================== */
$videoLoop = $pdo->query(
     "SELECT *
      FROM gallery_eventi
      WHERE attivo = 1
        AND sezione = 'video_loop'
        AND tipo = 'video'"
)->fetchAll();

$foto = $pdo->query(
     "SELECT *
      FROM gallery_eventi
      WHERE attivo = 1
        AND sezione = 'foto'"
)->fetchAll();

$instagram = $pdo->query(
     "SELECT *
      FROM gallery_eventi
      WHERE attivo = 1
        AND sezione = 'instagram'"
)->fetchAll();

$youtube = $pdo->query(
     "SELECT *
      FROM gallery_eventi
      WHERE attivo = 1
        AND sezione = 'youtube'"
)->fetchAll();

/* ==========================================================
     TEMPLATE
========================================================== */
$titolo = 'CheersClub';
require ROOT_PATH . '/themes/semplice/header.php';
?>

<!-- =======================================================
     AZIONI PRINCIPALI
======================================================= -->
<section class="main-actions">

<a href="<?= BASE_URL ?>/?mod=tavoli" class="btn-nuova-carta">
    Prenota tavolo
</a>

     <a href="<?= BASE_URL ?>/?mod=eventi" class="btn-nuova-carta">
          🎶 Eventi<br>
          <span>In arrivo</span>
     </a>

</section>


<!-- =======================================================
     GALLERY PRINCIPALE – 4 RIQUADRI
======================================================= -->
<section class="media-grid">

     <!-- ================= VIDEO LOOP ================= -->
     <div class="media video-loop">
          <?php foreach ($videoLoop as $v): ?>
               <video
                    class="loop-video"
                    src="<?= BASE_URL ?>/assets/<?= htmlspecialchars($v['file']) ?>"
                    muted
                    playsinline
               ></video>
          <?php endforeach; ?>
     </div>

     <!-- ================= FOTO ================= -->
     <div class="media">
          <?php foreach ($foto as $f): ?>
               <img
                    src="<?= BASE_URL ?>/assets/<?= htmlspecialchars($f['file']) ?>"
                    alt="Evento"
               >
          <?php endforeach; ?>
     </div>

     <!-- ================= INSTAGRAM ================= -->
     <div class="media">
          <?php foreach ($instagram as $i): ?>
               <blockquote
                    class="instagram-media"
                    data-instgrm-permalink="<?= htmlspecialchars($i['url']) ?>"
               ></blockquote>
          <?php endforeach; ?>
     </div>

     <!-- ================= YOUTUBE ================= -->
     <div class="media">
          <?php foreach ($youtube as $y): ?>
               <iframe
                    src="<?= htmlspecialchars($y['url']) ?>"
                    allow="autoplay; encrypted-media"
                    allowfullscreen
               ></iframe>
          <?php endforeach; ?>
     </div>

</section>


<!-- =======================================================
     SCRIPT INSTAGRAM
======================================================= -->
<script async src="https://www.instagram.com/embed.js"></script>


<!-- =======================================================
     SCRIPT VIDEO LOOP
======================================================= -->
<script>
     const vids = document.querySelectorAll('.loop-video');
     let idx = 0;

     vids.forEach(v => v.muted = true);

     function play(i) {
          vids.forEach(v => v.style.display = 'none');
          vids[i].style.display = 'block';
          vids[i].play();
     }

     vids.forEach((v, i) => {
          v.addEventListener('ended', () => {
               idx = (i + 1) % vids.length;
               play(idx);
          });
     });

     if (vids.length) {
          play(0);
     }
</script>

<?php
require ROOT_PATH . '/themes/semplice/footer.php';
