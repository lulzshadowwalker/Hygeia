<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="success-icon">✅</div>
        <h1>Password Reset Successful!</h1>
        <p>Your password has been successfully reset. You can now log in with your new password.</p>
        <p>You may close this window and return to the app to log in.</p>
        
        <a href="#" class="btn" onclick="window.close(); return false;">Close Window</a>
    </div>

    <script>
        // Auto-close after 5 seconds if this is a popup window
        if (window.opener) {
            setTimeout(() => {
                window.close();
            }, 5000);
        }
    </script>
</body>
</html>
