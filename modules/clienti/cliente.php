<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/init.php';
require __DIR__ . '/query.php';

$token = $_GET['t'] ?? '';

if ($token === '') {
    die('Accesso non valido');
}

/* cliente + punti carta */
$stmt = $pdo->prepare(
    "SELECT 
        u.id,
        u.nome,
        u.token_accesso,
        c.punti
     FROM utenti u
     LEFT JOIN carte_fedelta c ON c.utente_id = u.id
     WHERE u.token_accesso = ?
     AND u.ruolo = 'cliente'
     LIMIT 1"
);
$stmt->execute([$token]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die('Cliente non trovato');
}

/* percorso tema */
$theme_path = realpath(__DIR__ . '/../../themes/semplice/') . DIRECTORY_SEPARATOR;

/* QR corretto */
$qr_url = BASE_URL . '/assets/qr/' . $cliente['token_accesso'] . '.png';

$titolo = 'Area cliente';
require $theme_path . 'header.php';
?>

<style>
/* elimina spazio sotto header */
.content {
    padding-top: 0 !important;
}

/* carta stile bancomat */
.card-wrapper {
    display: flex;
    justify-content: center;
    padding: 20px 0 20px 0;
}

.bancomat {
    width: 360px;
    max-width: 92%;
    background: linear-gradient(135deg, #0b3d2e, #145a43);
    color: #fff;
    border-radius: 20px;
    padding: 22px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.25);
    position: relative;
    overflow: hidden;
    text-align: center;
}
/* riflesso carta */
.bancomat::after {
    content: "";
    position: absolute;
    top: -40%;
    left: -20%;
    width: 160%;
    height: 160%;
    background: linear-gradient(120deg, transparent 40%, rgba(255,255,255,0.15) 50%, transparent 60%);
    transform: rotate(25deg);
}

/* logo al posto del chip */
.card-logo {
    margin-bottom: 5px;
    display: flex;
    align-items: left;
    gap: 30px;
}

.logo-main {
    height: 60px;
    width: auto;
}

.logo-fed {
    height: 60px;
    width: auto;
}

/* nome cliente */
.card-name {
    font-size: 18px;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 18px;
}

/* punti */
.card-points-label {
    font-size: 12px;
    opacity: 0.8;
}

.card-points {
    font-size: 36px;
    font-weight: 700;
    margin-top: 2px;
}

/* QR zona */
.qr-area {
    background: #fff;
    padding: 14px;
    border-radius: 14px;
    text-align: center;
    margin: 18px auto 0 auto;
    display: inline-block;
}

.qr-area img {
    width: 180px;
    max-width: 100%;
}

/* info sotto */
.card-info {
    text-align: center;
    font-size: 14px;
    color: #fef9f9;
    margin: 10px auto 10px auto;
    max-width: 360px;
    line-height: 1.4;
}

/* pulsanti azione cliente */
.client-actions {
    max-width: 360px;
    margin: 10px auto 40px auto;
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.client-actions a {
    display: block;
    padding: 12px;
    background: #0b3d2e;
    color: #fff;
    text-decoration: none;
    border-radius: 10px;
    text-align: center;
    font-weight: 600;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.client-actions a:hover {
    background: #145a43;
}
</style>

<div class="card-wrapper">

    <div class="bancomat">

<div class="card-logo">
    <?php if (!empty($SETTINGS['logo'])): ?>
        <img
            src="<?= BASE_URL ?>/assets/img/<?= htmlspecialchars($SETTINGS['logo']) ?>"
            alt="Logo"
            class="logo-main"
        >
    <?php endif; ?>

    <img
        src="<?= BASE_URL ?>/assets/img/fedechill.png"
        alt="FedeChill"
        class="logo-fed"
    >
</div>

        <div class="card-name">
            <?= htmlspecialchars($cliente['nome']) ?>
        </div>

        <div class="card-points-label">
            Punti disponibili
        </div>
        <div class="card-points">
            <?= (int)($cliente['punti'] ?? 0) ?>
        </div>

        <div class="qr-area">
            <img src="<?= $qr_url ?>" alt="QR Code">
        </div>
        <div class="card-info">
            Vai alla cassa per accumulare o usare i punti.
        </div>

    </div>

</div>


<div class="client-actions">
    <a class="btn-azione"
       href="<?= BASE_URL ?>/?mod=tavoli&public=1&t=<?= urlencode($_GET['t'] ?? '') ?>">
      🍽 Prenotazione tavolo
    </a>

    <a href="#">🧾 Ordina</a>

    <a href="#">🎉 Prenotazione eventi</a>

<a class="btn-azione"
   href="<?= BASE_URL ?>/chillquiz/?nome=<?= urlencode($cliente['nome']) ?>">
  🎮 Prenotazione game
</a>
</div>

<?php
require $theme_path . 'footer.php';
