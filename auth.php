<?php
session_start();
$returnTo = $_GET['return'] ?? '';
if (!preg_match('/^[a-z0-9-]+(?:\.php)?(?:#[a-z0-9_-]+)?$/i', $returnTo)) {
    $returnTo = '';
}
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($returnTo ?: (($_SESSION['role'] ?? 'user') === 'admin' ? 'dashboard.php' : 'user-dashboard.php')));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account access | Barangay San Isidro</title>
    <style>
        :root { --navy:#103b4a; --teal:#087f7b; --gold:#f3bf62; --paper:#f7fbf8; --muted:#62777a; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px; background:linear-gradient(135deg,#d8f2e7,var(--paper) 55%,#f3e4bd); color:#17323a; font-family:Arial,sans-serif; }
        .shell { width:min(920px,100%); display:grid; grid-template-columns:1fr 1fr; background:white; border:1px solid #dbe9e2; box-shadow:0 24px 60px rgba(16,59,74,.14); }
        .intro { background:var(--navy); color:white; padding:44px; display:flex; flex-direction:column; justify-content:space-between; min-height:560px; }
        .intro h1 { font:500 42px/1 Georgia,serif; margin:28px 0 16px; }
        .intro p { color:#d7e8e4; line-height:1.6; }
        .seal { color:var(--gold); font-size:27px; }
        .form { padding:44px; }
        .tabs { display:flex; gap:22px; border-bottom:1px solid #dbe9e2; margin-bottom:30px; }
        .tabs button { border:0; background:none; padding:0 0 13px; color:var(--muted); font-weight:700; cursor:pointer; }
        .tabs button.active { color:var(--teal); border-bottom:2px solid var(--teal); }
        h2 { font:500 28px Georgia,serif; margin:0 0 10px; color:var(--navy); }
        .form p { color:var(--muted); font-size:14px; line-height:1.5; }
        form { margin-top:26px; }
        label { display:block; color:var(--navy); font-size:13px; font-weight:700; margin:18px 0 7px; }
        input { width:100%; padding:13px 14px; border:1px solid #cbded5; border-radius:3px; font-size:15px; }
        input:focus { outline:2px solid #a9d8c7; border-color:var(--teal); }
        .submit { width:100%; margin-top:24px; padding:14px; border:0; border-radius:3px; background:var(--teal); color:white; font-weight:700; cursor:pointer; }
        .guest { display:block; text-align:center; margin-top:18px; color:var(--teal); font-size:14px; }
        .message { display:none; margin-top:16px; padding:12px; background:#fde8e5; color:#9a332b; font-size:13px; }
        @media (max-width:700px) { .shell { grid-template-columns:1fr; } .intro { min-height:0; padding:30px; } .intro h1 { font-size:34px; } .form { padding:30px; } }
    </style>
</head>
<body>
    <div class="shell">
        <section class="intro"><div><div class="seal">SAN ISIDRO / COMMUNITY OFFICE</div><h1>Stay close to what matters.</h1><p>Create an account to save information, subscribe to alerts, and submit requests online.</p></div><a href="index.php" style="color:#f3bf62">Return to public home</a></section>
        <section class="form">
            <div class="tabs"><button class="active" data-target="login">Login</button><button data-target="register">Create account</button></div>
            <div id="login"><h2>Welcome back</h2><p>Use your username and password to continue to your dashboard.</p><div class="message" id="login-message"></div><form id="login-form"><label for="login-username">Username</label><input id="login-username" name="username" required autocomplete="username"><label for="login-password">Password</label><input id="login-password" name="password" type="password" required autocomplete="current-password"><button class="submit" type="submit">Login to account</button></form></div>
            <div id="register" hidden><h2>Join the portal</h2><p>Registration is free. Your account lets you keep track of community requests.</p><div class="message" id="register-message"></div><form id="register-form"><label for="register-username">Username</label><input id="register-username" name="username" required minlength="3" autocomplete="username"><label for="register-password">Password</label><input id="register-password" name="password" type="password" required minlength="8" autocomplete="new-password"><label for="register-confirm">Confirm password</label><input id="register-confirm" name="confirm_password" type="password" required minlength="8" autocomplete="new-password"><button class="submit" type="submit">Create account</button></form></div>
            <a class="guest" href="user-dashboard.php?guest=1">Continue as a guest</a>
        </section>
    </div>
    <script>
        const tabs = document.querySelectorAll('.tabs button');
        tabs.forEach(tab => tab.addEventListener('click', () => { tabs.forEach(item => item.classList.remove('active')); tab.classList.add('active'); document.getElementById('login').hidden = tab.dataset.target !== 'login'; document.getElementById('register').hidden = tab.dataset.target !== 'register'; }));
        async function submitAuth(form, endpoint, messageId) { const message = document.getElementById(messageId); message.style.display = 'none'; try { const response = await fetch(endpoint, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(Object.fromEntries(new FormData(form))) }); const data = await response.json(); if (data.status === 'success') { window.location.href = data.redirect || <?= json_encode($returnTo ?: 'user-dashboard.php') ?>; return; } message.textContent = data.message || 'Please check your details.'; } catch (error) { message.textContent = 'The service is temporarily unavailable. Please try again.'; } message.style.display = 'block'; }
        document.getElementById('login-form').addEventListener('submit', event => { event.preventDefault(); submitAuth(event.currentTarget, 'bootstrap/login.php', 'login-message'); });
        document.getElementById('register-form').addEventListener('submit', event => { event.preventDefault(); const data = new FormData(event.currentTarget); if (data.get('password') !== data.get('confirm_password')) { const message = document.getElementById('register-message'); message.textContent = 'Passwords do not match.'; message.style.display = 'block'; return; } submitAuth(event.currentTarget, 'bootstrap/registery.php', 'register-message'); });
    </script>
</body>
</html>