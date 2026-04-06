<?php
require_once __DIR__ . '/bootstrap.php';

$currentUser = geckode_current_user();
$flash = geckode_flash_get();

$geckodePageTitle = 'See How It Works';
$geckodeNavPrefix = 'demo.php';

require __DIR__ . '/includes/header.php';
?>

  <main id="main-content">
    <?php if ($flash): ?>
      <section class="notice notice-<?= htmlspecialchars($flash['type']) ?>" aria-live="polite">
        <?= htmlspecialchars($flash['message']) ?>
      </section>
    <?php endif; ?>

    <!-- Demo content goes here -->
    <div id="container"></div>

    <script src="/1xd3-for-fun/js/demo.js"></script>

  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
