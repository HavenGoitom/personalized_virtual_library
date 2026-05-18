<div class="panel auth" style="max-width:760px;margin:12px auto">
  <h3>Login</h3>
  <form method="post" action="<?= BASE_PATH ?>/login" style="margin-top:10px;display:grid;gap:12px">
    <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">
    <label>Username</label>
    <input name="username" type="text" placeholder="e.g. alice_01" required>
    <label>Password</label>
    <input name="password" type="password" placeholder="Your password" required>
    <div style="display:flex;align-items:center;gap:8px;margin-top:8px">
      <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#6b3a3a">
        <input type="checkbox" name="remember_me"> Remember me
      </label>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-top:6px">
      <a class="btn ghost" href="<?= BASE_PATH ?>/signup">Signup</a>
      <button class="btn" type="submit">Login</button>
    </div>
    <div style="font-size:13px;color:#6d5a48">Passwords are hashed with bcrypt and sessions are used for login.</div>
  </form>
</div>
