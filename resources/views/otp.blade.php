<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        h1 {
            color: #0F5252; /* 900 */
        }
        .code {
            display: inline-block;
            background-color: #09C3BA; /* 500 */
            padding: 10px 20px;
            font-size: 20px;
            border-radius: 5px;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="{{ asset('public/imgs/photo_2024-11-27_19-05-52.jpg') }}" alt="Mate Order Logo"
            width="150">
        </div>
        <h1>Verify Your Account</h1>
        <p>Hello {{$firstName}}</p>
        <p>We received a request to set up email for your account. Please use the following code to verify your email address:</p>
        <div class="code">{{$code}}</div>
        <p>If you did not request this, please ignore this email.</p>
        <p>Thank you!</p>
    </div>
</body>
</html>
