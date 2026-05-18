<div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
  <div>
    <h2 style="margin:0">My Shelf</h2>
    <div style="font-size:13px;color:#6d5a48">Your personal saved books.</div>
  </div>
</div>

<?php if (empty($items)): ?>
  <div class="panel"><div class="small">Your shelf is empty. Add a book from the Books page.</div></div>
<?php else: ?>
  <div style="display:grid;gap:12px;margin-top:8px">
    <?php foreach ($items as $item): ?>
      <?php
      $title = $item['custom_title'] ?: $item['original_title'];
      $author = $item['custom_author'] ?: $item['original_author'];
      $category = $item['custom_category'] ?: $item['original_category'];
      $description = $item['custom_description'] ?: $item['original_description'];
      $cover = $item['custom_cover_image'] ?: $item['original_cover_image'];
      if (!$cover) {
          $coverUrl = '';
      } elseif (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://') || str_starts_with($cover, BASE_PATH)) {
          $coverUrl = $cover;
      } elseif (str_starts_with($cover, '/')) {
          $coverUrl = BASE_PATH . $cover;
      } else {
          $coverUrl = BASE_PATH . '/' . ltrim($cover, '/');
      }
      ?>
      <div class="panel" style="display:flex;gap:12px;align-items:center">
        <div style="width:110px;height:150px;border-radius:8px;background-size:cover;background-position:center;background-image:url('<?= htmlspecialchars($coverUrl) ?>')"></div>
        <div style="flex:1">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div>
              <div style="font-weight:700"><?= htmlspecialchars($title) ?></div>
              <div style="font-size:13px;color:#6d5a48"><?= htmlspecialchars($author) ?></div>
              <?php if ($item['book_owner_id'] === $user['id']): ?>
                <div style="font-size:12px;color:#999;margin-top:2px">You added this</div>
              <?php else: ?>
                <div style="font-size:12px;color:#999;margin-top:2px">Added by <?= htmlspecialchars($item['book_owner_username']) ?></div>
              <?php endif; ?>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;align-items:center;flex-wrap:wrap">
              <a class="btn ghost" href="<?= htmlspecialchars($item['original_url'] ?: '#') ?>" target="_blank" rel="noopener noreferrer">Read</a>
              <?php if ($item['book_owner_id'] === $user['id']): ?>
                <a class="btn ghost" href="<?= BASE_PATH ?>/shelf/edit?id=<?= $item['id'] ?>">Edit</a>
                <form method="post" action="<?= BASE_PATH ?>/shelf/remove" data-confirm="Remove this book from your shelf?" style="margin:0;display:inline">
                  <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                  <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                  <button type="submit" class="btn">Delete</button>
                </form>
              <?php else: ?>
                <form method="post" action="<?= BASE_PATH ?>/shelf/remove" data-confirm="Remove this book from your shelf?" style="margin:0;display:inline">
                  <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
                  <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                  <button type="submit" class="btn">Remove</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
          <div style="margin-top:8px;color:var(--muted)"><?= htmlspecialchars($description) ?></div>
          <div style="margin-top:8px;font-size:13px;color:#6d5a48">Category: <?= htmlspecialchars($category) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
