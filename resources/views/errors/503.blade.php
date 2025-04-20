<!DOCTYPE html>
<html>
<head>
    <title>Site Maintenance</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        html, body {
            font-family: 'DM Sans', Arial, sans-serif;
            height: 100%;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            color: #333;
            text-align: center;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 0 20px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 30px;
        }
        h1 {
            font-size: 36px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        p {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
            max-width: 600px;
        }
        .countdown {
            font-size: 24px;
            font-weight: bold;
            margin: 30px 0;
            color: #e74c3c;
        }
        .social-links {
            margin-top: 40px;
        }
        .social-links a {
            margin: 0 10px;
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{ asset('logo.png') }}" alt="Company Logo" class="logo">
        <h1>We're Upgrading Our Site</h1>
        <p>We are currently performing scheduled maintenance. We should be back shortly. Thank you for your patience!</p>

        <div class="countdown">
            Expected completion: <span id="countdown">5 hours</span>
        </div>


    </div>
</body>
</html>