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
            text-align: center; /* Center-align content */
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .weather-info {
            font-size: 18px;
            line-height: 1.6;
            margin-top: 10px;
        }
        canvas#weather-icon {
            display: block;
            margin: 0 auto; /* Center the canvas */
        }
    </style>
    <script src="skycons/skycons.js"></script>
</head>
<body>
    <h1>CityLens Weather</h1>
    <div class="weather-card">
        <canvas id="weather-icon" width="128" height="128"></canvas>
        <div class="weather-info">
            <?php include 'src/weather.php'; ?>
        </div>
    </div>
    <script>
        const skycons = new Skycons({
            monochrome: false,
            colors: {
                main: "#111",
                sun: "#FFD700",        // Gold for the sun
                moon: "#A9A9A9",       // Dark gray for the moon
                fog: "#C6C6C6",        // Light gray for fog
                fogbank: "#B8B8B8",    // Slightly darker gray for fogbank
                light_cloud: "#D3D3D3", // Light gray for light clouds
                cloud: "#A9A9A9",      // Dark gray for clouds
                dark_cloud: "#696969",  // Darker gray for dark clouds
                thunder: "#FFD700",    // Gold for thunder
                snow: "#FFFFFF",       // White for snow
                hail: "#E0FFFF",       // Light cyan for hail
                sleet: "#E0FFFF",      // Light cyan for sleet
                wind: "#C0C0C0",       // Silver for wind
                leaf: "#32CD32",       // Lime green for leaf
                rain: "#4682B4"        // Steel blue for rain
            }
        });
        const weatherCondition = "<?php echo $weatherIcon; ?>"; // PHP variable for icon
        skycons.add("weather-icon", Skycons[weatherCondition]);
        skycons.play();
    </script>
</body>
</html>
