<?php
$flash = \App\Core\Session::flash('message');
if ($flash):
    $type = htmlspecialchars($flash['type'] ?? 'info');
    $text = htmlspecialchars($flash['text'] ?? '');
?>
  <div class="panel flash <?= $type ?>" role="alert">
    <?= $text ?>
  </div>
<?php endif; ?>
