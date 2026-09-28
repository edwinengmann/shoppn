document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("register-form");


    // If registration form doesn't exist,
    // stop here.
    if (!form) {
        return;
    }


    form.addEventListener("submit", function (event) {

        let valid = true;


        // Get values
        const name =
            document.getElementById("customer_name")
                .value.trim();


        const email =
            document.getElementById("customer_email")
                .value.trim();


        const password =
            document.getElementById("customer_pass")
                .value;


        const country =
            document.getElementById("customer_country")
                .value.trim();


        const city =
            document.getElementById("customer_city")
                .value.trim();


        const contact =
            document.getElementById("customer_contact")
                .value.trim();


        // Regular expressions
        const emailRegex =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        const phoneRegex =
            /^[0-9+\-\s]{7,15}$/;

        const passwordRegex =
            /^(?=.*\d).{8,}$/;


        // Clear old errors
        document
            .querySelectorAll(".field-error")
            .forEach(function (element) {

                element.textContent = "";

            });


        // Name validation
        if (name.length < 2) {

            showError(
                "name-error",
                "Name must be at least 2 characters."
            );

            valid = false;
        }


        // Email validation
        if (!emailRegex.test(email)) {

            showError(
                "email-error",
                "Enter a valid email address."
            );

            valid = false;
        }


        // Password validation
        if (!passwordRegex.test(password)) {

            showError(
                "password-error",
                "Password must be at least 8 characters and contain a number."
            );

            valid = false;
        }


        // Country validation
        if (country === "") {

            showError(
                "country-error",
                "Please select your country."
            );

            valid = false;
        }


        // City validation
        if (city.length < 2) {

            showError(
                "city-error",
                "Enter a valid city."
            );

            valid = false;
        }


        // Contact validation
        if (!phoneRegex.test(contact)) {

            showError(
                "contact-error",
                "Enter a valid contact number."
            );

            valid = false;
        }


        // Stop submission if invalid
        if (!valid) {

            event.preventDefault();

            return;
        }


        // Loading state
        const button =
            document.getElementById("register-button");


        button.disabled = true;

        button.textContent = "Registering...";

    });


    function showError(id, message) {

        const element =
            document.getElementById(id);


        if (element) {

            element.textContent = message;

        }

    }

});