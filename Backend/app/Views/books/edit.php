<div class="panel" style="max-width:760px;margin:12px auto">
  <h3 style="margin:0 0 12px">Edit Book</h3>
  <form method="post" action="<?= BASE_PATH ?>/books/update" enctype="multipart/form-data" style="display:grid;gap:12px">
    <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
    <input type="hidden" name="id" value="<?= $book['id'] ?>">
    <label>Title</label>
    <input name="title" type="text" value="<?= htmlspecialchars($book['title']) ?>" required>
    <div class="form-row">
      <label>Title</label>
      <input name="title" type="text" value="<?= htmlspecialchars($book['title']) ?>" required>
    </div>
    <div class="form-row">
      <label>Author</label>
      <input name="author" type="text" value="<?= htmlspecialchars($book['author']) ?>" required>
    </div>
    <div class="form-row">
      <label>Category</label>
      <select name="category" required>
        <option value="Fiction" <?= $book['category'] === 'Fiction' ? 'selected' : '' ?>>Fiction</option>
        <option value="Non-Fiction" <?= $book['category'] === 'Non-Fiction' ? 'selected' : '' ?>>Non-Fiction</option>
      </select>
    </div>
    <div class="form-row">
      <label>URL</label>
      <input name="url" type="url" value="<?= htmlspecialchars($book['url']) ?>" required>
    </div>
    <div class="form-row">
      <label>Cover image (leave blank to keep current)</label>
      <input name="cover" type="file" accept="image/*">
    </div>
    <div class="form-row">
      <label>Description</label>
      <textarea name="description"><?= htmlspecialchars($book['description']) ?></textarea>
    </div>
      <button class="btn" type="submit">Save</button>
    </div>
  </form>
</div>
