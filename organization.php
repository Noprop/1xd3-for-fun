<?php
$geckodePageTitle = 'Organizations – Geckode';
$geckodeNavPrefix = '';
require_once __DIR__ . '/bootstrap.php';

/* ── guard: must be logged in ── */
if (!geckode_is_logged_in()) {
    geckode_flash_set('error', 'Please log in to manage organizations.');
    geckode_redirect('login.php');
}

$currentUser = geckode_current_user();

/* ================================================================
   ACTION HANDLERS (POST)
   ================================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    /* ── Create Organization ── */
    if ($action === 'create_org') {
        $name = trim($_POST['org_name'] ?? '');
        $type = $_POST['org_type'] ?? 'classroom';

        if ($name === '') {
            geckode_flash_set('error', 'Organization name cannot be empty.');
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO organizations (org_name, org_type, admin_user_id)
                 VALUES (:name, :type, :uid)"
            );
            $stmt->execute([
                ':name' => $name,
                ':type' => $type,
                ':uid'  => $currentUser['user_id'],
            ]);
            $newOrgId = $pdo->lastInsertId();

            /* auto-add creator as admin member */
            $stmt = $pdo->prepare(
                "INSERT INTO org_members (org_id, user_id, org_role)
                 VALUES (:oid, :uid, 'admin')"
            );
            $stmt->execute([':oid' => $newOrgId, ':uid' => $currentUser['user_id']]);

            geckode_flash_set('success', "Organization \"{$name}\" created!");
        }
        geckode_redirect('organizations.php');
    }

    /* ── Invite Member (by username) ── */
    if ($action === 'invite_member') {
        $orgId    = (int)($_POST['org_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $role     = $_POST['role'] ?? 'student';

        $check = $pdo->prepare(
            "SELECT 1 FROM org_members
              WHERE org_id = :oid AND user_id = :uid AND org_role = 'admin'"
        );
        $check->execute([':oid' => $orgId, ':uid' => $currentUser['user_id']]);

        if (!$check->fetch()) {
            geckode_flash_set('error', 'You are not an admin of this organization.');
        } elseif ($username === '') {
            geckode_flash_set('error', 'Please enter a username.');
        } else {
            $find = $pdo->prepare("SELECT user_id FROM users WHERE username = :u");
            $find->execute([':u' => $username]);
            $target = $find->fetch();

            if (!$target) {
                geckode_flash_set('error', "User \"{$username}\" not found.");
            } else {
                $ins = $pdo->prepare(
                    "INSERT OR IGNORE INTO org_members (org_id, user_id, org_role)
                     VALUES (:oid, :uid, :role)"
                );
                $ins->execute([
                    ':oid'  => $orgId,
                    ':uid'  => $target['user_id'],
                    ':role' => $role,
                ]);
                geckode_flash_set('success', "\"{$username}\" added to the organization.");
            }
        }
        geckode_redirect('organizations.php?view=' . $orgId);
    }

    /* ── Remove Member ── */
    if ($action === 'remove_member') {
        $orgId    = (int)($_POST['org_id'] ?? 0);
        $memberId = (int)($_POST['membership_id'] ?? 0);

        $check = $pdo->prepare(
            "SELECT 1 FROM org_members
              WHERE org_id = :oid AND user_id = :uid AND org_role = 'admin'"
        );
        $check->execute([':oid' => $orgId, ':uid' => $currentUser['user_id']]);

        if ($check->fetch()) {
            $del = $pdo->prepare("DELETE FROM org_members WHERE membership_id = :mid AND org_id = :oid");
            $del->execute([':mid' => $memberId, ':oid' => $orgId]);
            geckode_flash_set('success', 'Member removed.');
        }
        geckode_redirect('organizations.php?view=' . $orgId);
    }
}

/* ================================================================
   DATA FOR THE PAGE
   ================================================================ */
$myOrgs = $pdo->prepare(
    "SELECT o.*, om.org_role AS my_role
       FROM organizations o
       JOIN org_members om ON om.org_id = o.org_id
      WHERE om.user_id = :uid
      ORDER BY o.date_created DESC"
);
$myOrgs->execute([':uid' => $currentUser['user_id']]);
$orgList = $myOrgs->fetchAll();

$viewOrgId = isset($_GET['view']) ? (int)$_GET['view'] : null;
$activeOrg = null;
$members   = [];

if ($viewOrgId) {
    $stmt = $pdo->prepare("SELECT * FROM organizations WHERE org_id = :oid");
    $stmt->execute([':oid' => $viewOrgId]);
    $activeOrg = $stmt->fetch();

    if ($activeOrg) {
        $stmt = $pdo->prepare(
            "SELECT om.membership_id, om.org_role, om.joined_date,
                    u.username, u.display_name, u.email
               FROM org_members om
               JOIN users u ON u.user_id = om.user_id
              WHERE om.org_id = :oid
              ORDER BY om.joined_date ASC"
        );
        $stmt->execute([':oid' => $viewOrgId]);
        $members = $stmt->fetchAll();
    }
}

$isAdmin = false;
foreach ($orgList as $o) {
    if ($o['org_id'] == $viewOrgId && $o['my_role'] === 'admin') {
        $isAdmin = true;
        break;
    }
}

$flash = geckode_flash_get();

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ── org page layout ── */
.org-page {
  display: flex;
  gap: 1.5rem;
  width: min(calc(100% - 2rem), var(--page-width));
  margin: 1.5rem auto 2rem;
}

/* ── sidebar ── */
.org-sidebar { flex: 0 0 240px; }

.org-sidebar-heading {
  margin: 0 0 0.6rem;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--green-dark);
}

