<?php 
require_once __DIR__ . '/includes/auth.php';
$title = 'Modération';
require 'includes/header.php';
require 'includes/db.php';
$rows = $db->query(
  "SELECT r.*,d.name AS dish 
  FROM ratings r 
  JOIN dishes d ON d.id=r.dish_id 
  ORDER BY r.id DESC"
)->fetchAll(); 
?>
<h1>Modération des avis</h1>
<p>Historique des contributions reçues.</p>
<table>
  <thead>
    <tr>
      <th>Plat</th>
      <th>Pseudo</th>
      <th>Avis</th>
      <th>Statut</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody><?php foreach ($rows as $r): ?>
    <tr>
      <td><?= h($r['dish']) ?></td>
      <td><?= h($r['nickname']) ?></td>
      <td><?= h($r['comment']) ?></td>
      <td><?= h($r['status']) ?></td>
      <td>
        <?php if ($r['status'] === 'pending'): ?>
          <form method="post" action="/moderation-action.php">
            <input type="hidden" name="token" value="<?= csrf() ?>">
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <button type="submit" name="status" value="published">Approuver</button>
            <button type="submit" name="status" value="rejected">Refuser</button>
          </form>
        <?php else: ?>
          —
        <?php endif; ?>
      </td>
    </tr><?php endforeach ?></tbody>
</table>
<p><a href="/dashboard.php">Retour au dashboard</a></p><?php require 'includes/footer.php'; ?>