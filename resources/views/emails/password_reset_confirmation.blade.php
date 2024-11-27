<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #EFFEF8; /* 50 */
            color: #087D7B; /* 700 */
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #C9FEF6; /* 100 */
            border-radius: 8px;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        h3 {
            color: #0F5252; /* 900 */
        }
        .btn {
            display: inline-block;
            background-color: #09C3BA; /* 500 */
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="{{ asset('imgs/photo_2024-11-27_19-05-52.jpg') }}" alt="Mate Order Logo" width="150">
        </div>
        <h3>Hello,</h3>
        <p>We received a request to reset your password. If you made this request, click the link below to confirm:</p>
        <a href="{{ $resetLink }}" class="btn">Yes, reset my password</a>
        <p>If you did not request this change, you can safely ignore this email.</p>
    </div>
</body>
</html>
