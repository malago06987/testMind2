<?php
session_start();

// หากล็อกอินแล้วให้ไปหน้า dashboard
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit;
}

?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>MindVault - พื้นที่ปลอดภัยสำหรับความคิดและความรู้สึกของคุณ</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --bg: #0b0f1a;
            --card: #0f1724;
            --accent: #64ffda;
            --accent-light: #4fc3f7;
            --muted: #9aa6b2;
            --text: #e6eef6;
            --success: #4caf50;
            --error: #f44336;
            --radius: 12px;
            --shadow: 0 8px 30px rgba(2, 6, 23, 0.6);
            --shadow-hover: 0 12px 40px rgba(2, 6, 23, 0.8);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        .landing-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg) 0%, #16213e 50%, #1a2b5c 100%);
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, -apple-system, Arial, sans-serif;
            color: var(--text);
            position: relative;
            overflow: hidden;
            cursor: default;
        }
        
        .landing-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(100, 255, 218, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(79, 195, 247, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(100, 255, 218, 0.05) 0%, transparent 50%);
            animation: backgroundShift 15s ease-in-out infinite;
        }
        
        @keyframes backgroundShift {
            0%, 100% { 
                background: 
                    radial-gradient(circle at 20% 80%, rgba(100, 255, 218, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(79, 195, 247, 0.08) 0%, transparent 50%),
                    radial-gradient(circle at 40% 40%, rgba(100, 255, 218, 0.05) 0%, transparent 50%);
            }
            25% {
                background: 
                    radial-gradient(circle at 80% 30%, rgba(100, 255, 218, 0.12) 0%, transparent 60%),
                    radial-gradient(circle at 20% 70%, rgba(79, 195, 247, 0.1) 0%, transparent 55%),
                    radial-gradient(circle at 60% 10%, rgba(100, 255, 218, 0.06) 0%, transparent 45%);
            }
            50% {
                background: 
                    radial-gradient(circle at 60% 90%, rgba(100, 255, 218, 0.08) 0%, transparent 45%),
                    radial-gradient(circle at 10% 10%, rgba(79, 195, 247, 0.12) 0%, transparent 50%),
                    radial-gradient(circle at 90% 60%, rgba(100, 255, 218, 0.07) 0%, transparent 55%);
            }
            75% {
                background: 
                    radial-gradient(circle at 30% 20%, rgba(100, 255, 218, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 70% 80%, rgba(79, 195, 247, 0.09) 0%, transparent 48%),
                    radial-gradient(circle at 20% 50%, rgba(100, 255, 218, 0.08) 0%, transparent 52%);
            }
        }
        
        .landing-content {
            text-align: center;
            color: var(--text);
            max-width: 900px;
            padding: 3rem 2rem;
            position: relative;
            z-index: 1;
        }
        
        .logo {
            font-size: 4.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(100, 255, 218, 0.3);
            letter-spacing: -2px;
        }
        
        .tagline {
            font-size: 1.8rem;
            margin-bottom: 2.5rem;
            color: var(--accent-light);
            font-weight: 300;
            letter-spacing: 0.5px;
        }
        
        .description {
            font-size: 1.2rem;
            line-height: 1.7;
            margin-bottom: 4rem;
            color: var(--muted);
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
            margin-bottom: 4rem;
        }
        
        .feature {
            background: rgba(15, 23, 36, 0.8);
            padding: 2.5rem 2rem;
            border-radius: var(--radius);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(100, 255, 218, 0.15);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            transition: left 0.6s ease;
        }
        
        .feature:hover::before {
            left: 100%;
        }
        
        .feature:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: rgba(100, 255, 218, 0.3);
        }
        
        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 0 10px rgba(100, 255, 218, 0.3));
        }
        
        .feature-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--accent);
        }
        
        .feature-desc {
            color: var(--muted);
            line-height: 1.6;
            font-size: 1rem;
        }
        
        .cta-section {
            margin-top: 4rem;
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .cta-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            color: var(--bg);
            padding: 1.2rem 3rem;
            border-radius: 50px;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(100, 255, 218, 0.25);
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
        }
        
        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }
        
        .cta-button:hover::before {
            left: 100%;
        }
        
        .cta-button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 40px rgba(100, 255, 218, 0.4);
        }
        
        .cta-button.secondary {
            background: transparent;
            color: var(--accent);
            border: 2px solid var(--accent);
            box-shadow: 0 8px 25px rgba(100, 255, 218, 0.15);
        }
        
        .cta-button.secondary:hover {
            background: var(--accent);
            color: var(--bg);
            box-shadow: 0 15px 40px rgba(100, 255, 218, 0.3);
        }
        
        .quote {
            margin-top: 4rem;
            font-style: italic;
            font-size: 1.2rem;
            color: var(--muted);
            position: relative;
            padding: 1.5rem;
        }
        
        .quote::before {
            content: '"';
            font-size: 4rem;
            position: absolute;
            top: -1rem;
            left: -1rem;
            color: var(--accent);
            opacity: 0.3;
        }
        
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }
        
        .floating-element {
            position: absolute;
            border-radius: 50%;
            opacity: 0.08;
            animation: float 20s infinite linear;
        }
        
        .floating-element:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            background: linear-gradient(45deg, var(--accent), transparent);
            animation: float 20s infinite linear, pulse 4s infinite ease-in-out;
            animation-delay: 0s;
        }
        
        .floating-element:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 20%;
            right: 10%;
            background: linear-gradient(135deg, var(--accent-light), transparent);
            animation: float 25s infinite linear reverse, pulse 5s infinite ease-in-out;
            animation-delay: 2s;
        }
        
        .floating-element:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            background: linear-gradient(225deg, var(--accent), transparent);
            animation: float 18s infinite linear, pulse 3.5s infinite ease-in-out;
            animation-delay: 1s;
        }
        
        .floating-element:nth-child(4) {
            width: 100px;
            height: 100px;
            bottom: 10%;
            right: 20%;
            background: linear-gradient(315deg, var(--accent-light), transparent);
            animation: float 22s infinite linear reverse, pulse 4.5s infinite ease-in-out;
            animation-delay: 3s;
        }
        
        .floating-element:nth-child(5) {
            width: 40px;
            height: 40px;
            top: 50%;
            left: 5%;
            background: radial-gradient(circle, var(--accent), transparent);
            animation: float 16s infinite linear, pulse 2.8s infinite ease-in-out;
            animation-delay: 1.5s;
        }
        
        .floating-element:nth-child(6) {
            width: 90px;
            height: 90px;
            top: 70%;
            right: 5%;
            background: radial-gradient(circle, var(--accent-light), transparent);
            animation: float 24s infinite linear reverse, pulse 3.8s infinite ease-in-out;
            animation-delay: 2.5s;
        }
        
        /* Interactive Particles */
        .interactive-particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--accent);
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
            opacity: 0;
            animation: particleFade 2s ease-out forwards;
        }
        
        @keyframes float {
            0% { transform: translateY(0px) translateX(0px) rotate(0deg); }
            25% { transform: translateY(-20px) translateX(10px) rotate(90deg); }
            50% { transform: translateY(0px) translateX(20px) rotate(180deg); }
            75% { transform: translateY(20px) translateX(10px) rotate(270deg); }
            100% { transform: translateY(0px) translateX(0px) rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { 
                opacity: 0.08; 
                transform: scale(1);
            }
            50% { 
                opacity: 0.15; 
                transform: scale(1.1);
            }
        }
        
        @keyframes particleFade {
            0% { 
                opacity: 1; 
                transform: scale(0);
            }
            50% { 
                opacity: 0.8; 
                transform: scale(1);
            }
            100% { 
                opacity: 0; 
                transform: scale(0) translateY(-100px);
            }
        }
        
        
        /* Click Wave Effect */
        .modern-wave {
            position: absolute;
            border: 2px solid rgba(100, 255, 218, 0.8);
            border-radius: 50%;
            pointer-events: none;
            z-index: 101;
            animation: modernWaveExpand 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        @keyframes modernWaveExpand {
            0% { 
                width: 20px; 
                height: 20px; 
                opacity: 1;
                border-width: 2px;
                filter: blur(0px);
            }
            50% {
                border-width: 1px;
                opacity: 0.6;
                filter: blur(0.5px);
            }
            100% { 
                width: 200px; 
                height: 200px; 
                opacity: 0;
                border-width: 0px;
                filter: blur(2px);
            }
        }
        
        /* Enhanced Interactive Particles */
        .interactive-particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            pointer-events: none;
            z-index: 2;
            opacity: 0;
            animation: particleSpiral 3s ease-out forwards;
            box-shadow: 0 0 10px var(--accent);
        }
        
        .magic-particle {
            position: absolute;
            width: 3px;
            height: 20px;
            background: linear-gradient(180deg, var(--accent), transparent);
            pointer-events: none;
            z-index: 2;
            opacity: 0;
            animation: magicWand 2s ease-out forwards;
            border-radius: 50%;
        }
        
        @keyframes particleSpiral {
            0% { 
                opacity: 1; 
                transform: scale(0) rotate(0deg);
            }
            30% { 
                opacity: 0.8; 
                transform: scale(1.5) rotate(180deg);
            }
            100% { 
                opacity: 0; 
                transform: scale(0) rotate(360deg) translateX(80px);
            }
        }
        

        

        
        /* Click Wave Effect */
        .click-wave {
            position: absolute;
            border: 3px solid rgba(100, 255, 218, 0.6);
            border-radius: 50%;
            pointer-events: none;
            z-index: 5;
            animation: waveExpand 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        @keyframes waveExpand {
            0% { 
                width: 10px; 
                height: 10px; 
                opacity: 1;
                border-width: 3px;
            }
            50% {
                border-width: 1px;
                opacity: 0.7;
            }
            100% { 
                width: 300px; 
                height: 300px; 
                opacity: 0;
                border-width: 0px;
            }
        }
        
        /* Animated Grid Background */
        .grid-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        
        .neural-network {
            position: absolute;
            width: 100%;
            height: 100%;
            background: transparent;
        }
        
        .neural-node {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(100, 255, 218, 0.6);
            border-radius: 50%;
            animation: nodePulse 3s ease-in-out infinite;
            box-shadow: 0 0 10px rgba(100, 255, 218, 0.3);
        }
        
        .neural-connection {
            position: absolute;
            height: 1px;
            background: linear-gradient(90deg, 
                rgba(100, 255, 218, 0.1) 0%, 
                rgba(100, 255, 218, 0.4) 50%, 
                rgba(100, 255, 218, 0.1) 100%);
            transform-origin: left center;
            animation: connectionFlow 4s ease-in-out infinite;
        }
        
        @keyframes nodePulse {
            0%, 100% { 
                transform: scale(1); 
                opacity: 0.6;
                box-shadow: 0 0 10px rgba(100, 255, 218, 0.3);
            }
            50% { 
                transform: scale(1.5); 
                opacity: 1;
                box-shadow: 0 0 20px rgba(100, 255, 218, 0.6);
            }
        }
        
        @keyframes connectionFlow {
            0% { 
                background: linear-gradient(90deg, 
                    rgba(100, 255, 218, 0.1) 0%, 
                    rgba(100, 255, 218, 0.1) 100%);
            }
            25% {
                background: linear-gradient(90deg, 
                    rgba(100, 255, 218, 0.1) 0%, 
                    rgba(100, 255, 218, 0.6) 25%, 
                    rgba(100, 255, 218, 0.1) 100%);
            }
            50% {
                background: linear-gradient(90deg, 
                    rgba(100, 255, 218, 0.1) 0%, 
                    rgba(100, 255, 218, 0.6) 50%, 
                    rgba(100, 255, 218, 0.1) 100%);
            }
            75% {
                background: linear-gradient(90deg, 
                    rgba(100, 255, 218, 0.1) 0%, 
                    rgba(100, 255, 218, 0.6) 75%, 
                    rgba(100, 255, 218, 0.1) 100%);
            }
            100% {
                background: linear-gradient(90deg, 
                    rgba(100, 255, 218, 0.1) 0%, 
                    rgba(100, 255, 218, 0.1) 100%);
            }
        }
        
        @media (max-width: 768px) {
            .logo { 
                font-size: 3rem; 
                letter-spacing: -1px;
            }
            .tagline { font-size: 1.4rem; }
            .description { font-size: 1.1rem; }
            .features { 
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            .feature {
                padding: 2rem 1.5rem;
            }
            .cta-section {
                flex-direction: column;
                align-items: center;
            }
            .cta-button {
                width: 100%;
                max-width: 300px;
            }
            .landing-content {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <!-- Neural Network Background -->
        <div class="grid-background">
            <div class="neural-network" id="neuralNetwork"></div>
        </div>
        
        <!-- Floating Elements -->
        <div class="floating-elements">
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
        </div>
        
        <div class="landing-content">
            <div class="logo">🧠 MindVault</div>
            
            <div class="tagline">
                พื้นที่ปลอดภัยสำหรับความคิดและความรู้สึกของคุณ
            </div>
            
            <div class="description">
                MindVault เป็นแอปพลิเคชันบันทึกส่วนตัวที่ช่วยให้คุณได้สำรวจตัวเอง 
                บันทึกความคิด ความรู้สึก และประสบการณ์ในชีวิตประจำวัน 
                พร้อมระบบ AI ที่จะช่วยให้คุณได้เข้าใจตัวเองมากขึ้น
            </div>
            
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">✍️</div>
                    <div class="feature-title">บันทึกความคิด</div>
                    <div class="feature-desc">
                        เขียนบันทึกส่วนตัวได้อย่างอิสระ พร้อมระบุอารมณ์ในแต่ละวัน
                    </div>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">🤖</div>
                    <div class="feature-title">AI Philosophy Engine</div>
                    <div class="feature-desc">
                        ระบบ AI ที่จะให้คำแนะนำและคำถามเพื่อการสะท้อนความคิด
                    </div>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">📊</div>
                    <div class="feature-title">วิเคราะห์รูปแบบ</div>
                    <div class="feature-desc">
                        ดูสถิติและแนวโน้มของอารมณ์ ความคิด และพฤติกรรมของคุณ
                    </div>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">🔒</div>
                    <div class="feature-title">ปลอดภัย 100%</div>
                    <div class="feature-desc">
                        ข้อมูลของคุณถูกเก็บอย่างปลอดภัย เฉพาะคุณเท่านั้นที่เข้าถึงได้
                    </div>
                </div>
            </div>
            
            <div class="cta-section">
                <a href="login.php" class="cta-button">เข้าสู่ระบบ</a>
                <a href="register.php" class="cta-button secondary">สมัครสมาชิก</a>
            </div>
            
            <div class="quote">
                "การรู้จักตัวเองคือจุดเริ่มต้นของปัญญาทั้งมวล" - อริสโตเติล
            </div>
        </div>
    </div>
    
    <script>
        let particleCount = 0;
        let neuralNodes = [];
        let neuralConnections = [];
        
        // Initialize neural network
        document.addEventListener('DOMContentLoaded', function() {
            createNeuralNetwork();
        });
        
        function createNeuralNetwork() {
            const network = document.getElementById('neuralNetwork');
            const nodeCount = 25;
            
            // Create nodes
            for (let i = 0; i < nodeCount; i++) {
                const node = document.createElement('div');
                node.className = 'neural-node';
                node.style.left = Math.random() * 100 + '%';
                node.style.top = Math.random() * 100 + '%';
                node.style.animationDelay = Math.random() * 3 + 's';
                
                network.appendChild(node);
                neuralNodes.push({
                    element: node,
                    x: parseFloat(node.style.left),
                    y: parseFloat(node.style.top)
                });
            }
            
            // Create connections
            for (let i = 0; i < nodeCount; i++) {
                for (let j = i + 1; j < nodeCount; j++) {
                    const distance = Math.sqrt(
                        Math.pow(neuralNodes[i].x - neuralNodes[j].x, 2) + 
                        Math.pow(neuralNodes[i].y - neuralNodes[j].y, 2)
                    );
                    
                    if (distance < 30 && Math.random() < 0.6) {
                        createConnection(neuralNodes[i], neuralNodes[j], network);
                    }
                }
            }
        }
        
        function createConnection(node1, node2, container) {
            const connection = document.createElement('div');
            connection.className = 'neural-connection';
            
            const deltaX = node2.x - node1.x;
            const deltaY = node2.y - node1.y;
            const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
            const angle = Math.atan2(deltaY, deltaX) * 180 / Math.PI;
            
            connection.style.left = node1.x + '%';
            connection.style.top = node1.y + '%';
            connection.style.width = distance + '%';
            connection.style.transform = `rotate(${angle}deg)`;
            connection.style.animationDelay = Math.random() * 4 + 's';
            
            container.appendChild(connection);
            neuralConnections.push(connection);
        }
        
        // Enhanced parallax effect
        document.addEventListener('mousemove', function(e) {
            const floatingElements = document.querySelectorAll('.floating-element');
            const x = (e.clientX / window.innerWidth - 0.5) * 30;
            const y = (e.clientY / window.innerHeight - 0.5) * 30;
            
            floatingElements.forEach((el, index) => {
                const multiplier = (index + 1) * 0.1;
                const intensity = 1 + (index * 0.02);
                const currentTransform = el.style.transform;
                const rotateMatch = currentTransform.match(/rotate\([^)]*\)/);
                const currentRotate = rotateMatch ? rotateMatch[0] : 'rotate(0deg)';
                
                el.style.transform = `translate(${x * multiplier * intensity}px, ${y * multiplier * intensity}px) ${currentRotate}`;
            });
        });
        
        // Simple click effect
        document.addEventListener('click', function(e) {
            // Pulse effect on neural network
            neuralNodes.forEach(node => {
                const rect = node.element.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                const distance = Math.sqrt(
                    Math.pow(e.clientX - centerX, 2) + Math.pow(e.clientY - centerY, 2)
                );
                
                if (distance < 150) {
                    node.element.style.animation = 'none';
                    node.element.style.transform = `scale(${2 - distance / 150})`;
                    node.element.style.boxShadow = `0 0 30px rgba(100, 255, 218, 0.8)`;
                    
                    setTimeout(() => {
                        node.element.style.animation = 'nodePulse 3s ease-in-out infinite';
                        node.element.style.transform = 'scale(1)';
                        node.element.style.boxShadow = '0 0 10px rgba(100, 255, 218, 0.3)';
                    }, 500);
                }
            });
        });
        
        // Enhanced Loading Animations
        window.addEventListener('load', function() {
            // Logo animation with bounce
            const logo = document.querySelector('.logo');
            logo.style.opacity = '0';
            logo.style.transform = 'scale(0.3) rotate(-10deg)';
            
            setTimeout(() => {
                logo.style.transition = 'all 1.2s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                logo.style.opacity = '1';
                logo.style.transform = 'scale(1) rotate(0deg)';
            }, 300);
            
            // Tagline animation
            const tagline = document.querySelector('.tagline');
            tagline.style.opacity = '0';
            tagline.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                tagline.style.transition = 'all 0.8s ease';
                tagline.style.opacity = '1';
                tagline.style.transform = 'translateY(0)';
            }, 800);
            
            // Description animation
            const description = document.querySelector('.description');
            description.style.opacity = '0';
            description.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                description.style.transition = 'all 0.8s ease';
                description.style.opacity = '1';
                description.style.transform = 'translateY(0)';
            }, 1200);
            
            // Features staggered animation
            const features = document.querySelectorAll('.feature');
            features.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(50px) rotateX(45deg)';
                
                setTimeout(() => {
                    el.style.transition = 'all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0) rotateX(0deg)';
                }, 1600 + (index * 200));
            });
            
            // CTA buttons with spring animation
            const ctaButtons = document.querySelectorAll('.cta-button');
            ctaButtons.forEach((btn, index) => {
                btn.style.opacity = '0';
                btn.style.transform = 'translateY(20px) scale(0.8)';
                
                setTimeout(() => {
                    btn.style.transition = 'all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                    btn.style.opacity = '1';
                    btn.style.transform = 'translateY(0) scale(1)';
                }, 2200 + (index * 150));
            });
            
            // Quote fade in
            const quote = document.querySelector('.quote');
            quote.style.opacity = '0';
            
            setTimeout(() => {
                quote.style.transition = 'all 1s ease';
                quote.style.opacity = '1';
            }, 3000);
        });
        
        // Enhanced button interactions
        document.querySelectorAll('.cta-button').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.05)';
                createParticlesBurst(this);
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
            
            button.addEventListener('click', function(e) {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 100);
                }, 100);
            });
        });
        
        function createParticlesBurst(element) {
            const rect = element.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            
            // Create starburst pattern
            for (let i = 0; i < 12; i++) {
                setTimeout(() => {
                    const angle = (Math.PI * 2 * i) / 12;
                    const distance = 40 + Math.random() * 20;
                    const x = centerX + Math.cos(angle) * distance;
                    const y = centerY + Math.sin(angle) * distance;
                    
                    const particleType = i % 3 === 0 ? 'magic' : 'spiral';
                    createMagicParticle(x, y, particleType);
                }, i * 30);
            }
        }
        
        // Mouse leave effect
        document.addEventListener('mouseleave', function() {
            const mouseGlow = document.getElementById('mouseGlow');
            if (mouseGlow) {
                mouseGlow.style.opacity = '0';
            }
        });
        
        // Add enhanced button hover effects
        document.querySelectorAll('.feature').forEach(feature => {
            feature.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
                this.style.filter = 'brightness(1.1)';
                
                // Create subtle particle effect around feature
                const rect = this.getBoundingClientRect();
                for (let i = 0; i < 3; i++) {
                    setTimeout(() => {
                        const x = rect.left + Math.random() * rect.width;
                        const y = rect.top + Math.random() * rect.height;
                        createMagicParticle(x, y, 'spiral');
                    }, i * 100);
                }
            });
            
            feature.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.filter = 'brightness(1)';
            });
        });
        
        // Remove old ripple style and add new wave style
        const waveStyle = document.createElement('style');
        waveStyle.textContent = `
            @keyframes fadeInOut {
                0% { opacity: 0; transform: scale(0); }
                50% { opacity: 1; transform: scale(1); }
                100% { opacity: 0; transform: scale(0); }
            }
        `;
        document.head.appendChild(waveStyle);
        
        // Automatic magical background effects
        setInterval(function() {
            if (Math.random() < 0.4) {
                const x = Math.random() * window.innerWidth;
                const y = Math.random() * window.innerHeight;
                const type = Math.random() < 0.3 ? 'magic' : 'spiral';
                createMagicParticle(x, y, type);
            }
        }, 1500);
        
        // Add constellation effect
        setInterval(function() {
            if (Math.random() < 0.2) {
                createConstellation();
            }
        }, 5000);
        
        function createConstellation() {
            const numStars = 3 + Math.floor(Math.random() * 4);
            const baseX = Math.random() * (window.innerWidth - 200);
            const baseY = Math.random() * (window.innerHeight - 200);
            
            for (let i = 0; i < numStars; i++) {
                setTimeout(() => {
                    const x = baseX + (Math.random() - 0.5) * 150;
                    const y = baseY + (Math.random() - 0.5) * 150;
                    createMagicParticle(x, y, 'spiral');
                }, i * 200);
            }
        }
    </script>
</body>
</html>

