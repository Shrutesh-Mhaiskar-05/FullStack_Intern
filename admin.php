<?php
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unreadCount = $pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $id = (int)$_POST['mark_read'];
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$id]);
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([$id]);
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Inter', sans-serif;
      background: #050505;
      color: #e8e8e8;
      padding: 40px 24px;
    }
    .container { max-width: 900px; margin: 0 auto; }
    h1 { font-size: 1.8rem; margin-bottom: 8px; }
    h1 span { color: #39FF88; }
    .subtitle { color: #888; margin-bottom: 32px; }
    .stats {
      display: flex; gap: 24px; margin-bottom: 32px; flex-wrap: wrap;
    }
    .stat-box {
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(57,255,136,0.1);
      border-radius: 12px;
      padding: 20px 28px;
      flex: 1; min-width: 140px;
    }
    .stat-box .num {
      font-size: 1.8rem; font-weight: 700; color: #39FF88;
    }
    .stat-box .label { font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 1px; }
    .message-card {
      background: rgba(255,255,255,0.02);
      border: 1px solid rgba(57,255,136,0.08);
      border-radius: 14px;
      padding: 24px;
      margin-bottom: 16px;
      transition: 0.3s;
    }
    .message-card:hover { border-color: rgba(57,255,136,0.2); }
    .message-card.unread {
      border-color: #39FF88;
      background: rgba(57,255,136,0.03);
    }
    .message-card.unread .badge {
      display: inline-block;
      background: #39FF88;
      color: #050505;
      font-size: 0.7rem;
      font-weight: 700;
      padding: 2px 10px;
      border-radius: 10px;
      text-transform: uppercase;
      margin-left: 8px;
    }
    .msg-header {
      display: flex; justify-content: space-between; align-items: flex-start;
      margin-bottom: 12px; flex-wrap: wrap; gap: 8px;
    }
    .msg-from {
      font-weight: 600; font-size: 1.05rem;
    }
    .msg-from small {
      font-weight: 400; color: #888; font-size: 0.85rem; margin-left: 8px;
    }
    .msg-date { font-size: 0.8rem; color: #666; }
    .msg-body {
      color: #aaa; line-height: 1.6; margin-bottom: 16px;
    }
    .msg-actions { display: flex; gap: 8px; }
    .msg-actions button {
      padding: 6px 16px;
      border: 1px solid rgba(57,255,136,0.15);
      background: transparent;
      color: #e8e8e8;
      border-radius: 8px;
      font-size: 0.8rem;
      cursor: pointer;
      transition: 0.3s;
      font-family: 'Inter', sans-serif;
    }
    .msg-actions .read-btn:hover {
      border-color: #39FF88; color: #39FF88;
    }
    .msg-actions .delete-btn:hover {
      border-color: #ff4d4d; color: #ff4d4d;
    }
    .empty { color: #888; text-align: center; padding: 60px 0; }
    .empty i { font-size: 3rem; margin-bottom: 16px; color: rgba(57,255,136,0.2); }
    .back-link {
      display: inline-flex; align-items: center; gap: 6px;
      color: #888; margin-bottom: 24px; text-decoration: none; font-size: 0.9rem;
    }
    .back-link:hover { color: #39FF88; }
  </style>
</head>
<body>
  <div class="container">
    <a href="index.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
    <h1>Messages <span>Inbox</span></h1>
    <p class="subtitle">Messages sent through your portfolio contact form</p>

    <div class="stats">
      <div class="stat-box">
        <div class="num"><?= count($messages) ?></div>
        <div class="label">Total Messages</div>
      </div>
      <div class="stat-box">
        <div class="num"><?= $unreadCount ?></div>
        <div class="label">Unread</div>
      </div>
    </div>

    <?php if (empty($messages)): ?>
      <div class="empty">
        <i class="fas fa-inbox"></i>
        <p>No messages yet. Share your portfolio link!</p>
      </div>
    <?php else: ?>
      <?php foreach ($messages as $msg): ?>
        <div class="message-card <?= $msg['is_read'] ? '' : 'unread' ?>">
          <div class="msg-header">
            <div>
              <span class="msg-from">
                <?= htmlspecialchars($msg['name']) ?>
                <small><?= htmlspecialchars($msg['email']) ?></small>
              </span>
              <?php if (!$msg['is_read']): ?>
                <span class="badge">New</span>
              <?php endif; ?>
            </div>
            <span class="msg-date"><?= date('M j, Y g:i A', strtotime($msg['created_at'])) ?></span>
          </div>
          <div class="msg-body"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
          <div class="msg-actions">
            <?php if (!$msg['is_read']): ?>
              <form method="POST" style="display:inline">
                <button name="mark_read" value="<?= $msg['id'] ?>" class="read-btn">
                  <i class="fas fa-check"></i> Mark Read
                </button>
              </form>
            <?php endif; ?>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this message?')">
              <button name="delete" value="<?= $msg['id'] ?>" class="delete-btn">
                <i class="fas fa-trash"></i> Delete
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>
