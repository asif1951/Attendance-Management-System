<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Attendance Notification - RTM Al-Kabir Technical University</title>
    <style>
        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2d3748;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            position: relative;
        }
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #ffeaa7);
            z-index: 1;
        }
        
        /* University Header - More Colorful */
        .university-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #9f7aea 100%);
            color: white;
            padding: 35px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .university-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .university-header::after {
            content: '📚';
            position: absolute;
            bottom: 10px;
            right: 20px;
            font-size: 60px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }
        .university-name {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        .university-address {
            font-size: 15px;
            opacity: 0.95;
            margin: 8px 0 0;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .university-address span {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 50px;
            backdrop-filter: blur(5px);
        }
        
        /* Alert Header - More Vibrant */
        .header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 50%, #ff9f43 100%);
            color: white;
            padding: 25px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '⚠️';
            position: absolute;
            top: -20px;
            left: -20px;
            font-size: 120px;
            opacity: 0.1;
            transform: rotate(15deg);
        }
        .header::after {
            content: '⚠️';
            position: absolute;
            bottom: -20px;
            right: -20px;
            font-size: 120px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .header h1 span {
            font-size: 40px;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .header p {
            margin: 10px 0 0;
            opacity: 1;
            font-size: 18px;
            font-weight: 500;
            background: rgba(255,255,255,0.2);
            display: inline-block;
            padding: 8px 24px;
            border-radius: 50px;
            backdrop-filter: blur(5px);
            position: relative;
            z-index: 1;
        }
        
        /* Main Content - More Colorful */
        .content {
            padding: 40px 35px;
            background: linear-gradient(135deg, #fff5f5 0%, #f0fff4 50%, #fef9e7 100%);
        }
        
        .greeting {
            font-size: 20px;
            margin-bottom: 20px;
            color: #2d3748;
            background: linear-gradient(135deg, #48c9b0 0%, #1abc9c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 600;
        }
        .greeting strong {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .student-info {
            background: linear-gradient(135deg, #a8e6cf 0%, #d4edda 50%, #c3e6cb 100%);
            border-left: 8px solid #28a745;
            padding: 25px;
            margin: 25px 0;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.2);
            position: relative;
            overflow: hidden;
        }
        .student-info::before {
            content: '📋';
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 40px;
            opacity: 0.2;
            transform: rotate(10deg);
        }
        .student-info p {
            margin: 12px 0;
            font-size: 16px;
            color: #155724;
            font-weight: 500;
        }
        .student-info strong {
            color: #155724;
            min-width: 90px;
            display: inline-block;
            font-weight: 700;
            background: rgba(255,255,255,0.5);
            padding: 4px 12px;
            border-radius: 20px;
        }
        
        .attendance-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 30px;
            border-radius: 30px;
            text-align: center;
            margin: 30px 0;
            box-shadow: 0 15px 35px rgba(245, 87, 108, 0.4);
            position: relative;
            overflow: hidden;
        }
        .attendance-box::before {
            content: '📊';
            position: absolute;
            top: -20px;
            left: -20px;
            font-size: 80px;
            opacity: 0.2;
            transform: rotate(-15deg);
        }
        .attendance-box::after {
            content: '📈';
            position: absolute;
            bottom: -20px;
            right: -20px;
            font-size: 80px;
            opacity: 0.2;
            transform: rotate(15deg);
        }
        .attendance-box .percentage {
            font-size: 64px;
            font-weight: 900;
            line-height: 1.2;
            letter-spacing: -2px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        .attendance-box .label {
            font-size: 18px;
            opacity: 1;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            background: rgba(255,255,255,0.2);
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            margin-top: 10px;
            position: relative;
            z-index: 1;
        }
        
        .threshold-badge {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 50%, #fff3cd 100%);
            color: #856404;
            padding: 15px 20px;
            border-radius: 50px;
            text-align: center;
            margin: 25px 0;
            font-weight: 600;
            border: 2px dashed #ffc107;
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
            position: relative;
            overflow: hidden;
        }
        .threshold-badge::before {
            content: '⚠️';
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
        }
        .threshold-badge::after {
            content: '⚠️';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
        }
        .threshold-badge strong {
            font-size: 18px;
            color: #dc3545;
            background: white;
            padding: 4px 12px;
            border-radius: 30px;
            margin-left: 5px;
        }
        
        .recommendations {
            background: linear-gradient(135deg, #cce5ff 0%, #b8daff 50%, #cce5ff 100%);
            padding: 30px;
            border-radius: 25px;
            margin: 30px 0;
            border: 2px solid #007bff;
            box-shadow: 0 10px 30px rgba(0, 123, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        .recommendations::before {
            content: '📝';
            position: absolute;
            top: -20px;
            right: -20px;
            font-size: 80px;
            opacity: 0.2;
            transform: rotate(15deg);
        }
        .recommendations h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #004085;
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
        }
        .recommendations h3 span {
            font-size: 28px;
            margin-right: 10px;
        }
        .recommendations ul {
            margin: 0;
            padding-left: 25px;
        }
        .recommendations li {
            margin-bottom: 12px;
            color: #004085;
            font-weight: 500;
            position: relative;
            list-style-type: none;
            padding-left: 30px;
        }
        .recommendations li::before {
            content: '✨';
            position: absolute;
            left: 0;
            color: #ffc107;
            font-size: 18px;
        }
        
        .divider {
            height: 3px;
            background: linear-gradient(90deg, transparent, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #ffeaa7, transparent);
            margin: 30px 0;
            border-radius: 3px;
        }
        
        .meta-info {
            font-size: 14px;
            color: #6c757d;
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: rgba(255,255,255,0.7);
            border-radius: 15px;
            border: 1px solid #dee2e6;
        }
        
        /* Footer - More Colorful */
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 50%, #2980b9 100%);
            padding: 30px 35px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .footer::before {
            content: '🎓';
            position: absolute;
            top: -20px;
            left: -20px;
            font-size: 100px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }
        .footer::after {
            content: '📚';
            position: absolute;
            bottom: -20px;
            right: -20px;
            font-size: 100px;
            opacity: 0.1;
            transform: rotate(15deg);
        }
        .footer .university-name-footer {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        .footer p {
            margin: 8px 0;
            font-size: 14px;
            opacity: 0.95;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            z-index: 1;
        }
        .footer p span {
            background: rgba(255,255,255,0.15);
            padding: 4px 12px;
            border-radius: 50px;
            backdrop-filter: blur(5px);
        }
        .footer .copyright {
            margin-top: 15px;
            font-size: 12px;
            opacity: 0.8;
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 15px;
        }
        
        /* Animations */
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Responsive */
        @media (max-width: 640px) {
            .container {
                margin: 15px;
                border-radius: 20px;
            }
            .university-name {
                font-size: 22px;
            }
            .header h1 {
                font-size: 26px;
            }
            .attendance-box .percentage {
                font-size: 48px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- University Header - No Logo -->
        <div class="university-header">
            <div class="university-name">🎓 RTM Al-Kabir Technical University</div>
            <div class="university-address">
                <span>📍 East Shahi Eidgah</span>
                <span>🏛️ Sylhet</span>
            </div>
        </div>
        
        <!-- Alert Header - More Vibrant -->
        <div class="header">
            <h1>
                <span>⚠️</span> Low Attendance Alert
            </h1>
            <p>{{ $data['course_name'] ?? 'Course' }} • {{ $data['course_code'] ?? 'N/A' }}</p>
        </div>
        
        <!-- Main Content -->
        <div class="content">
            <div class="greeting">
                Dear <strong>{{ $data['student_name'] }}</strong>,
            </div>
            
            <p style="color: #4a5568; font-size: 16px; margin-bottom: 20px;">
                This is a notification regarding your attendance in the following course:
            </p>
            
            <div class="student-info">
                <p><strong>📚 Course:</strong> {{ $data['course_name'] ?? 'N/A' }} ({{ $data['course_code'] ?? 'N/A' }})</p>
                <p><strong>🆔 Student ID:</strong> {{ $data['student_id'] ?? 'N/A' }}</p>
                <p><strong>📅 Date:</strong> {{ $data['current_date'] }}</p>
            </div>
            
            <div class="attendance-box">
                <div class="percentage">{{ $data['attendance_percentage'] }}%</div>
                <div class="label">Current Attendance</div>
            </div>
            
            <div class="threshold-badge">
                ⚠️ Your attendance is below <strong>{{ $data['threshold'] ?? 60 }}%</strong> threshold
            </div>
            
            <div class="recommendations">
                <h3>
                    <span>✨</span> Recommendations
                </h3>
                <ul>
                    <li>Attend all upcoming classes regularly</li>
                    <li>Submit any pending assignments on time</li>
                    <li>Contact your course instructor for guidance</li>
                    <li>Check for any makeup class opportunities</li>
                    <li>Review course materials to catch up</li>
                </ul>
            </div>
            
            <p style="color: #2d3748; font-size: 16px; font-weight: 500; text-align: center; background: #e2e8f0; padding: 15px; border-radius: 15px;">
                Regular attendance is crucial for academic success. Please take immediate steps to improve your attendance.
            </p>
            
            <div class="divider"></div>
            
            <div class="meta-info">
                ⚡ This is an automated notification from the Attendance Management System<br>
                📧 Please do not reply to this email
            </div>
        </div>
        
        <!-- Footer with University Info -->
        <div class="footer">
            <div class="university-name-footer">🎓 RTM Al-Kabir Technical University</div>
            <p><span>📍 East Shahi Eidgah, Sylhet, Bangladesh</span></p>
            <p><span>📞 +880 1234-567890</span> <span>📧 info@rtmaktu.edu.bd</span></p>
            <div class="copyright">
                © {{ date('Y') }} RTM Al-Kabir Technical University. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>