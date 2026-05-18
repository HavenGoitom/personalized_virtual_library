<div class="panel" style="max-width:760px;margin:12px auto">
  <h3 style="margin:0 0 12px">Profile</h3>
  <form method="post" action="<?= BASE_PATH ?>/profile/update" enctype="multipart/form-data" style="display:grid;gap:12px">
    <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
    <div style="display:grid;grid-template-columns:1fr;gap:12px;align-items:start">
      <div>
        <label>Display name</label>
        <input name="display_name" type="text" value="<?= htmlspecialchars($user['display_name']) ?>" required>
        <label style="margin-top:8px">Bio</label>
        <textarea name="bio"><?= htmlspecialchars($user['bio']) ?></textarea>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:10px">
          <a class="btn ghost" href="<?= BASE_PATH ?>/books">Cancel</a>
          <button class="btn" type="submit">Save</button>
        </div>
      </div>
    </div>
    <div style="margin-top:10px" class="small">Profile changes are stored on the server and shown when you sign in.</div>
  </form>
</div>
