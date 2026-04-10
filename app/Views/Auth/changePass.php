<?php
session_start();

if (!isset($_SESSION['StaffID']) && !isset($_SESSION['MemberID'])) {
    header('Location: /index.php');
    exit();
}

if (isset($_SESSION['DefaultPassword']) && $_SESSION['DefaultPassword'] == 0) {
    
    switch ($_SESSION['Role']) {
            case 'Admin':
                $redirect = 'app/Views/Dashboards/adminDashboard.php';
                break;
            case 'CirculationLibrarian':
                $redirect = 'app/Views/Circulation/manageBorrows.php';
                break;
            case 'DataAnalyst':
                $redirect = 'app/Views/Dashboards/analystDashboard.php';
                break;
            case 'Member':
                echo json_encode(['success' => true, 'redirect' => 'app/Views/Dashboards/memberDashboard.php']);
                break;
            default:
                $redirect = '/index.php';
                break;
        }

        echo json_encode(['success' => true, 'redirect' => $redirect]);
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
    <link rel="stylesheet" href="app/Views/CSS/changep.css">

    <title>Change Password</title>
</head>
<body>
    <div class="brand">Library Management System</div>
    <div class="box">
        <h2>Change Password</h2>
        <p>You must set a new password before continuing.</p>

        <label>New Password</label>
        <input type="password" id="newpass" placeholder="At least 7 characters">

        <label>Confirm Password</label>
        <input type="password" id="conpass" placeholder="Confirm password">

        <button id="changebtn">Change Password</button>

        <div id="error_text"></div>
        <div id="success_text"></div>
    </div>

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
                    type: 'POST', dataType: 'json',
                    data: { action: 'changepass', newpass: newpass, conpass: conpass },
                    success: function (resp) {
                        if (resp.success) {
                            $("#success_text").text("Password changed! Redirecting...").fadeIn();
                            setTimeout(() => { window.location.href = resp.redirect; }, 1000);
                        } else {
                            $("#error_text").text(resp.message).fadeIn();
                        }
                    },
                    error: function () {
                        $("#error_text").text("Something went wrong. Try again.").fadeIn();
                    }
                });
            });

            $('input').on('keyup', function(e) {
                if (e.key === 'Enter') $('#changebtn').click();
            });
        });
    </script>
</body>
</html>