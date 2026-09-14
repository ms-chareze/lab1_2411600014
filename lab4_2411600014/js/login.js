document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");

    loginForm.addEventListener("submit", (e) => {
        e.preventDefault();
        
        const usernameInput = document.getElementById("username").value.trim();
        const passwordInput = document.getElementById("password").value.trim();

        if (usernameInput !== "" && passwordInput !== "") {
            localStorage.setItem("loggedInUser", usernameInput);
            window.location.href = "dashboard.html";
        } else {
            const alertBox = document.getElementById("loginAlert");
            if (alertBox) {
                alertBox.textContent = "Please enter both username and password.";
                alertBox.classList.remove("d-none");
            }
        }
    });
});