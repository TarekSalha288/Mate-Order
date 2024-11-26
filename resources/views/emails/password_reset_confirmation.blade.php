<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Confirmation</title>
</head>
<body>
    <h3>Hello,</h3>
    <p>We received a request to reset your password. If you made this request, click the link below to confirm:</p>
    <a href="{{ $resetLink }}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Yes, reset my password</a>
    <p>If you did not request this change, you can safely ignore this email.</p>
</body>
</html>
