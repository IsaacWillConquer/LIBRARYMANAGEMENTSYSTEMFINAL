$(document).ready(function () {

    $('#loginBtn').click(login);

    $('input').on('keyup', function (e) {
        if (e.key === 'Enter') $('#loginBtn').click();
    });

    function showError(msg) {
        $("#error_text").text(msg).fadeIn();
    }

    function hideError() {
        $("#error_text").hide();
    }

    function login() {
        hideError();

        let email = $("#email").val().trim();
        let password = $("#password").val().trim();

        if (!email || !password) {
            showError("Email and password are required.");
            return;
        }

        $.ajax({
            url: 'app/Controllers/authController.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'loginDaUser', email: email, password: password },
            success: function (resp) {
                if (resp.success === true) {
                    showError("Login successful! Redirecting...");
                    $("#error_text").css("color", "green");

                    //dramatic effect wowow
                    setTimeout(() => {
                        window.location.href = resp.redirect;
                    }, 1000);
                } else {
                    showError(resp.message || "Login failed. Please try again.");
                }
            },
            error: function () {
                showError("Something went wrong. Try again.");
            }
        });
    }

});

function logout() {
    $('#logoutText').fadeIn();
    //dramatic exit type shi
    setTimeout(() => {
        window.location.href = '/LIBRARYMANAGEMENTSYSTEMFINAL/logout.php';
    }, 1500);
}