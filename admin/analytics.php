<?php
session_start();

// ตรวจสอบ session admin - ถ้าไม่ใช่ admin ให้กลับไปหน้า login
if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

$page_title = 'Analytics';
$current_page = 'analytics';

// สถิติอารมณ์ทั้งหมด
$mood_stats = $conn->query("
    SELECT 
        mood,
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM entries), 2) as percentage
    FROM entries
    GROUP BY mood
    ORDER BY count DESC
")->fetch_all(MYSQLI_ASSOC);

// สถิติตามวัน (7 วันล่าสุด)
$daily_stats = $conn->query("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as total_entries,
        COUNT(DISTINCT user_id) as active_users,
        SUM(CASE WHEN mood = 'happy' THEN 1 ELSE 0 END) as happy_count,
        SUM(CASE WHEN mood = 'neutral' THEN 1 ELSE 0 END) as neutral_count,
        SUM(CASE WHEN mood = 'sad' THEN 1 ELSE 0 END) as sad_count
    FROM entries
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC
")->fetch_all(MYSQLI_ASSOC);

// ผู้ใช้ที่เขียนมากที่สุด
$top_writers = $conn->query("
    SELECT 
        u.id,
        u.name,
        u.email,
        COUNT(e.id) as total_entries,
        SUM(e.word_count) as total_words,
        AVG(e.word_count) as avg_words_per_entry
    FROM users u
    LEFT JOIN entries e ON u.id = e.user_id
    GROUP BY u.id, u.name, u.email
    HAVING total_entries > 0
    ORDER BY total_entries DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// อารมณ์เฉลีย
$avg_mood = $conn->query("
    SELECT 
        AVG(CASE 
            WHEN mood = 'happy' THEN 3 
            WHEN mood = 'neutral' THEN 2 
            WHEN mood = 'sad' THEN 1 
        END) as avg_mood_score
    FROM entries
")->fetch_assoc()['avg_mood_score'];

include 'includes/header.php';
?>

<h1>📈 Analytics - สถิติทั้งระบบ</h1>

<div class="stats-grid">
    <div class="stat-card">
        <h3>😊 อารมณ์เฉลีย</h3>
        <div class="stat-value" style="color: <?php 
            echo $avg_mood >= 2.5 ? '#4caf50' : ($avg_mood >= 1.5 ? '#ff9800' : '#f44336'); 
        ?>">
            <?php echo number_format($avg_mood, 2); ?>/3
        </div>
        <div class="stat-label">
            <?php 
            if($avg_mood >= 2.5) echo '😊 ดีมาก';
            elseif($avg_mood >= 1.5) echo '😐 ปานกลาง';
            else echo '😢 ควรดูแล';
            ?>
        </div>
    </div>
    
    <?php foreach($mood_stats as $mood): ?>
    <div class="stat-card">
        <h3>
            <?php 
            $mood_labels = [
                'happy' => '😊 ความสุข',
                'neutral' => '😐 ปานกลาง',
                'sad' => '😢 เศร้า'
            ];
            echo $mood_labels[$mood['mood']] ?? $mood['mood'];
            ?>
        </h3>
        <div class="stat-value"><?php echo number_format($mood['count']); ?></div>
        <div class="stat-label"><?php echo $mood['percentage']; ?>% ของทั้งหมด</div>
    </div>
    <?php endforeach; ?>
</div>

<h2>📊 สถิติรายวัน (7 วันล่าสุด)</h2>
<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>วันที่</th>
                <th>บันทึกทั้งหมด</th>
                <th>ผู้ใช้ Active</th>
                <th>😊 Happy</th>
                <th>😐 Neutral</th>
                <th>😢 Sad</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($daily_stats as $day): ?>
            <tr>
                <td>
                    <strong><?php echo date('d/m/Y', strtotime($day['date'])); ?></strong><br>
                    <small style="color: var(--muted);">
                        <?php 
                        $thai_days = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์', 'เสาร์'];
                        echo $thai_days[date('w', strtotime($day['date']))]; 
                        ?>
                    </small>
                </td>
                <td><?php echo number_format($day['total_entries']); ?></td>
                <td><?php echo number_format($day['active_users']); ?></td>
                <td style="color: #4caf50;"><?php echo number_format($day['happy_count']); ?></td>
                <td style="color: #ff9800;"><?php echo number_format($day['neutral_count']); ?></td>
                <td style="color: #f44336;"><?php echo number_format($day['sad_count']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h2 style="margin-top: 2rem;">✍️ ผู้ใช้ที่เขียนมากที่สุด (Top 10)</h2>
<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>อันดับ</th>
                <th>ชื่อ</th>
                <th>Email</th>
                <th>บันทึกทั้งหมด</th>
                <th>คำทั้งหมด</th>
                <th>เฉลี่ย/บันทึก</th>
            </tr>
        </thead>
        <tbody>
            <?php $rank = 1; foreach($top_writers as $writer): ?>
            <tr>
                <td>
                    <?php 
                    if($rank == 1) echo '🥇';
                    elseif($rank == 2) echo '🥈';
                    elseif($rank == 3) echo '🥉';
                    else echo $rank;
                    ?>
                </td>
                <td><strong><?php echo htmlspecialchars($writer['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($writer['email']); ?></td>
                <td><?php echo number_format($writer['total_entries']); ?></td>
                <td><?php echo number_format($writer['total_words']); ?></td>
                <td><?php echo number_format($writer['avg_words_per_entry'], 0); ?> คำ</td>
            </tr>
            <?php $rank++; endforeach; ?>
        </tbody>
    </table>
</div>

    </div>
</body>
</html>
