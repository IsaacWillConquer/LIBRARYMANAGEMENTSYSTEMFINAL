<?php
session_start();

if (!isset($_SESSION['StaffID']) && !isset($_SESSION['MemberID'])) {
    header('Location: /index.php');
    exit();
}

if (isset($_SESSION['DefaultPassword']) && $_SESSION['DefaultPassword'] == 0) {
    if (isset($_SESSION['StaffID'])) {
        switch ($_SESSION['Role']) {
            case 'Admin':
                header('Location: /LIBRARYMANAGEMENTSYSTEMFINAL/app/Views/Dashboards/adminDashboard.php');
                break;
            case 'CirculationLibrarian':
            case 'MaterialLibrarian':
                header('Location: /LIBRARYMANAGEMENTSYSTEMFINAL/app/Views/Dashboards/circulationDashboard.php');
                break;
            case 'DataAnalyst':
                header('Location: /LIBRARYMANAGEMENTSYSTEMFINAL/app/Views/Dashboards/analystDashboard.php');
                break;
            default:
                header('Location: /LIBRARYMANAGEMENTSYSTEMFINAL/index.php');
        }
    } else {
        header('Location: /LIBRARYMANAGEMENTSYSTEMFINAL/app/Views/Dashboards/memberDashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/LIBRARYMANAGEMENTSYSTEMFINAL/">
    <script src="core/jq.js"></script>
    <title>Change Password</title>
</head>
<body>
    <h1>Library Management System</h1>
    <h2>Change Password</h2>
    <p>You must change your password before continuing.</p>

    <div id="error_text" style="color:red; display:none;"></div>
    <div id="success_text" style="color:green; display:none;"></div>

    <label>New Password</label><br>
    <input type="password" id="newpass" placeholder="New password"><br><br>

    <label>Confirm Password</label><br>
    <input type="password" id="conpass" placeholder="Confirm password"><br><br>

    <button id="changebtn">Change Password</button>

    <script>
        $(document).ready(function () {
            $('#changebtn').click(function () {
                $("#error_text").hide();
                $("#success_text").hide();

                let newpass = $("#newpass").val();
                let conpass = $("#conpass").val();

                if (!newpass || !conpass) {
                    $("#error_text").text("All fields are required.").fadeIn();
                    return;
                }

                if (newpass.length < 7) {
                    $("#error_text").text("Password must be at least 7 characters.").fadeIn();
                    return;
                }

                if (newpass !== conpass) {
                    $("#error_text").text("Passwords do not match.").fadeIn();
                    return;
                }

                $.ajax({
                    url: 'app/Controllers/authController.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { action: 'changepass', newpass: newpass, conpass: conpass },
                    success: function (resp) {
                        if (resp.success === true) {
                            $("#success_text").text("Password changed! Redirecting...").fadeIn();
                            setTimeout(() => {
                                window.location.href = resp.redirect;
                            }, 1000);
                        } else {
                            $("#error_text").text(resp.message).fadeIn();
                        }
                    },
                    error: function () {
                        $("#error_text").text("Something went wrong. Try again.").fadeIn();
                    }
                });
            });
        });
    </script>
</body>
</html>