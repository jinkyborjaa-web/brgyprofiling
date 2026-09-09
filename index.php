<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ' . (($_SESSION['role'] ?? 'user') === 'admin' ? 'dashboard.php' : 'user-dashboard.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay San Isidro | Community Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --navy:#103b4a; --teal:#087f7b; --mint:#d8f2e7; --gold:#f3bf62; --ink:#17323a; --muted:#62777a; --paper:#f7fbf8; }
        * { box-sizing:border-box; }
        body { margin:0; color:var(--ink); font-family: Georgia, 'Times New Roman', serif; background:var(--paper); }
        .topbar { max-width:1180px; margin:auto; padding:24px 28px; display:flex; align-items:center; justify-content:space-between; gap:20px; }
        .brand { display:flex; align-items:center; gap:12px; color:var(--navy); text-decoration:none; font-weight:bold; }
        .seal { width:42px; height:42px; border:2px solid var(--gold); border-radius:50%; display:grid; place-items:center; color:var(--teal); }
        nav { display:flex; align-items:center; gap:24px; font:600 14px Arial, sans-serif; }
        nav a { color:var(--ink); text-decoration:none; }
        .outline { border:1px solid var(--teal); color:var(--teal); padding:10px 16px; border-radius:4px; }
        .hero { background:var(--navy); color:white; padding:76px max(28px, calc((100% - 1124px) / 2)); position:relative; overflow:hidden; }
        .hero:after { content:''; position:absolute; width:420px; height:420px; border:1px solid rgba(243,191,98,.45); border-radius:50%; right:-110px; top:-170px; box-shadow:0 0 0 34px rgba(243,191,98,.06), 0 0 0 70px rgba(243,191,98,.04); }
        .hero-copy { max-width:700px; position:relative; z-index:1; }
        .eyebrow { color:var(--gold); font:700 12px Arial, sans-serif; letter-spacing:2px; text-transform:uppercase; }
        h1 { font-size:clamp(42px,6vw,76px); line-height:.98; margin:18px 0; font-weight:500; }
        .hero p { color:#d7e8e4; font:18px/1.6 Arial, sans-serif; max-width:610px; }
        .actions { display:flex; flex-wrap:wrap; gap:12px; margin-top:30px; }
        .button { display:inline-block; padding:14px 20px; border-radius:4px; text-decoration:none; font:700 14px Arial, sans-serif; }
        .primary { background:var(--gold); color:var(--navy); }
        .secondary { border:1px solid rgba(255,255,255,.45); color:white; }
        main { max-width:1180px; margin:auto; padding:70px 28px; }
        .section-head { display:flex; justify-content:space-between; align-items:end; gap:20px; margin-bottom:28px; }
        h2 { color:var(--navy); font-size:32px; font-weight:500; margin:0; }
        .section-head p { color:var(--muted); font:15px Arial, sans-serif; max-width:430px; line-height:1.5; }
        .grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
        .tile { background:white; border:1px solid #dbe9e2; padding:26px 22px; min-height:210px; text-decoration:none; color:var(--ink); transition:transform .2s, box-shadow .2s; }
        .tile:hover { transform:translateY(-4px); box-shadow:0 16px 30px rgba(16,59,74,.1); }
        .tile i { color:var(--teal); font-size:23px; }
        .tile h3 { font-size:21px; font-weight:500; margin:30px 0 10px; }
        .tile p { color:var(--muted); font:14px/1.55 Arial, sans-serif; }
        .lower { display:grid; grid-template-columns:1.2fr .8fr; gap:18px; margin-top:70px; }
        .panel { padding:28px; border:1px solid #dbe9e2; background:white; }
        .panel h3 { margin:0 0 18px; font-size:22px; font-weight:500; }
        .news { display:flex; gap:16px; padding:14px 0; border-top:1px solid #e7efeb; }
        .date { color:var(--teal); font:700 12px Arial, sans-serif; min-width:70px; }
        .news strong { font-weight:500; }
        .news p { color:var(--muted); font:13px/1.4 Arial, sans-serif; margin:6px 0 0; }
        footer { background:var(--navy); color:#c8ded8; padding:26px 28px; font:13px Arial, sans-serif; }
        footer div { max-width:1124px; margin:auto; display:flex; justify-content:space-between; gap:20px; }
        @media (max-width:800px) { nav a:not(.outline) { display:none; } .grid { grid-template-columns:repeat(2,1fr); } .lower { grid-template-columns:1fr; } }
        @media (max-width:480px) { .topbar { padding:18px; } .hero { padding:58px 18px; } main { padding:52px 18px; } .grid { grid-template-columns:1fr; } h1 { font-size:46px; } footer div { display:block; } }
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="index.php"><span class="seal"><i class="fa-solid fa-leaf"></i></span><span>Barangay San Isidro</span></a>
        <nav><a href="#explore">Explore</a><a href="#updates">Updates</a><a class="outline" href="#access">Login to Account?</a></nav>
    </header>
    <section class="hero">
        <div class="hero-copy">
            <div class="eyebrow">A connected community, made visible</div>
            <h1>Information and services, close to home.</h1>
            <p>Find barangay programs, public records, community updates, and everyday services in one clear place.</p>
            <div class="actions" id="access"><a class="button primary" href="user-dashboard.php?guest=1">Browse as guest <i class="fa-solid fa-arrow-right"></i></a><a class="button secondary" href="auth.php">Login or create an account</a></div>
        </div>
    </section>
    <main>
        <section id="explore">
            <div class="section-head"><h2>Explore the barangay</h2><p>Start with the information you need, then sign in when you are ready to save items or submit a request.</p></div>
            <div class="grid">
                <a class="tile" href="community-profile"><i class="fa-solid fa-landmark"></i><h3>Community profile</h3><p>History, leadership, and the people who serve San Isidro.</p></a>
                <a class="tile" href="census-statistics"><i class="fa-solid fa-chart-simple"></i><h3>Census & statistics</h3><p>Understand our population and household information.</p></a>
                <a class="tile" href="services-programs"><i class="fa-solid fa-hand-holding-heart"></i><h3>Services & programs</h3><p>Discover available programs, schedules, and contact details.</p></a>
                <a class="tile" href="official-documents"><i class="fa-solid fa-file-lines"></i><h3>Official documents</h3><p>Browse public records, ordinances, and downloadable forms.</p></a>
            </div>
        </section>
        <section class="lower" id="updates">
            <div class="panel"><h3>Latest from San Isidro</h3><div class="news"><span class="date">SEP 12</span><div><strong>Community clean-up drive</strong><p>Meet at the barangay hall at 7:00 AM. Volunteers are welcome.</p></div></div><div class="news"><span class="date">SEP 19</span><div><strong>Senior citizens' wellness day</strong><p>Free check-ups and assistance with social services.</p></div></div></div>
            <div class="panel"><h3>Need assistance?</h3><p style="font:15px/1.6 Arial,sans-serif;color:#62777a">Send a suggestion, report a concern, or request a certificate through your dashboard.</p><a class="button primary" href="user-dashboard.php?guest=1">Open the dashboard</a></div>
        </section>
    </main>
    <footer><div><span>Barangay San Isidro Information Office</span><span>Open Monday to Friday, 8:00 AM - 5:00 PM</span></div></footer>
</body>
</html>
