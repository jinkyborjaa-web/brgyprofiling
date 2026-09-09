<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$publicPdo = null;
try {
    require_once __DIR__ . '/server/config.php';
    $publicPdo = $pdo;
} catch (Throwable $exception) {
    error_log('Public page database error: ' . $exception->getMessage());
}

$isPublicGuest = !isset($_SESSION['user_id']);

function publicRows(string $query, array $params = []): array
{
    global $publicPdo;
    if (!$publicPdo) {
        return [];
    }

    try {
        $statement = $publicPdo->prepare($query);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $exception) {
        error_log('Public page query error: ' . $exception->getMessage());
        return [];
    }
}

function publicCount(string $query): int
{
    global $publicPdo;
    if (!$publicPdo) {
        return 0;
    }

    try {
        return (int)$publicPdo->query($query)->fetchColumn();
    } catch (Throwable $exception) {
        error_log('Public page count error: ' . $exception->getMessage());
        return 0;
    }
}

function publicText(?string $value, string $fallback = 'Not available'): string
{
    $value = trim((string)$value);
    return htmlspecialchars($value === '' ? $fallback : $value, ENT_QUOTES, 'UTF-8');
}

function publicDate(?string $value, string $fallback = 'Date to be announced'): string
{
    if (!$value) {
        return $fallback;
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('F j, Y', $timestamp) : publicText($value, $fallback);
}

function publicGate(string $page, string $action): string
{
    if (!isset($_SESSION['user_id'])) {
        return 'auth.php?return=' . rawurlencode($page . '#' . $action);
    }

    return '#' . $action;
}

function publicHeader(string $title, string $subtitle, string $icon): void
{
    $safeTitle = publicText($title);
    $safeSubtitle = publicText($subtitle);
    $currentUser = $_SESSION['username'] ?? null;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $safeTitle ?> | Barangay San Isidro</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root { --navy:#103b4a; --teal:#087f7b; --mint:#d8f2e7; --gold:#f3bf62; --paper:#f7fbf8; --ink:#17323a; --muted:#62777a; --line:#dbe9e2; }
            * { box-sizing:border-box; } body { margin:0; background:var(--paper); color:var(--ink); font-family:Arial,sans-serif; }
            header { background:var(--navy); color:white; padding:18px max(24px,calc((100% - 1124px)/2)); display:flex; align-items:center; justify-content:space-between; gap:20px; }
            .brand { color:white; text-decoration:none; font:500 21px Georgia,serif; } header nav { display:flex; align-items:center; gap:20px; } header a { color:#d8f2e7; text-decoration:none; font-size:13px; }
            .logout { border:1px solid rgba(255,255,255,.4); padding:9px 13px; border-radius:3px; }
            main { max-width:1124px; margin:auto; padding:34px 24px 80px; } .breadcrumb { color:var(--teal); font-size:13px; font-weight:700; text-decoration:none; }
            .hero { display:flex; align-items:flex-start; gap:22px; padding:40px 0 36px; border-bottom:1px solid var(--line); } .hero-icon { color:var(--teal); font-size:28px; width:46px; padding-top:7px; }
            h1,h2,h3 { font-family:Georgia,serif; font-weight:500; color:var(--navy); } h1 { font-size:clamp(36px,5vw,56px); margin:0 0 12px; } h2 { font-size:28px; margin:0 0 18px; } h3 { font-size:20px; margin:0 0 9px; }
            .hero p { color:var(--muted); margin:0; font-size:16px; line-height:1.6; } .section { padding-top:44px; } .section > p { color:var(--muted); line-height:1.6; max-width:760px; }
            .grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; } .grid.two { grid-template-columns:repeat(2,1fr); } .panel { background:white; border:1px solid var(--line); padding:24px; }
            .panel p { color:var(--muted); line-height:1.6; font-size:14px; } .stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; } .stat { background:var(--mint); border:1px solid #bfe1d2; padding:20px; } .stat strong { display:block; color:var(--navy); font:500 34px Georgia,serif; }
            .stat span, .meta { color:var(--muted); font-size:12px; } .official { min-height:170px; } .avatar { width:48px; height:48px; border-radius:50%; display:grid; place-items:center; background:var(--mint); color:var(--teal); font-size:19px; margin-bottom:16px; }
            .official .position { color:var(--teal); font-size:13px; font-weight:700; } .official .meta { display:block; margin-top:8px; line-height:1.5; }
            .button { display:inline-flex; align-items:center; gap:8px; border:0; border-radius:3px; padding:12px 16px; background:var(--teal); color:white; text-decoration:none; font-size:13px; font-weight:700; cursor:pointer; } .button.alt { background:white; color:var(--teal); border:1px solid var(--teal); }
            .actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:22px; } .form-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; } label { display:block; color:var(--navy); font-size:12px; font-weight:700; margin-bottom:6px; } input, select, textarea { width:100%; padding:12px; border:1px solid #cbded5; border-radius:3px; font:14px Arial,sans-serif; background:white; } textarea { min-height:105px; resize:vertical; } .full { grid-column:1 / -1; }
            .notice { padding:14px 17px; background:#fff8e8; border:1px solid #ecd39f; color:#76591d; font-size:13px; line-height:1.5; } .success { padding:18px; background:var(--mint); border:1px solid #bfe1d2; color:#165a4d; line-height:1.5; }
            table { width:100%; border-collapse:collapse; background:white; } th, td { text-align:left; padding:13px 14px; border-bottom:1px solid var(--line); font-size:13px; } th { color:var(--navy); font-size:12px; text-transform:uppercase; letter-spacing:.5px; } td { color:var(--muted); }
            .bar { height:9px; background:var(--mint); margin-top:9px; } .bar i { display:block; height:100%; background:var(--teal); } .empty { color:var(--muted); font-size:14px; padding:10px 0; }
            footer { background:var(--navy); color:#c8ded8; padding:26px 24px; font:13px Arial,sans-serif; } footer div { max-width:1124px; margin:auto; display:flex; justify-content:space-between; gap:20px; }
            @media (max-width:800px) { .grid,.grid.two { grid-template-columns:repeat(2,1fr); } .stats { grid-template-columns:repeat(2,1fr); } } @media (max-width:520px) { header nav a:not(.logout) { display:none; } main { padding:28px 18px 60px; } .hero { padding-top:30px; } .grid,.grid.two,.form-grid { grid-template-columns:1fr; } .stats { grid-template-columns:repeat(2,1fr); } .full { grid-column:auto; } footer div { display:block; } footer span { display:block; margin-bottom:8px; } }
        </style>
    </head>
    <body>
        <header><a class="brand" href="index.php">Barangay San Isidro</a><nav><a href="user-dashboard.php?guest=1">Dashboard</a><a href="index.php#explore">Explore</a><?php if ($currentUser): ?><a class="logout" href="bootstrap/logout.php">Log out</a><?php else: ?><a class="logout" href="auth.php">Login / register</a><?php endif; ?></nav></header>
        <main><a class="breadcrumb" href="index.php#explore"><i class="fa-solid fa-arrow-left"></i> Back to Explore</a><section class="hero"><div class="hero-icon"><i class="fa-solid <?= publicText($icon) ?>"></i></div><div><h1><?= $safeTitle ?></h1><p><?= $safeSubtitle ?></p></div></section>
    <?php
}

function publicFooter(): void
{
    ?>
        </main><footer><div><span>Barangay San Isidro Information Office</span><span>Open Monday to Friday, 8:00 AM - 5:00 PM</span></div></footer>
    </body></html>
    <?php
}