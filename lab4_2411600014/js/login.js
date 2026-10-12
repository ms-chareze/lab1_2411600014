document.addEventListener("DOMContentLoaded", () => { // Waits for the HTML document to fully load before running script
    const loginForm = document.getElementById("loginForm"); // Finds the login form element by its ID

    loginForm.addEventListener("submit", (e) => { // Listens for when the login form is submitted
        e.preventDefault(); // Prevents the default form submission page reload
        
        const usernameInput = document.getElementById("username").value.trim(); // Gets and trims the typed username value
        const passwordInput = document.getElementById("password").value.trim(); // Gets and trims the typed password value
        const alertBox = document.getElementById("loginAlert"); // Finds the alert box element for error messages

        // Enforcing Lab 3 credentials
        const validUsername = "admin"; // Sets the required correct username
        const validPassword = "password123"; // Sets the required correct password

        if (usernameInput === validUsername && passwordInput === validPassword) { // Checks if typed username and password are correct
            localStorage.setItem("isLoggedIn", "true"); // Saves login status as true in browser storage
            localStorage.setItem("user", usernameInput); // Saves the logged-in username in browser storage
            
            if (alertBox) { // Checks if the alert box element exists
                alertBox.classList.add("d-none"); // Hides the alert box by adding Bootstrap's d-none class
            } // Ends alertBox check block

            window.location.href = "dashboard.html"; // Redirects user to the dashboard page upon successful login
        } else { // Runs if username or password is incorrect
            if (alertBox) { // Checks if the alert box element exists
                alertBox.textContent = "Invalid username or password. Use 'admin' and 'password123'."; // Sets the error message text
                alertBox.classList.remove("d-none"); // Shows the alert box by removing the d-none class
            } // Ends alertBox check block
        } // Ends if-else condition block
    }); // Ends form submit event listener
}); // Ends DOMContentLoaded event listener