<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="favicon.ico" type="image/x-icon">
    <title>CityLens Weather</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .weather-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .weather-info {
            font-size: 18px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <h1>CityLens Weather</h1>
    <div class="weather-card">
        <div class="weather-info">
            <?php include 'src/weather.php'; ?>
        </div>
    </div>
</body>
</html>