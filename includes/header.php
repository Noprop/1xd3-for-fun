<?php
$geckodePageTitle = $geckodePageTitle ?? 'Geckode';
$geckodeNavPrefix = $geckodeNavPrefix ?? '';
$navHome = $geckodeNavPrefix === '' ? '#hero' : 'index.php#hero';
$navWhy = $geckodeNavPrefix === '' ? '#why-geckode' : 'index.php#why-geckode';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($geckodePageTitle) ?></title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <a class="skip-link" href="#main-content">Skip to content</a>

  <header class="site-header">
    <div class="header-inner">
      <a class="brand" href="index.php" aria-label="Geckode home">
        <span class="brand-mark" aria-hidden="true">
          <span></span>
        </span>
        <span class="brand-text">Geckode</span>
      </a>

      <nav class="site-nav" id="site-nav" aria-label="Primary">
        <a href="<?= htmlspecialchars($navHome) ?>">Home</a>
        <a href="<?= htmlspecialchars($navWhy) ?>">Why Geckode?</a>
        <a href="auth.php">Get Started</a>
        <a href="<?= $geckodeNavPrefix === '' ? '#footer' : 'index.php#footer' ?>">Support</a>
      </nav>

      <div class="header-actions">
        <?php if ($currentUser): ?>
          <span class="signed-in-pill">Signed in as <?= htmlspecialchars($currentUser['display_name']) ?></span>
          <a class="btn btn-secondary" href="logout.php">Log out</a>
        <?php else: ?>
          <a class="btn btn-secondary" href="auth.php?panel=login">Log in</a>
          <a class="btn btn-primary" href="auth.php?panel=signup">Sign up</a>
        <?php endif; ?>
      </div>
    </div>
  </header>
