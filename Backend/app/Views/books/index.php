<div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
  <div>
    <h2 style="margin:0">Books</h2>
    <div style="font-size:13px;color:#6d5a48">Public collection organized by category.</div>
  </div>
  <div style="display:flex;gap:8px;align-items:center">
    <form action="<?= BASE_PATH ?>/books" method="get" style="display:flex;gap:8px;align-items:center">
      <input id="search-input" name="search" placeholder="Search title/author" value="<?= htmlspecialchars($search) ?>" style="padding:8px;border-radius:8px;border:1px solid #e6e2dd;width:240px">
      <button type="submit" class="btn ghost">Search</button>
    </form>
    <?php if ($user): ?>
      <a class="btn" href="<?= BASE_PATH ?>/books/create">Add book</a>
    <?php endif; ?>
  </div>
</div>

<div id="book-results" class="shelf-wrap">
  <?php
    $fiction = [];
    $nonFiction = [];

    function normalize_category_for_display($category)
    {
      $category = (string)($category ?? '');
      $normalized = strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', $category));
      if ($normalized === 'fiction') {
        return 'Fiction';
      }
      if ($normalized === 'nonfiction' || strpos($normalized, 'non') === 0 || strpos($normalized, 'nonfiction') !== false) {
        return 'Non-Fiction';
      }
      // Fallback to Non-Fiction for unknown categories to keep them visible
      return 'Non-Fiction';
    }

    foreach ($books as $book) {
      $book['category'] = normalize_category_for_display($book['category'] ?? '');
      $slug = strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', $book['category']));
      $bucket = $slug === 'fiction' ? 'fiction' : 'nonfiction';
      ${$bucket}[] = $book;
    }

  function coverUrl($cover) {
      if (!$cover) {
          return '';
      }

      if (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://')) {
          return $cover;
      }

      if (str_starts_with($cover, BASE_PATH)) {
          return $cover;
      }

      if (str_starts_with($cover, '/')) {
          return BASE_PATH . $cover;
      }

      return BASE_PATH . '/' . ltrim($cover, '/');
  }
  ?>

  <div class="shelf">
    <div class="shelf-label"><div>Fiction</div><div style="font-size:13px;opacity:0.9"><?= count($fiction) ?> book<?= count($fiction) !== 1 ? 's' : '' ?></div></div>
    <?php if (empty($fiction)): ?>
      <div class="panel" style="margin:12px">No fiction books found.</div>
    <?php else: ?>
      <div class="books-row">
        <?php foreach ($fiction as $book): ?>
          <div class="book-card">
            <a class="book-link" href="<?= htmlspecialchars($book['url'] ?: '#') ?>" target="_blank" rel="noopener noreferrer">
              <div class="cover" style="background-image:url('<?= htmlspecialchars(coverUrl($book['cover_image'])) ?>')"></div>
              <div class="spine-label"><?= htmlspecialchars($book['title']) ?></div>
            </a>
            <div class="cat-badge"><?= htmlspecialchars($book['category']) ?></div>
            <div class="tag <?= $book['owner_id'] === ($user['id'] ?? 0) ? 'you' : '' ?>">
              <?= $book['owner_id'] === ($user['id'] ?? 0) ? 'You added this' : 'Added by ' . htmlspecialchars($book['username']) ?>
            </div>
            <div class="opts" style="flex-direction:column;top:auto;bottom:8px;right:8px;">
              <form method="post" action="<?= BASE_PATH ?>/shelf/add" style="margin:0">
                <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                <button type="submit" class="icon" title="Add to My Shelf">＋</button>
              </form>
              <?php if ($user && $book['owner_id'] === $user['id']): ?>
                <div style="display:flex;gap:6px;margin-top:6px">
                  <a class="icon" href="<?= BASE_PATH ?>/books/edit?id=<?= $book['id'] ?>">✎</a>
                  <form method="post" action="<?= BASE_PATH ?>/books/delete" data-confirm="Delete this book?" style="margin:0;display:inline">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                    <input type="hidden" name="id" value="<?= $book['id'] ?>">
                    <button type="submit" class="icon">🗑</button>
                  </form>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="shelf">
    <div class="shelf-label"><div>Non-Fiction</div><div style="font-size:13px;opacity:0.9"><?= count($nonFiction) ?> book<?= count($nonFiction) !== 1 ? 's' : '' ?></div></div>
    <?php if (empty($nonFiction)): ?>
      <div class="panel" style="margin:12px">No non-fiction books found.</div>
    <?php else: ?>
      <div class="books-row">
        <?php foreach ($nonFiction as $book): ?>
          <div class="book-card">
            <a class="book-link" href="<?= htmlspecialchars($book['url'] ?: '#') ?>" target="_blank" rel="noopener noreferrer">
              <div class="cover" style="background-image:url('<?= htmlspecialchars(coverUrl($book['cover_image'])) ?>')"></div>
              <div class="spine-label"><?= htmlspecialchars($book['title']) ?></div>
            </a>
            <div class="cat-badge"><?= htmlspecialchars($book['category']) ?></div>
            <div class="tag <?= $book['owner_id'] === ($user['id'] ?? 0) ? 'you' : '' ?>">
              <?= $book['owner_id'] === ($user['id'] ?? 0) ? 'You added this' : 'Added by ' . htmlspecialchars($book['username']) ?>
            </div>
            <div class="opts" style="flex-direction:column;top:auto;bottom:8px;right:8px;">
              <form method="post" action="<?= BASE_PATH ?>/shelf/add" style="margin:0">
                <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                <button type="submit" class="icon" title="Add to My Shelf">＋</button>
              </form>
              <?php if ($user && $book['owner_id'] === $user['id']): ?>
                <div style="display:flex;gap:6px;margin-top:6px">
                  <a class="icon" href="<?= BASE_PATH ?>/books/edit?id=<?= $book['id'] ?>">✎</a>
                  <form method="post" action="<?= BASE_PATH ?>/books/delete" data-confirm="Delete this book?" style="margin:0;display:inline">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                    <input type="hidden" name="id" value="<?= $book['id'] ?>">
                    <button type="submit" class="icon">🗑</button>
                  </form>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
