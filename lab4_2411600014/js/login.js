document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const alertBox = document.getElementById('loginAlert');

            if (username && password) {
                // Save session username
                localStorage.setItem('currentUser', username);
                // Redirect to dashboard
                window.location.href = 'dashboard.html';
            } else {
                alertBox.textContent = 'Please enter both username and password.';
                alertBox.classList.remove('d-none');
            }
        });
    }
});