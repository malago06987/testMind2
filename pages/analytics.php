<?php
// หน้าสถิติและการวิเคราะห์
// ตัวแปร $conn, $user_id ถูกส่งมาจาก index.php

// ดึงข้อมูลสถิติรายเดือน
$monthlyStats = [];
$monthlyQuery = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as total_entries,
    SUM(CASE WHEN mood = 'happy' THEN 1 ELSE 0 END) as happy_count,
    SUM(CASE WHEN mood = 'neutral' THEN 1 ELSE 0 END) as neutral_count,
    SUM(CASE WHEN mood = 'sad' THEN 1 ELSE 0 END) as sad_count,
    AVG(CASE 
        WHEN mood = 'happy' THEN 3 
        WHEN mood = 'neutral' THEN 2 
        WHEN mood = 'sad' THEN 1 
    END) as avg_mood
    FROM entries 
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC";

$monthlyStmt = $conn->prepare($monthlyQuery);
$monthlyStmt->bind_param("i", $user_id);
$monthlyStmt->execute();
$monthlyStats = $monthlyStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$monthlyStmt->close();

// ดึงข้อมูลสถิติรายสัปดาห์ล่าสุด
$weeklyStats = [];
$weeklyQuery = "SELECT 
    DATE(created_at) as date,
    COUNT(*) as entries_count,
    AVG(CASE 
        WHEN mood = 'happy' THEN 3 
        WHEN mood = 'neutral' THEN 2 
        WHEN mood = 'sad' THEN 1 
    END) as avg_mood
    FROM entries 
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC";

$weeklyStmt = $conn->prepare($weeklyQuery);
$weeklyStmt->bind_param("i", $user_id);
$weeklyStmt->execute();
$weeklyStats = $weeklyStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$weeklyStmt->close();

// ดึงคำที่ใช้บ่อยสุด
$wordFreq = getWordFrequency($user_id, $conn);

// ดึงสถิติทั่วไป
$generalStats = [];
$generalQuery = "SELECT 
    COUNT(*) as total_entries,
    COUNT(DISTINCT DATE(created_at)) as active_days,
    MIN(created_at) as first_entry,
    MAX(created_at) as last_entry,
    AVG(LENGTH(content)) as avg_content_length
    FROM entries WHERE user_id = ?";

$generalStmt = $conn->prepare($generalQuery);
$generalStmt->bind_param("i", $user_id);
$generalStmt->execute();
$generalStats = $generalStmt->get_result()->fetch_assoc();
$generalStmt->close();

// คำนวณ streak
$streak = 0;
$streakQuery = "SELECT DATE(created_at) as entry_date 
                FROM entries 
                WHERE user_id = ? 
                GROUP BY DATE(created_at) 
                ORDER BY entry_date DESC";
$streakStmt = $conn->prepare($streakQuery);
$streakStmt->bind_param("i", $user_id);
$streakStmt->execute();
$dates = $streakStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$streakStmt->close();

if(!empty($dates)) {
    $currentDate = new DateTime();
    $streak = 0;
    
    foreach($dates as $dateRow) {
        $entryDate = new DateTime($dateRow['entry_date']);
        $diff = $currentDate->diff($entryDate)->days;
        
        if($diff == $streak) {
            $streak++;
        } else {
            break;
        }
    }
}
?>

