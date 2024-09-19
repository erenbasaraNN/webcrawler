<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading...</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-weight: normal;
            background-color: #333333;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .spinner {
            margin: 20px auto;
            width: 40px;
            height: 40px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #4CAF50;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        p {
            font-size: 45px;
            color: #f3f3f3;
        }
    </style>
</head>
<body>
<div>
    <div class="spinner"></div>
    <p>Fetching data... Please wait.</p>
</div>
<form id="loadingForm" method="POST" action="index.php">
    <input type="hidden" name="domain" value="<?php echo htmlspecialchars($_POST['domain']); ?>">
</form>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById('loadingForm');
        if (form) {
            form.submit();  // Ensure the form exists before submitting
        }
    });
</script>
</body>
</html>
