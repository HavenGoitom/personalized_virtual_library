<?php
$user = \App\Core\Auth::user();
$active = $active ?? 'home';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title ?? 'Woodland Library') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Roboto:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
</head>
<body>
  <div class="app">
    <header class="topbar">
      <div class="brand">
        <div class="logo">WL</div>
        <div>
          <div class="site-title">Woodland Library</div>
          <div class="site-sub" style="font-size:11px;color:rgba(255,255,255,0.9)">A wooden-themed reading experience</div>
        </div>
      </div>

      <nav class="topnav">
        <a href="<?= BASE_PATH ?>/" class="<?= $active === 'home' ? 'active' : '' ?>">Home</a>
        <a href="<?= BASE_PATH ?>/books" class="<?= $active === 'books' ? 'active' : '' ?>">Books</a>
        <a href="<?= BASE_PATH ?>/shelf" class="<?= $active === 'shelf' ? 'active' : '' ?>">My Shelf</a>
        <a href="<?= BASE_PATH ?>/profile" class="<?= $active === 'profile' ? 'active' : '' ?>">Profile</a>
      </nav>

      <div class="right-controls">
        <?php if ($user): ?>
          <div class="user-chip">
            <div class="user-av" style="background: rgba(255,255,255,0.12); color: #fff;">
              <?= strtoupper(htmlspecialchars($user['display_name'][0] ?? $user['username'][0] ?? 'U')) ?>
            </div>
            <div style="color:#fff;font-size:13px;">Signed in: <?= htmlspecialchars($user['display_name'] ?: $user['username']) ?></div>
          </div>
          <form method="post" action="<?= BASE_PATH ?>/logout" style="margin:0">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
            <button type="submit" class="btn ghost">Logout</button>
          </form>
        <?php else: ?>
          <a href="<?= BASE_PATH ?>/login" class="btn ghost">Login</a>
          <a href="<?= BASE_PATH ?>/signup" class="btn">Signup</a>
        <?php endif; ?>
      </div>
    </header>

    <main>
      <div class="content">
        <?php require __DIR__ . '/flash.php'; ?>
