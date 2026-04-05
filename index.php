<!-- index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="core/jq.js"></script>
    <base href="/LIBRARYMANAGEMENTSYSTEMFINAL/">
    <title>Login – Library</title>
</head>
<body>

<h1 id="title">MOFO"S Library Management System</h1>

<div id="error_text" style="color:red; display:none;"></div>

<h2>Login</h2>

<div id="loginForm">
    <input type="text" id="email" placeholder="Email">
    <br><br>
    <input type="password" id="password" placeholder="Password">
    <br><br>
    <button id="loginBtn">Login</button>
</div>

<script src="app/Views/Auth/logAuth.js"></script>

</body>
</html>