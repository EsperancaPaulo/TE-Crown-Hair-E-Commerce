document.addEventListener("DOMContentLoaded", function() {

    const registerForm =
        document.getElementById("registerForm");


    registerForm.addEventListener("submit", function(event) {

        event.preventDefault();


        const name =
            document.getElementById("registerName").value.trim();

        const email =
            document.getElementById("registerEmail").value.trim();

        const password =
            document.getElementById("registerPassword").value;

        const confirmPassword =
            document.getElementById("confirmPassword").value;

        const terms =
            document.getElementById("terms").checked;


        if (
            name === "" ||
            email === "" ||
            password === "" ||
            confirmPassword === ""
        ) {

            alert("Please complete all required fields.");

            return;
        }


        if (!email.includes("@")) {

            alert("Please enter a valid email address.");

            return;
        }


        if (password.length < 6) {

            alert("Password must contain at least 6 characters.");

            return;
        }


        if (password !== confirmPassword) {

            alert("Passwords do not match.");

            return;
        }


        if (!terms) {

            alert("Please agree to the terms and conditions.");

            return;
        }


        alert(
            "Account details accepted successfully!"
        );

    });

});
