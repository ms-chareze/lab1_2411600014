document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");

    loginForm.addEventListener("submit", (e) => {
        e.preventDefault();
        
        const usernameInput = document.getElementById("username").value.trim();
        const passwordInput = document.getElementById("password").value.trim();
        const alertBox = document.getElementById("loginAlert");

        // Enforcing Lab 3 credentials
        const validUsername = "admin";
        const validPassword = "password123";

        if (usernameInput === validUsername && passwordInput === validPassword) {
            localStorage.setItem("isLoggedIn", "true");
            localStorage.setItem("user", usernameInput);
            
            if (alertBox) {
                alertBox.classList.add("d-none");
            }

            window.location.href = "dashboard.html";
        } else {
            if (alertBox) {
                alertBox.textContent = "Invalid username or password. Use 'admin' and 'password123'.";
                alertBox.classList.remove("d-none");
            }
        }
    });
});