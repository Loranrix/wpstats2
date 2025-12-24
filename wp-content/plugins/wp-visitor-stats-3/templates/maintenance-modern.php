<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo('name'); ?> - Maintenance</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #0a0e27;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            overflow: hidden;
            position: relative;
        }
        .maintenance-container {
            text-align: center;
            padding: 40px;
            max-width: 700px;
            z-index: 1;
        }
        .maintenance-icon {
            width: 250px;
            height: 250px;
            margin: 0 auto 40px;
        }
        h1 {
            font-size: 3.5em;
            margin-bottom: 20px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        p {
            font-size: 1.3em;
            line-height: 1.8;
            opacity: 0.8;
            margin-bottom: 15px;
        }
        .grid-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            opacity: 0.1;
        }
    </style>
</head>
<body>
    <svg class="grid-bg" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#fff" stroke-width="1"/>
            </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#grid)"/>
    </svg>
    <div class="maintenance-container">
        <div class="maintenance-icon">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                    </linearGradient>
                </defs>
                <circle cx="100" cy="100" r="90" fill="url(#grad1)" opacity="0.2"/>
                <path d="M100 40 L100 100 L150 150" stroke="url(#grad1)" stroke-width="10" stroke-linecap="round" fill="none">
                    <animate attributeName="stroke-dasharray" values="0,200;100,200;0,200" dur="2s" repeatCount="indefinite"/>
                </path>
                <circle cx="100" cy="100" r="12" fill="url(#grad1)"/>
                <path d="M50 100 L100 100" stroke="url(#grad1)" stroke-width="8" stroke-linecap="round"/>
            </svg>
        </div>
        <h1>Under Maintenance</h1>
        <p>We're working hard to improve your experience.</p>
        <p style="font-size: 1em; opacity: 0.6;">Please check back soon.</p>
    </div>
</body>
</html>

