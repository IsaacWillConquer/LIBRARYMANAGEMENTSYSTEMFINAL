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
    <title>Change Password</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; color: #222; font-size: .85rem; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .box { background: #fff; border-radius: 5px; padding: 28px 26px; width: 340px; }
        .box h2 { font-size: 1rem; color: #1a2744; margin-bottom: 6px; }
        .box p  { font-size: .82rem; color: #777; margin-bottom: 18px; }
        .box label { display: block; font-size: .78rem; font-weight: bold; color: #666; margin-bottom: 3px; margin-top: 10px; }
        .box input {
            width: 100%; padding: 7px 9px; border: 1px solid #ccc; border-radius: 3px;
            font-size: .84rem; font-family: Arial, sans-serif;
        }
        .box input:focus { outline: none; border-color: #4e73df; }
        #changebtn {
            width: 100%; margin-top: 16px; background: #1a2744; color: #fff;
            border: none; border-radius: 3px; padding: 9px;
            font-size: .88rem; font-family: Arial, sans-serif; cursor: pointer;
        }
        #changebtn:hover { background: #243358; }
        #error_text   { display:none; margin-top:10px; padding:7px 9px; border-radius:3px; font-size:.82rem; background:#fdecea; color:#c0392b; }
        #success_text { display:none; margin-top:10px; padding:7px 9px; border-radius:3px; font-size:.82rem; background:#edf7f1; color:#1a7a4a; }
        .brand { font-size: .82rem; color: #aaa; margin-bottom: 16px; }
    </style>
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