.org-list { list-style: none; padding: 0; margin: 0 0 1.25rem; }

.org-list li a {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 0.55rem 0.75rem;
  border-radius: var(--radius-sm);
  color: var(--text);
  font-size: 0.95rem;
  font-weight: 600;
  transition: background 0.15s;
}

.org-list li a:hover,
.org-list li a.is-active { background: var(--green-soft); }

/* ── role badges ── */
.org-badge {
  display: inline-block;
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 999px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.org-badge-admin    { background: var(--green);       color: #fff; }
.org-badge-educator { background: #ff9800;            color: #fff; }
.org-badge-student  { background: var(--purple-soft); color: var(--purple); }
.org-badge-member   { background: var(--border);      color: var(--muted); }

/* ── cards (matches hero-copy / feature-card style) ── */
.org-card {
  background: rgba(255, 255, 255, 0.88);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}
.org-card h2 {
  margin: 0 0 0.15rem;
  font-size: 1.25rem;
  letter-spacing: -0.03em;
}
.org-card .org-sub {
  color: var(--muted);
  font-size: 0.88rem;
  margin-bottom: 1rem;
}

/* ── form rows ── */
.org-form-row {
  display: flex;
  gap: 0.75rem;
  align-items: flex-end;
  flex-wrap: wrap;
}
.org-form-row .field { margin-bottom: 0; flex: 1; min-width: 140px; }

.org-form-row select {
  width: 100%;
  min-height: 3rem;
  padding: 0.8rem 0.95rem;
  border-radius: 14px;
  border: 1px solid var(--border);
  background: #fff;
  color: var(--text);
  font: inherit;
}
.org-form-row select:focus {
  border-color: var(--green);
  outline: 3px solid rgba(90, 181, 118, 0.45);
  outline-offset: 2px;
}

/* ── roster table ── */
.org-roster { width: 100%; border-collapse: collapse; margin-top: 0.75rem; }

.org-roster th,
.org-roster td {
  text-align: left;
  padding: 0.65rem 0.75rem;
  border-bottom: 1px solid var(--border);
  font-size: 0.92rem;
}
.org-roster th {
  color: var(--muted);
  font-weight: 700;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.org-roster tbody tr:hover { background: var(--surface-soft); }

/* ── danger button ── */
.btn-danger {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 2.2rem;
  padding: 0.4rem 0.85rem;
  border-radius: 999px;
  border: 1px solid transparent;
  background: #fff1ef;
  color: #b45146;
  font-weight: 700;
  font-size: 0.82rem;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}
.btn-danger:hover { background: #b45146; color: #fff; }

/* ── divider ── */
.org-divider { margin: 1.25rem 0; border: none; border-top: 1px solid var(--border); }

/* ── responsive ── */
@media (max-width: 767px) {
  .org-page { flex-direction: column; }
  .org-sidebar { flex: unset; }
  .org-roster th:nth-child(4),
  .org-roster td:nth-child(4) { display: none; }
}
</style>

<div class="org-page" id="main-content">

  <!-- ── SIDEBAR ── -->
  <aside class="org-sidebar">
    <p class="org-sidebar-heading">Your Organizations</p>

    <?php if (empty($orgList)): ?>
      <p style="font-size:.9rem;color:var(--muted);">None yet — create one!</p>
    <?php else: ?>
      <ul class="org-list">
        <?php foreach ($orgList as $o): ?>
          <li>
            <a href="organizations.php?view=<?= $o['org_id'] ?>"
               class="<?= $viewOrgId == $o['org_id'] ? 'is-active' : '' ?>">
              <span><?= htmlspecialchars($o['org_name']) ?></span>
              <span class="org-badge org-badge-<?= $o['my_role'] ?>">
                <?= $o['my_role'] ?>
              </span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <a href="organizations.php" class="btn btn-primary btn-block">+ Create New Org</a>
  </aside>

  <!-- ── MAIN CONTENT ── -->
  <section style="flex:1;min-width:0;">

    <?php if ($flash): ?>
      <div class="notice notice-<?= $flash['type'] ?>">
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <div style="height:1rem;"></div>
    <?php endif; ?>

    <!-- ── CREATE FORM ── -->
    <div class="org-card">
      <p class="eyebrow">Dashboard</p>
      <h2>Create Organization</h2>
      <p class="org-sub">Start a new hub for your creative coding team.</p>

      <form method="POST" action="organizations.php">
        <input type="hidden" name="action" value="create_org">
        <div class="org-form-row">
          <div class="field">
            <span>Organization Name</span>
            <input type="text" name="org_name"
                   placeholder="e.g. Oakwood Elementary Coding Club" required>
          </div>
          <div class="field">
            <span>Type</span>
            <select name="org_type">
              <option value="classroom">Classroom</option>
              <option value="school">School</option>
              <option value="community_group">Community Group</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" style="min-height:3rem;">
            Create Organization
          </button>
        </div>
      </form>
    </div>

    <!-- ── ROSTER (when viewing an org) ── -->
    <?php if ($activeOrg): ?>
    <div class="org-card">
      <p class="eyebrow">Editing: <?= htmlspecialchars($activeOrg['org_name']) ?></p>
      <h2><?= htmlspecialchars($activeOrg['org_name']) ?></h2>
      <p class="org-sub">
        <?= ucfirst(str_replace('_', ' ', $activeOrg['org_type'])) ?>
        &middot; Created <?= date('M j, Y', strtotime($activeOrg['date_created'])) ?>
      </p>

      <h3 style="margin:0 0 0.25rem;font-size:1.05rem;letter-spacing:-0.02em;">
        Organization Members
      </h3>

      <?php if (empty($members)): ?>
        <p style="color:var(--muted);font-size:.92rem;">No members yet.</p>
      <?php else: ?>
        <table class="org-roster">
          <thead>
            <tr>
              <th>Name</th>
              <th>Username</th>
              <th>Role</th>
              <th>Joined</th>
              <?php if ($isAdmin): ?><th>Actions</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($members as $m): ?>
            <tr>
              <td><?= htmlspecialchars($m['display_name']) ?></td>
              <td style="color:var(--muted);"><?= htmlspecialchars($m['username']) ?></td>
              <td>
                <span class="org-badge org-badge-<?= $m['org_role'] ?>">
                  <?= $m['org_role'] ?>
                </span>
              </td>
              <td><?= date('M j, Y', strtotime($m['joined_date'])) ?></td>
              <?php if ($isAdmin): ?>
              <td>
                <form method="POST" style="display:inline;"
                      onsubmit="return confirm('Remove this member?');">
                  <input type="hidden" name="action" value="remove_member">
                  <input type="hidden" name="org_id" value="<?= $activeOrg['org_id'] ?>">
                  <input type="hidden" name="membership_id" value="<?= $m['membership_id'] ?>">
                  <button type="submit" class="btn-danger">Remove</button>
                </form>
              </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <!-- ── INVITE FORM (admin only) ── -->
      <?php if ($isAdmin): ?>
      <hr class="org-divider">
      <h3 style="margin:0 0 0.5rem;font-size:1.05rem;letter-spacing:-0.02em;">
        + Invite New Member
      </h3>
      <form method="POST" action="organizations.php">
        <input type="hidden" name="action" value="invite_member">
        <input type="hidden" name="org_id" value="<?= $activeOrg['org_id'] ?>">
        <div class="org-form-row">
          <div class="field">
            <span>Username</span>
            <input type="text" name="username" placeholder="Enter username" required>
          </div>
          <div class="field">
            <span>Role</span>
            <select name="role">
              <option value="student">Student</option>
              <option value="educator">Educator</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" style="min-height:3rem;">
            Invite
          </button>
        </div>
      </form>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>