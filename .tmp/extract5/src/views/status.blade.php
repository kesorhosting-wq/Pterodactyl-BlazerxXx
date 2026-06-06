<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Euphoria Theme - System Status</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ $statusData['primaryColor'] ?? '#667eea' }};
            --primary-color-rgb: {{ 
                $statusData['primaryColor'] ? 
                implode(', ', [
                    hexdec(substr($statusData['primaryColor'], 1, 2)), 
                    hexdec(substr($statusData['primaryColor'], 3, 2)), 
                    hexdec(substr($statusData['primaryColor'], 5, 2))
                ]) : 
                '102, 126, 234' 
            }};
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .status-container {
            background: rgba(30, 30, 30, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            max-width: 1200px;
            width: 100%;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: var(--primary-color, #667eea);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
            box-shadow: 0 0 30px rgba(var(--primary-color-rgb, 102, 126, 234), 0.3);
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        .logo i {
            font-size: 35px;
            color: white;
        }

        .title {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .subtitle {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.7);
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .status-card {
            background: rgba(40, 40, 40, 0.8);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            border-left: 4px solid transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            background: rgba(50, 50, 50, 0.9);
        }

        .status-card.success {
            border-left-color: var(--primary-color, #27ae60);
            box-shadow: 0 5px 20px rgba(var(--primary-color-rgb, 39, 174, 96), 0.1);
        }

        .status-card.warning {
            border-left-color: #f39c12;
            box-shadow: 0 5px 20px rgba(243, 156, 18, 0.1);
        }

        .status-card.error {
            border-left-color: #e74c3c;
            box-shadow: 0 5px 20px rgba(231, 76, 60, 0.1);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
        }

        .card-icon.success {
            background: rgba(var(--primary-color-rgb, 39, 174, 96), 0.2);
            color: var(--primary-color, #27ae60);
        }

        .card-icon.warning {
            background: rgba(243, 156, 18, 0.2);
            color: #f39c12;
        }

        .card-icon.error {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            margin: 0;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .card-value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .card-value.success {
            color: var(--primary-color, #27ae60);
        }

        .card-value.warning {
            color: #f39c12;
        }

        .card-value.error {
            color: #e74c3c;
        }

        .card-description {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }

        .system-info {
            background: rgba(40, 40, 40, 0.8);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .system-info h3 {
            font-size: 20px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .system-info h3 i {
            margin-right: 10px;
            color: var(--primary-color, #667eea);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            gap: 20px;
            min-height: 40px;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            flex-shrink: 0;
            margin-right: 15px;
            min-width: 120px;
        }

        .info-value.long-text {
            font-size: 11px;
            word-break: break-all;
            hyphens: auto;
            max-width: 70%;
        }

        .info-value {
            color: rgba(255, 255, 255, 0.7);
            font-family: 'Courier New', monospace;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-all;
            text-align: right;
            max-width: 65%;
            line-height: 1.3;
            font-size: 13px;
        }

        .refresh-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: var(--primary-color, #667eea);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(var(--primary-color-rgb, 102, 126, 234), 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .refresh-button:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(var(--primary-color-rgb, 102, 126, 234), 0.4);
        }

        .refresh-button:active {
            transform: scale(0.95);
        }

        @media (max-width: 768px) {
            .status-container {
                padding: 20px;
            }

            .title {
                font-size: 24px;
            }

            .status-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1rem;
        }

        .status-badge.online {
            background: rgba(var(--primary-color-rgb, 39, 174, 96), 0.2);
            color: var(--primary-color, #27ae60);
            border: 1px solid rgba(var(--primary-color-rgb, 39, 174, 96), 0.3);
        }

        .status-badge.offline {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .animated-counter {
            animation: countUp 1s ease-out;
        }

        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="status-container">
        <div class="header">
            <div class="logo">
                <img src="/extensions/euphoriatheme/images/logo.png" alt="Euphoria Theme Logo" style="width:100%; height: 100%; object-fit: contain;">
            </div>
            <h1 class="title">Euphoria Theme Status</h1>
            <p class="subtitle">Real-time system monitoring dashboard</p>
        </div>

        <div class="status-grid">
            <!-- System Status Card -->
            <div class="status-card {{ $statusData['status'] ? 'success' : 'error' }}">
                <div class="card-header">
                    <div class="card-icon {{ $statusData['status'] ? 'success' : 'error' }}">
                        <i class="fas {{ $statusData['status'] ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                    </div>
                    <h3 class="card-title">System Status</h3>
                </div>
                <div class="card-value {{ $statusData['status'] ? 'success' : 'error' }} animated-counter">
                    {{ $statusData['status'] ? 'Online' : 'Offline' }}
                </div>
                <div class="card-description">
                    System is {{ $statusData['status'] ? 'running normally' : 'experiencing issues' }}
                    <br><span class="status-badge {{ $statusData['status'] ? 'online' : 'offline' }}">
                        <i class="fas fa-circle" style="font-size: 6px; margin-right: 5px;"></i>
                        {{ $statusData['status'] ? 'Operational' : 'Down' }}
                    </span>
                </div>
            </div>

            <!-- License Status Card -->
            <div class="status-card {{ $statusData['activated'] ? 'success' : 'warning' }}">
                <div class="card-header">
                    <div class="card-icon {{ $statusData['activated'] ? 'success' : 'warning' }}">
                        <i class="fas {{ $statusData['activated'] ? 'fa-shield-alt' : 'fa-exclamation-triangle' }}"></i>
                    </div>
                    <h3 class="card-title">License Status</h3>
                </div>
                <div class="card-value {{ $statusData['activated'] ? 'success' : 'warning' }} animated-counter">
                    {{ $statusData['activated'] ? 'Licensed' : 'Unlicensed' }}
                </div>
                <div class="card-description">
                    @if($statusData['activated'])
                        Valid license detected
                        @if($statusData['licenseKey'])
                            <br><small>Key: {{ $statusData['licenseKey'] }}</small>
                        @endif
                    @else
                        No valid license found
                    @endif
                </div>
            </div>

            <!-- Server Uptime Card -->
            <div class="status-card success">
                <div class="card-header">
                    <div class="card-icon success">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="card-title">Server Uptime</h3>
                </div>
                <div class="card-value success animated-counter">
                    {{ $statusData['uptime']['formatted'] ?? 'Unknown' }}
                </div>
                <div class="card-description">
                    System has been running for 
                    {{ $statusData['uptime']['seconds'] ?? 0 }} seconds
                </div>
            </div>

            <!-- Response Time Card -->
            <div class="status-card {{ $statusData['ping'] && $statusData['ping'] < 100 ? 'success' : ($statusData['ping'] && $statusData['ping'] < 300 ? 'warning' : 'error') }}">
                <div class="card-header">
                    <div class="card-icon {{ $statusData['ping'] && $statusData['ping'] < 100 ? 'success' : ($statusData['ping'] && $statusData['ping'] < 300 ? 'warning' : 'error') }}">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <h3 class="card-title">Response Time</h3>
                </div>
                <div class="card-value {{ $statusData['ping'] && $statusData['ping'] < 100 ? 'success' : ($statusData['ping'] && $statusData['ping'] < 300 ? 'warning' : 'error') }} animated-counter">
                    {{ $statusData['ping'] ? $statusData['ping'] . 'ms' : 'N/A' }}
                </div>
                <div class="card-description">
                    Database connectivity test
                    <br><small>
                        @if($statusData['ping'] && $statusData['ping'] < 100)
                            Excellent performance
                        @elseif($statusData['ping'] && $statusData['ping'] < 300)
                            Good performance
                        @else
                            Needs attention
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="system-info">
            <h3><i class="fas fa-info-circle"></i> System Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Domain:</span>
                    <span class="info-value">{{ $statusData['siteDomain'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Site URL:</span>
                    <span class="info-value">{{ $statusData['siteUrl'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Server Time:</span>
                    <span class="info-value">{{ $statusData['serverTime'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">PHP Version:</span>
                    <span class="info-value">{{ $statusData['phpVersion'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Laravel Version:</span>
                    <span class="info-value">{{ $statusData['laravelVersion'] }}</span>
                </div>
                @if($statusData['hwid'])
                <div class="info-item">
                    <span class="info-label">Hardware ID:</span>
                    <span class="info-value long-text">{{ $statusData['hwid'] }}</span>
                </div>
                @endif
                @if($statusData['productId'])
                <div class="info-item">
                    <span class="info-label">Product ID:</span>
                    <span class="info-value">{{ $statusData['productId'] }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <button class="refresh-button" onclick="window.location.reload()" title="Refresh Status">
        <i class="fas fa-sync-alt"></i>
    </button>

    <script>
        // Auto-refresh every 30 seconds
        setInterval(() => {
            window.location.reload();
        }, 30000);

        // Animate counters on load
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.animated-counter');
            counters.forEach(counter => {
                counter.style.animationDelay = Math.random() * 0.5 + 's';
            });
        });
    </script>
</body>
</html>