<div class="analytics-page">
    <div class="page-header">
        <h2 class="page-title">📊 สถิติและการวิเคราะห์</h2>
    </div>

    <!-- สถิติทั่วไป -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $generalStats['total_entries'] ?? 0; ?></div>
                <div class="stat-label">บันทึกทั้งหมด</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $generalStats['active_days'] ?? 0; ?></div>
                <div class="stat-label">วันที่ใช้งาน</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🔥</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $streak; ?></div>
                <div class="stat-label">ต่อเนื่อง (วัน)</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📏</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo round($generalStats['avg_content_length'] ?? 0); ?></div>
                <div class="stat-label">ความยาวเฉลี่ย</div>
            </div>
        </div>
    </div>

    <!-- กราฟแนวโน้มรายเดือน -->
    <div class="chart-section">
        <h3 class="section-title">📈 แนวโน้มรายเดือน</h3>
        <div class="chart-container">
            <div class="month-chart">
                <?php if(!empty($monthlyStats)): ?>
                    <?php foreach($monthlyStats as $month): ?>
                        <div class="month-bar">
                            <div class="bar-container">
                                <div class="bar happy" style="height: <?php echo ($month['happy_count'] / max(1, $month['total_entries'])) * 100; ?>%"></div>
                                <div class="bar neutral" style="height: <?php echo ($month['neutral_count'] / max(1, $month['total_entries'])) * 100; ?>%"></div>
                                <div class="bar sad" style="height: <?php echo ($month['sad_count'] / max(1, $month['total_entries'])) * 100; ?>%"></div>
                            </div>
                            <div class="month-label"><?php echo date('M Y', strtotime($month['month'] . '-01')); ?></div>
                            <div class="month-count"><?php echo $month['total_entries']; ?> รายการ</div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">ยังไม่มีข้อมูลเพียงพอสำหรับแสดงกราฟ</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- กราฟอารมณ์ 7 วันล่าสุด -->
    <div class="chart-section">
        <h3 class="section-title">😊 อารมณ์ 7 วันล่าสุด</h3>
        <div class="mood-timeline">
            <?php if(!empty($weeklyStats)): ?>
                <?php foreach(array_reverse($weeklyStats) as $day): ?>
                    <div class="mood-day">
                        <div class="mood-indicator" 
                             style="background: <?php 
                                $mood = $day['avg_mood'];
                                if($mood >= 2.5) echo '#4caf50';
                                elseif($mood >= 1.5) echo '#ff9800'; 
                                else echo '#f44336';
                             ?>">
                            <?php 
                            if($mood >= 2.5) echo '😊';
                            elseif($mood >= 1.5) echo '😐'; 
                            else echo '😔';
                            ?>
                        </div>
                        <div class="day-label"><?php echo date('d/m', strtotime($day['date'])); ?></div>
                        <div class="day-count"><?php echo $day['entries_count']; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">ยังไม่มีข้อมูลสำหรับสัปดาห์นี้</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- คำที่ใช้บ่อย -->
    <div class="chart-section">
        <h3 class="section-title">🏷️ คำที่ใช้บ่อยสุด</h3>
        <div class="word-frequency">
            <?php if(!empty($wordFreq)): ?>
                <?php $maxCount = max($wordFreq); ?>
                <?php foreach(array_slice($wordFreq, 0, 20) as $word => $count): ?>
                    <div class="word-item">
                        <div class="word-text"><?php echo htmlspecialchars($word); ?></div>
                        <div class="word-bar">
                            <div class="word-fill" style="width: <?php echo ($count / $maxCount) * 100; ?>%"></div>
                        </div>
                        <div class="word-count"><?php echo $count; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">เขียนบันทึกเพิ่มเติมเพื่อดูการวิเคราะห์คำ</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.analytics-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.page-header {
    margin-bottom: 2rem;
    text-align: center;
}

.page-title {
    color: var(--accent);
    font-size: 2rem;
    margin: 0;
}

.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: linear-gradient(135deg, rgba(100, 255, 218, 0.05), rgba(79, 195, 247, 0.05));
    border: 1px solid rgba(100, 255, 218, 0.1);
    border-radius: var(--radius);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.stat-icon {
    font-size: 2rem;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 1.8rem;
    font-weight: bold;
    color: var(--accent);
}

.stat-label {
    color: var(--muted);
    font-size: 0.9rem;
}

.chart-section {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
    border: 1px solid rgba(100, 255, 218, 0.08);
    border-radius: var(--radius);
    padding: 2rem;
    margin-bottom: 2rem;
}

.section-title {
    color: var(--accent);
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
}

.month-chart {
    display: flex;
    gap: 1rem;
    justify-content: center;
    align-items: end;
    min-height: 200px;
}

.month-bar {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 60px;
}

.bar-container {
    height: 150px;
    width: 40px;
    display: flex;
    flex-direction: column-reverse;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 4px;
    overflow: hidden;
}

.bar {
    width: 100%;
    transition: all 0.3s ease;
}

.bar.happy { background: #4caf50; }
.bar.neutral { background: #ff9800; }
.bar.sad { background: #f44336; }

.month-label {
    font-size: 0.8rem;
    color: var(--muted);
    margin-top: 0.5rem;
}

.month-count {
    font-size: 0.7rem;
    color: var(--accent);
}

.mood-timeline {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.mood-day {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
}

.mood-indicator {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.day-label {
    font-size: 0.9rem;
    color: var(--muted);
}

.day-count {
    font-size: 0.8rem;
    color: var(--accent);
}

.word-frequency {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.word-item {
    display: grid;
    grid-template-columns: 120px 1fr 40px;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.02);
}

.word-text {
    font-weight: 500;
    color: var(--text);
}

.word-bar {
    height: 20px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    overflow: hidden;
}

.word-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--accent), var(--accent-light));
    transition: width 0.5s ease;
}

.word-count {
    font-size: 0.9rem;
    color: var(--accent);
    text-align: right;
}

.no-data {
    text-align: center;
    color: var(--muted);
    font-style: italic;
    padding: 2rem;
}

@media (max-width: 768px) {
    .analytics-page {
        padding: 1rem;
    }
    
    .stats-overview {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .month-chart {
        overflow-x: auto;
        justify-content: start;
        padding-bottom: 1rem;
    }
    
    .word-item {
        grid-template-columns: 100px 1fr 30px;
        gap: 0.5rem;
    }
}
</style>