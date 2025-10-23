<?php
// Dashboard - หน้าหลัก
// ตัวแปร $conn, $user_id, $user_name ถูกส่งมาจาก index.php

$message = '';
$messageType = '';

// ประมวลผลการส่งบันทึก
if(isset($_POST['submit_entry'])){
    $content = trim($_POST['content']);
    $mood = $_POST['mood'] ?? '';
    
    if(empty($content)){
        $message = 'กรุณาเขียนบันทึกของคุณ';
        $messageType = 'error';
    } elseif(empty($mood)){
        $message = 'กรุณาเลือกอารมณ์ของคุณ';
        $messageType = 'error';
    } else {
        // บันทึกลงฐานข้อมูล
        $stmt = $conn->prepare("INSERT INTO entries (user_id, content, mood, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $user_id, $content, $mood);
        
        if($stmt->execute()){
            $entry_id = $conn->insert_id;
            
            // สร้างการสะท้อนจาก Philosophy Engine
            $reflection = generatePhilosophyReflection($content, $mood);
            
            // บันทึกการสะท้อน
            $reflectStmt = $conn->prepare("INSERT INTO reflections (entry_id, reflection_text, created_at) VALUES (?, ?, NOW())");
            $reflectStmt->bind_param("is", $entry_id, $reflection);
            $reflectStmt->execute();
            $reflectStmt->close();
            
            $message = 'บันทึกของคุณถูกเพิ่มเรียบร้อยแล้ว! 🎉';
            $messageType = 'success';
            
            // Clear form data after successful submission
            $_POST = array();
        } else {
            $message = 'เกิดข้อผิดพลาดในการบันทึก กรุณาลองใหม่อีกครั้ง';
            $messageType = 'error';
        }
        $stmt->close();
    }
}

// ดึงข้อมูลสถิติ
$statsQuery = "SELECT 
    COUNT(*) as total_entries,
    COUNT(DISTINCT DATE(created_at)) as active_days,
    AVG(CASE 
        WHEN mood = 'happy' THEN 3 
        WHEN mood = 'neutral' THEN 2 
        WHEN mood = 'sad' THEN 1 
    END) as avg_mood
    FROM entries WHERE user_id = ?";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("i", $user_id);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();
$statsStmt->close();

// ดึงบันทึกล่าสุด 3 รายการ
$recentQuery = "SELECT content, mood, created_at FROM entries WHERE user_id = ? ORDER BY created_at DESC LIMIT 3";
$recentStmt = $conn->prepare($recentQuery);
$recentStmt->bind_param("i", $user_id);
$recentStmt->execute();
$recentEntries = $recentStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recentStmt->close();

// ดึงคำที่ใช้บ่อย
$wordFreq = getWordFrequency($user_id, $conn);

// ดึงการสะท้อนล่าสุด
$lastReflectionQuery = "SELECT r.reflection_text FROM reflections r 
                        JOIN entries e ON r.entry_id = e.id 
                        WHERE e.user_id = ? ORDER BY r.created_at DESC LIMIT 1";
$reflectionStmt = $conn->prepare($lastReflectionQuery);
$reflectionStmt->bind_param("i", $user_id);
$reflectionStmt->execute();
$lastReflection = $reflectionStmt->get_result()->fetch_assoc();
$reflectionStmt->close();

// คำคมประจำวัน
$dailyQuotes = [
    "ความสุขไม่ได้มาจากสิ่งที่เราได้รับ แต่มาจากสิ่งที่เราให้",
    "การเรียนรู้ที่แท้จริงเกิดขึ้นเมื่อเราหยุดคิดว่าเรารู้ทุกอย่าง",
    "ความสงบภายในคือพลังที่ยิ่งใหญ่ที่สุด",
    "ทุกวันคือโอกาสใหม่ที่จะเป็นคนที่ดีกว่าเมื่อวาน",
    "ความยากลำบากคือบันไดที่นำไปสู่ความแข็งแกร่ง"
];
$todayQuote = $dailyQuotes[date('z') % count($dailyQuotes)];
?>

<div class="dashboard-main">
    <!-- Sidebar (คอลัมน์ซ้าย) -->
    <aside class="sidebar">
        <div class="stats-card">
            <h3 class="card-title">📊 สถิติของคุณ</h3>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['total_entries'] ?? 0; ?></div>
                <div class="stat-label">บันทึกทั้งหมด</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['active_days'] ?? 0; ?></div>
                <div class="stat-label">วันที่ใช้งาน</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">
                    <?php 
                    $avgMood = $stats['avg_mood'] ?? 0;
                    if($avgMood >= 2.5) echo '😊 ดี';
                    elseif($avgMood >= 1.5) echo '😐 ปกติ';
                    else echo '😔 ควรดูแล';
                    ?>
                </div>
                <div class="stat-label">อารมณ์เฉลี่ย</div>
            </div>
        </div>

        <div class="recent-card">
            <h3 class="card-title">📝 บันทึกล่าสุด</h3>
            <?php if(empty($recentEntries)): ?>
                <p class="no-entries">ยังไม่มีบันทึก</p>
                <div class="welcome-message">
                    <p>🌟 ยินดีต้อนรับสู่ MindVault!</p>
                    <p>เริ่มเขียนบันทึกแรกของคุณได้เลย</p>
                </div>
            <?php else: ?>
                <?php foreach($recentEntries as $entry): ?>
                    <div class="recent-item">
                        <div class="recent-content">
                            <?php echo htmlspecialchars(substr($entry['content'], 0, 80)) . (strlen($entry['content']) > 80 ? '...' : ''); ?>
                        </div>
                        <div class="recent-meta">
                            <span class="recent-mood">
                                <?php 
                                switch($entry['mood']){
                                    case 'happy': echo '😊'; break;
                                    case 'neutral': echo '😐'; break;
                                    case 'sad': echo '😔'; break;
                                }
                                ?>
                            </span>
                            <span class="recent-date"><?php echo date('d/m', strtotime($entry['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Main Panel (คอลัมน์กลาง) -->
    <main class="main-panel">
        <?php if(!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="entry-card">
            <h2 class="card-title">✍️ เขียนบันทึกใหม่</h2>
            <form method="post" class="entry-form" action="?page=dashboard">
                <div class="form-group">
                    <label class="form-label">ความคิดและความรู้สึกของคุณวันนี้</label>
                    <textarea name="content" class="entry-textarea" 
                              placeholder="แบ่งปันความคิด ความรู้สึก หรือประสบการณ์ของคุณ...&#10;&#10;💡 เคล็ดลับ: เขียนอย่างตรงไปตรงมา อย่าคิดมาก ให้ใจเป็นเครื่องนำทาง" 
                              rows="8" required><?php echo isset($_POST['content']) && $messageType === 'error' ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">อารมณ์ของคุณ ณ ตอนนี้</label>
                    <div class="mood-selector">
                        <label class="mood-option">
                            <input type="radio" name="mood" value="happy" required>
                            <span class="mood-emoji">😊</span>
                            <span class="mood-text">ดี</span>
                        </label>
                        <label class="mood-option">
                            <input type="radio" name="mood" value="neutral" required>
                            <span class="mood-emoji">😐</span>
                            <span class="mood-text">ปกติ</span>
                        </label>
                        <label class="mood-option">
                            <input type="radio" name="mood" value="sad" required>
                            <span class="mood-emoji">😔</span>
                            <span class="mood-text">ไม่ดี</span>
                        </label>
                    </div>
                </div>

                <button type="submit" name="submit_entry" class="submit-btn">
                    <span class="btn-text">ส่งบันทึก</span>
                    <span class="btn-icon">✨</span>
                </button>
            </form>
        </div>

        <?php if(isset($lastReflection['reflection_text'])): ?>
        <div class="reflection-card">
            <h3 class="card-title">🤔 การสะท้อนจาก MindVault</h3>
            <div class="reflection-content">
                <?php echo htmlspecialchars($lastReflection['reflection_text']); ?>
            </div>
            <div class="reflection-actions">
                <small class="reflection-hint">💡 ใช้เวลาสักครู่เพื่อครองใจกับคำถามนี้</small>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Stats Panel (คอลัมน์ขวา) -->
    <aside class="stats-panel">
        <div class="insights-card">
            <h3 class="card-title">🎯 ข้อมูลเชิงลึก</h3>
            
            <div class="insight-section">
                <h4 class="insight-title">คำที่ใช้บ่อย</h4>
                <div class="word-cloud">
                    <?php if(!empty($wordFreq)): ?>
                        <?php foreach(array_slice($wordFreq, 0, 10) as $word => $count): ?>
                            <span class="word-tag" style="font-size: <?php echo min(1.5, 0.8 + ($count * 0.1)); ?>em;">
                                <?php echo htmlspecialchars($word); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">เขียนบันทึกเพิ่มเติมเพื่อดูข้อมูล</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="insight-section">
                <h4 class="insight-title">ระดับการสะท้อน</h4>
                <div class="reflection-level">
                    <?php 
                    $totalEntries = $stats['total_entries'] ?? 0;
                    if($totalEntries < 5) {
                        echo '<span class="level-badge beginner">🌱 เริ่มต้น</span>';
                        echo '<p class="level-desc">เขียนไปแล้ว ' . $totalEntries . ' รายการ</p>';
                    } elseif($totalEntries < 20) {
                        echo '<span class="level-badge developing">🌿 กำลังพัฒนา</span>';
                        echo '<p class="level-desc">เขียนไปแล้ว ' . $totalEntries . ' รายการ</p>';
                    } else {
                        echo '<span class="level-badge advanced">🌳 ผู้เชี่ยวชาญ</span>';
                        echo '<p class="level-desc">เขียนไปแล้ว ' . $totalEntries . ' รายการ</p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="quote-card">
            <h3 class="card-title">💭 คำแนะนำวันนี้</h3>
            <div class="daily-quote">
                "<?php echo $todayQuote; ?>"
            </div>
            <div class="quote-footer">
                <small>🌅 คำคมใหม่ทุกวัน</small>
            </div>
        </div>
    </aside>
</div>

<style>
.welcome-message {
    text-align: center;
    padding: 1rem;
    background: rgba(100, 255, 218, 0.05);
    border-radius: 8px;
    margin-top: 1rem;
}

.welcome-message p {
    margin: 0.5rem 0;
    color: var(--accent);
}

.submit-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-icon {
    transition: transform 0.3s ease;
}

.submit-btn:hover .btn-icon {
    transform: scale(1.2);
}

.reflection-actions {
    margin-top: 1rem;
    text-align: center;
}

.reflection-hint {
    color: var(--muted);
    font-style: italic;
}

.level-desc {
    font-size: 0.8rem;
    color: var(--muted);
    margin-top: 0.5rem;
    text-align: center;
}

.quote-footer {
    text-align: center;
    margin-top: 1rem;
}

.quote-footer small {
    color: var(--muted);
}
</style>

<script>
// Auto-resize textarea
const textarea = document.querySelector('.entry-textarea');
if(textarea) {
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
    
    // Focus on textarea when page loads
    setTimeout(() => textarea.focus(), 100);
}

// Auto-hide messages after 5 seconds
const message = document.querySelector('.message');
if(message) {
    setTimeout(() => {
        message.style.opacity = '0';
        setTimeout(() => {
            if(message.parentNode) {
                message.remove();
            }
        }, 300);
    }, 5000);
}

// Enhanced mood selector
const moodOptions = document.querySelectorAll('.mood-option');
moodOptions.forEach(option => {
    option.addEventListener('click', function() {
        const radio = this.querySelector('input[type="radio"]');
        if(radio) {
            radio.checked = true;
            
            // Visual feedback
            moodOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            // Add animation
            const emoji = this.querySelector('.mood-emoji');
            if(emoji) {
                emoji.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    emoji.style.transform = 'scale(1)';
                }, 200);
            }
        }
    });
});
</script>