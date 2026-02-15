<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($titolo ?? 'Tavoli') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet"
      href="<?= BASE_URL ?>/themes/tables-v2/style.css">
</head>
<body>

<header class="site-header">
  <div class="inner" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:0 12px">
    
    <!-- LOGO / TITOLO -->
    <div>
      🍷 <strong>CheersClub</strong>
    </div>

    <!-- NAV (solo se loggato) -->
<?php if (!empty($_SESSION['utente']['ruolo']) && $_SESSION['utente']['ruolo'] === 'amministratore'): ?>
  <nav style="display:flex;gap:10px;font-size:14px">
    <a href="<?= BASE_URL ?>/admin/index.php"
       style="color:#fff;text-decoration:none;font-weight:600">
      Dashboard
    </a>
    <a href="<?= BASE_URL ?>/logout.php"
       style="color:#fff;text-decoration:none;font-weight:600">
      Logout
    </a>
  </nav>
<?php endif; ?>

  </div>
</header>

<main class="content">