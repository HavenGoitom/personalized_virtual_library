<div class="panel" style="max-width:760px;margin:12px auto">
  <h3 style="margin:0 0 12px">Add Book</h3>
  <form method="post" action="<?= BASE_PATH ?>/books/create" enctype="multipart/form-data" style="display:grid;gap:12px">
    <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
    <div class="form-row">
      <label>Title</label>
      <input name="title" type="text" required>
    </div>
    <div class="form-row">
      <label>Author</label>
      <input name="author" type="text" required>
    </div>
    <div class="form-row">
      <label>Category</label>
      <select name="category" required>
        <option value="Fiction">Fiction</option>
        <option value="Non-Fiction">Non-Fiction</option>
      </select>
    </div>
    <div class="form-row">
      <label>URL</label>
      <input name="url" type="url" placeholder="https://..." required>
    </div>
    <div class="form-row">
      <label>Cover image</label>
      <input name="cover" type="file" accept="image/*" required>
    </div>
    <div class="form-row">
      <label>Description</label>
      <textarea name="description"></textarea>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:8px">
      <a class="btn ghost" href="<?= BASE_PATH ?>/books">Cancel</a>
      <button class="btn" type="submit">Save</button>
    </div>
  </form>
</div>
