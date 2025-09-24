<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="favicon.ico" type="image/x-icon">
    <title>MS-DOS Weather v1.0</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;700&display=swap');
        
        body {
            font-family: 'Source Code Pro', 'Courier New', monospace;
            background-color: #000080;
            color: #ffffff;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            font-size: 16px;
            line-height: 1.2;
        }
        
        .dos-header {
            background-color: #00ffff;
            color: #000080;
            padding: 5px 10px;
            font-weight: bold;
            margin-bottom: 10px;
            border: 2px solid #ffffff;
        }
        
        .weather-card {
            background-color: #000080;
            border: 2px solid #ffffff;
            padding: 15px;
            margin-top: 10px;
            color: #ffffff;
        }
        
        .weather-card::before {
            content: "╔══════════════════════════════════════════════════════════════╗";
            display: block;
            color: #ffff00;
            margin-bottom: 10px;
        }
        
        .weather-card::after {
            content: "╚══════════════════════════════════════════════════════════════╝";
            display: block;
            color: #ffff00;
            margin-top: 10px;
        }
        
        h1 {
            color: #ffff00;
            text-align: center;
            font-weight: bold;
            font-size: 24px;
            margin: 0 0 20px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .weather-info {
            font-size: 16px;
            line-height: 1.4;
            margin-top: 15px;
            font-family: 'Source Code Pro', 'Courier New', monospace;
        }
        
        .weather-info p {
            margin: 8px 0;
            color: #00ffff;
        }
        
        .weather-info strong {
            color: #ffff00;
            text-transform: uppercase;
        }
        
        canvas#weather-icon {
            display: block;
            margin: 10px auto;
            border: 1px solid #ffff00;
            background-color: #000040;
        }
        
        .dos-prompt {
            color: #00ff00;
            font-weight: bold;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .dos-prompt::before {
            content: "C:\\WEATHER> ";
        }
        
        .blink {
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
    </style>
    <script src="skycons/skycons.js"></script>
</head>
<body>
    <div class="dos-header">
        MS-DOS Weather System v1.0 - Copyright (C) 1985-2025 CityLens Corp.
    </div>
    
    <h1>░▒▓ CITYLENS WEATHER STATION ▓▒░</h1>
    
    <div class="weather-card">
        <canvas id="weather-icon" width="96" height="96"></canvas>
        <div class="weather-info">
            <?php include 'src/weather.php'; ?>
        </div>
    </div>
    
    <div class="dos-prompt">
        Press any key to continue<span class="blink">_</span>
    </div>
    <script>
        const skycons = new Skycons({
            monochrome: false,
            colors: {
                main: "#FFFF00",       // DOS Yellow
                sun: "#FFFF00",        // DOS Yellow for the sun
                moon: "#FFFFFF",       // DOS White for the moon
                fog: "#00FFFF",        // DOS Cyan for fog
                fogbank: "#00FFFF",    // DOS Cyan for fogbank
                light_cloud: "#FFFFFF", // DOS White for light clouds
                cloud: "#00FFFF",      // DOS Cyan for clouds
                dark_cloud: "#FFFFFF", // DOS White for dark clouds
                thunder: "#FFFF00",    // DOS Yellow for thunder
                snow: "#FFFFFF",       // DOS White for snow
                hail: "#00FFFF",       // DOS Cyan for hail
                sleet: "#00FFFF",      // DOS Cyan for sleet
                wind: "#FFFFFF",       // DOS White for wind
                leaf: "#00FF00",       // DOS Green for leaf
                rain: "#00FFFF"        // DOS Cyan for rain
            }
        });
        const weatherCondition = "<?php echo $weatherIcon; ?>"; // PHP variable for icon
        skycons.add("weather-icon", Skycons[weatherCondition]);
        skycons.play();
    </script>
</body>
</html>
