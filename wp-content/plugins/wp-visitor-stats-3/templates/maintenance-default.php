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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            overflow: hidden;
        }
        .maintenance-container {
            text-align: center;
            padding: 40px;
            max-width: 600px;
            z-index: 1;
        }
        .maintenance-icon {
            width: 200px;
            height: 200px;
            margin: 0 auto 30px;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        h1 {
            font-size: 3em;
            margin-bottom: 20px;
            font-weight: 700;
        }
        p {
            font-size: 1.2em;
            line-height: 1.6;
            opacity: 0.9;
        }
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="particles">
        <svg width="100%" height="100%">
            <circle cx="10%" cy="20%" r="3" fill="rgba(255,255,255,0.3)" opacity="0.5">
                <animate attributeName="cy" values="20%;80%;20%" dur="8s" repeatCount="indefinite"/>
            </circle>
            <circle cx="30%" cy="60%" r="4" fill="rgba(255,255,255,0.2)" opacity="0.5">
                <animate attributeName="cy" values="60%;10%;60%" dur="10s" repeatCount="indefinite"/>
            </circle>
            <circle cx="70%" cy="30%" r="2" fill="rgba(255,255,255,0.4)" opacity="0.5">
                <animate attributeName="cy" values="30%;90%;30%" dur="12s" repeatCount="indefinite"/>
            </circle>
            <circle cx="90%" cy="70%" r="5" fill="rgba(255,255,255,0.25)" opacity="0.5">
                <animate attributeName="cy" values="70%;20%;70%" dur="9s" repeatCount="indefinite"/>
            </circle>
        </svg>
    </div>
    <div class="maintenance-container">
        <div class="maintenance-icon">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="100" cy="100" r="80" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.5)" stroke-width="3"/>
                <path d="M100 50 L100 100 L140 140" stroke="white" stroke-width="8" stroke-linecap="round"/>
                <circle cx="100" cy="100" r="10" fill="white"/>
                <path d="M60 100 L100 100" stroke="white" stroke-width="6" stroke-linecap="round"/>
            </svg>
        </div>
        <h1>We'll be back soon!</h1>
        <p>We're currently performing some maintenance. We'll be back online shortly.</p>
        <p style="margin-top: 20px; font-size: 0.9em; opacity: 0.7;">Thank you for your patience.</p>
    </div>
</body>
</html>

