<?php
require_once __DIR__ . '/bootstrap.php';

$currentUser = geckode_current_user();
$flash = geckode_flash_get();

$geckodePageTitle = 'Geckode';
$geckodeNavPrefix = '';

require __DIR__ . '/includes/header.php';
?>

  <main id="main-content">
    <?php if ($flash): ?>
      <section class="notice notice-<?= htmlspecialchars($flash['type']) ?>" aria-live="polite">
        <?= htmlspecialchars($flash['message']) ?>
      </section>
    <?php endif; ?>

    <?php /* if ($currentUser): ?>
      <section class="account-banner">
        <div>
          <p class="eyebrow">Welcome back</p>
          <h2><?= htmlspecialchars($currentUser['display_name']) ?></h2>
          <p>You are signed in as <?= htmlspecialchars($currentUser['username']) ?>. Your profile and projects will live here in later increments.</p>
        </div>
        <a class="btn btn-secondary" href="logout.php">Sign out</a>
      </section>
    <?php endif; */ ?>

    <section class="hero" id="hero">
      <div class="hero-copy">
        <p class="eyebrow">Visual game builder for kids</p>
        <h1>Build Games.<br><span>Learn Code.</span></h1>
        <p class="hero-description">
          Geckode is a drag-and-drop platform that helps kids create interactive games and learn core programming ideas without typing complex syntax.
        </p>

        <div class="hero-actions">
          <a class="btn btn-primary btn-large" href="auth.php?panel=signup">Get Started</a>
          <a class="btn btn-secondary btn-large" href="demo.php">See How It Works</a>
        </div>
      </div>

      <div class="hero-visual" aria-hidden="true">
        <div class="mock-window">
          <div class="mock-window-bar">
            <span></span><span></span><span></span>
          </div>
          <div class="mock-workspace">
            <aside class="mock-palette">
              <span class="mock-block mock-block-blue"></span>
              <span class="mock-block mock-block-green"></span>
              <span class="mock-block mock-block-orange"></span>
              <span class="mock-block mock-block-purple"></span>
            </aside>
            <div class="mock-stage">
              <div class="mock-sprite">
                <span></span>
              </div>
              <div class="mock-block-stack">
                <span class="mock-block mock-block-blue mock-block-wide"></span>
                <span class="mock-block mock-block-green mock-block-medium"></span>
                <span class="mock-block mock-block-orange mock-block-short"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="why-section" id="why-geckode">
      <div class="section-heading section-heading-center">
        <p class="eyebrow">Why Geckode?</p>
        <h2>Built for first-time coders</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
      </div>

      <div class="feature-grid">
        <article class="feature-card">
          <div class="feature-icon feature-icon-purple" aria-hidden="true">⌘</div>
          <h3>Drag &amp; Drop Blocks</h3>
          <p>Visual coding keeps logic approachable and removes syntax barriers for beginners.</p>
        </article>

        <article class="feature-card">
          <div class="feature-icon feature-icon-green" aria-hidden="true">▶</div>
          <h3>Real-time Preview</h3>
          <p>See projects come to life instantly as blocks are arranged and changed.</p>
        </article>

        <article class="feature-card">
          <div class="feature-icon feature-icon-lilac" aria-hidden="true">◫</div>
          <h3>Share &amp; Collaborate</h3>
          <p>Classrooms and families can learn together and support each other’s projects.</p>
        </article>

        <article class="feature-card">
          <div class="feature-icon feature-icon-mint" aria-hidden="true">✦</div>
          <h3>Creative Freedom</h3>
          <p>Kids can customize characters, scenes, and ideas while learning core concepts.</p>
        </article>
      </div>
    </section>

    <section class="cta-band">
      <div>
        <p class="eyebrow">Ready to start building?</p>
        <h2>Join thousands of young creators.</h2>
      </div>
      <a class="btn btn-ghost btn-large" href="auth.php?panel=signup">Create your first game</a>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
