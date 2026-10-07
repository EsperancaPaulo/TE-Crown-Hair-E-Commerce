document.addEventListener("DOMContentLoaded", function() {

    const loginForm =
        document.getElementById("loginForm");


    loginForm.addEventListener("submit", function(event) {

        event.preventDefault();


        const email =
            document.getElementById("loginEmail").value.trim();

        const password =
            document.getElementById("loginPassword").value.trim();


        if (email === "" || password === "") {

            alert("Please enter your email address and password.");

            return;
        }


        if (!email.includes("@")) {

            alert("Please enter a valid email address.");

            return;
        }


        alert("Login details accepted.");

    });

});
