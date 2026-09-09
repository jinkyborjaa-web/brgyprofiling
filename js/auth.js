// Toggle Login/Register
document.getElementById('show-register').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('login-section').classList.remove('active');
    document.getElementById('register-section').classList.add('active');
    document.getElementById('login-message').style.display = 'none';
    document.getElementById('register-message').style.display = 'none';
});

document.getElementById('show-login').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('register-section').classList.remove('active');
    document.getElementById('login-section').classList.add('active');
    document.getElementById('login-message').style.display = 'none';
    document.getElementById('register-message').style.display = 'none';
});

// Registration (PHP)
document.getElementById('register-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const username = document.getElementById('register-username').value;
    const password = document.getElementById('register-password').value;
    const confirmPassword = document.getElementById('register-confirm-password').value;

    if (password !== confirmPassword) {
        showMessage('register-message', 'Passwords do not match', 'error');
        return;
    }

    const registerLoading = document.getElementById('register-loading');
    registerLoading.style.display = 'block';

    fetch("bootstrap/registery.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, password })
    })
    .then(res => res.json())
    .then(data => {
        console.log(data);
        registerLoading.style.display = 'none';
        showMessage('register-message', data.message, data.status);

        if (data.status === "success") {
            document.getElementById('register-form').reset();

            setTimeout(() => {
                document.getElementById('register-section').classList.remove('active');
                document.getElementById('login-section').classList.add('active');
            }, 1500);
        }
    })
    .catch(err => {
        registerLoading.style.display = 'none';
        console.error(err);
        showMessage('register-message', 'Network error. Try again.', 'error');
    });
});

// Login (PHP)
document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const username = document.getElementById('login-username').value.trim();
    const password = document.getElementById('login-password').value;

    const loginLoading = document.getElementById('login-loading');
    loginLoading.style.display = 'block';
    document.getElementById('login-message').style.display = 'none';

    fetch("bootstrap/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, password })
    })
    .then(res => res.json())
    .then(data => {
        loginLoading.style.display = 'none';
        showMessage('login-message', data.message, data.status);

        if (data.status === "success") {
            setTimeout(() => {
                window.location.href = data.redirect || "user-dashboard.php";
            }, 1000);
        }
    })
    .catch(err => {
        loginLoading.style.display = 'none';
        showMessage('login-message', 'Network error. Try again.', 'error');
    });
});

// Show message
function showMessage(elementId, message, type) {
    const msg = document.getElementById(elementId);
    msg.textContent = message;
    msg.className = `message ${type}`;
    msg.style.display = 'block';
}
