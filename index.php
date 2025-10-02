<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <title>CityLens Weather</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            padding: 20px;
            color: #2d3748;
        }
        
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding-top: 40px;
        }
        
        .app-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        h1 {
            color: #1e293b;
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 8px;
            text-shadow: none;
        }
        
        .subtitle {
            color: #64748b;
            font-size: 1.1rem;
            font-weight: 300;
        }
        
        .weather-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .weather-icon-container {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
        }
        
        canvas#weather-icon {
            border-radius: 16px;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 16px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .weather-info {
            font-size: 16px;
            line-height: 1.6;
        }
        
        .weather-info p {
            margin: 12px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .weather-info p:last-child {
            border-bottom: none;
        }
        
        .weather-label {
            font-weight: 500;
            color: #4a5568;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        .weather-value {
            font-weight: 600;
            color: #2d3748;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #64748b;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .container {
                padding-top: 20px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .weather-card {
                margin: 0 10px;
                padding: 24px 20px;
            }
        }
    </style>
    <script src="skycons/skycons.js"></script>
</head>
<body>
    <div class="container">
        <div class="app-header">
            <h1>CityLens Weather</h1>
            <p class="subtitle">Real-time weather information</p>
        </div>
        
        <div class="weather-card">
            <div class="weather-icon-container">
                <canvas id="weather-icon" width="96" height="96"></canvas>
            </div>
            <div class="weather-info">
                <?php include 'src/weather.php'; ?>
            </div>
        </div>
        
        <div class="footer">
            Powered by OpenWeatherMap
        </div>
    </div>
    <script>
        const skycons = new Skycons({
            monochrome: false,
            colors: {
                main: "#667eea",       // Modern purple-blue
                sun: "#f59e0b",        // Modern amber for the sun
                moon: "#e5e7eb",       // Modern gray for the moon
                fog: "#9ca3af",        // Modern gray for fog
                fogbank: "#d1d5db",    // Light gray for fogbank
                light_cloud: "#f3f4f6", // Very light gray for light clouds
                cloud: "#d1d5db",      // Light gray for clouds
                dark_cloud: "#6b7280", // Dark gray for dark clouds
                thunder: "#7c3aed",    // Modern purple for thunder
                snow: "#f3f4f6",       // Very light gray for snow
                hail: "#60a5fa",       // Modern blue for hail
                sleet: "#60a5fa",      // Modern blue for sleet
                wind: "#9ca3af",       // Modern gray for wind
                leaf: "#10b981",       // Modern emerald for leaf
                rain: "#3b82f6"        // Modern blue for rain
            }
        });
        const weatherCondition = "<?php echo $weatherIcon; ?>"; // PHP variable for icon
        skycons.add("weather-icon", Skycons[weatherCondition]);
        skycons.play();
    </script>
</body>
</html>
