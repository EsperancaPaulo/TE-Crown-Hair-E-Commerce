document.addEventListener("DOMContentLoaded", function() {

    const contactForm =
        document.getElementById("contactForm");


    contactForm.addEventListener("submit", function(event) {

        event.preventDefault();


        const name =
            document.getElementById("contactName").value.trim();

        const email =
            document.getElementById("contactEmail").value.trim();

        const subject =
            document.getElementById("contactSubject").value.trim();

        const message =
            document.getElementById("contactMessage").value.trim();


        if (
            name === "" ||
            email === "" ||
            subject === "" ||
            message === ""
        ) {

            alert("Please complete all required fields.");

            return;
        }


        if (!email.includes("@")) {

            alert("Please enter a valid email address.");

            return;
        }


        alert(
            "Thank you, " +
            name +
            "! Your message has been submitted successfully."
        );


        contactForm.reset();

    });

});
