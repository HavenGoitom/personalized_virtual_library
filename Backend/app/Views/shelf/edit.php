<?php
$titleValue = $item['original_title'];
$authorValue = $item['original_author'];
?>
<div class="panel" style="max-width:760px;margin:12px auto">
  <h3 style="margin:0 0 12px">Edit Shelf Copy</h3>
  <form method="post" action="<?= BASE_PATH ?>/shelf/update" enctype="multipart/form-data" style="display:grid;gap:12px">
    <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
    <label>Title</label>
    <input name="title" type="text" value="<?= htmlspecialchars($item['original_title']) ?>" readonly>
    <label>Author</label>
    <input name="author" type="text" value="<?= htmlspecialchars($item['original_author']) ?>" readonly>
    <label>Category</label>
    <select name="category" required>
      <option value="Fiction" <?= $item['original_category'] === 'Fiction' ? 'selected' : '' ?>>Fiction</option>
      <option value="Non-Fiction" <?= $item['original_category'] === 'Non-Fiction' ? 'selected' : '' ?>>Non-Fiction</option>
    </select>
    <label>Cover image (optional)</label>
    <input name="cover" type="file" accept="image/*">
    <label>Description</label>
    <textarea name="description" readonly><?= htmlspecialchars($item['original_description']) ?></textarea>
    <div style="display:flex;justify-content:flex-end;gap:8px">
      <a class="btn ghost" href="<?= BASE_PATH ?>/shelf">Cancel</a>
      <button class="btn" type="submit">Save</button>
    </div>
  </form>
</div>
