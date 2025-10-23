
-- สร้างฐานข้อมูล MindVault
CREATE DATABASE IF NOT EXISTS mindvault;
USE mindvault;

-- ตาราง users (เก็บข้อมูลผู้ใช้)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ตาราง entries (เก็บบันทึกของผู้ใช้)
CREATE TABLE IF NOT EXISTS entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    mood ENUM('happy','neutral','sad') DEFAULT 'neutral',
    word_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_mood (mood)
);

-- ตาราง reflections (เก็บการสะท้อนจาก Philosophy Engine)
CREATE TABLE IF NOT EXISTS reflections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_id INT NOT NULL,
    reflection_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_id) REFERENCES entries(id) ON DELETE CASCADE,
    INDEX idx_entry (entry_id)
);

-- ตาราง mood_analytics (เก็บสถิติอารมณ์)
CREATE TABLE IF NOT EXISTS mood_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    mood_happy INT DEFAULT 0,
    mood_neutral INT DEFAULT 0,
    mood_sad INT DEFAULT 0,
    avg_mood DECIMAL(3,2) DEFAULT 0,
    total_entries INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, date),
    INDEX idx_user_date (user_id, date)
);

-- ตาราง user_stats (เก็บสถิติผู้ใช้)
CREATE TABLE IF NOT EXISTS user_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_entries INT DEFAULT 0,
    total_words INT DEFAULT 0,
    active_days INT DEFAULT 0,
    streak_days INT DEFAULT 0,
    last_entry_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
);

-- ตาราง philosophy_quotes (เก็บคำคมปรัชญา)
CREATE TABLE IF NOT EXISTS philosophy_quotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quote_text TEXT NOT NULL,
    author VARCHAR(100) DEFAULT 'ไม่ระบุ',
    category ENUM('happiness','wisdom','reflection','mindfulness','growth') DEFAULT 'wisdom',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- เพิ่มข้อมูลคำคมปรัชญา
INSERT INTO philosophy_quotes (quote_text, author, category) VALUES
('ความสุขไม่ได้มาจากสิ่งที่เราได้รับ แต่มาจากสิ่งที่เราให้', 'ลาวจื้อ', 'happiness'),
('การเรียนรู้ที่แท้จริงเกิดขึ้นเมื่อเราหยุดคิดว่าเรารู้ทุกอย่าง', 'โซคราเตส', 'wisdom'),
('ความสงบภายในคือพลังที่ยิ่งใหญ่ที่สุด', 'พุทธะ', 'mindfulness'),
('ทุกวันคือโอกาสใหม่ที่จะเป็นคนที่ดีกว่าเมื่อวาน', 'ไม่ระบุ', 'growth'),
('ความยากลำบากคือบันไดที่นำไปสู่ความแข็งแกร่ง', 'นีทเช่', 'growth'),
('สิ่งที่เราคิดเราจะกลายเป็น', 'พุทธะ', 'mindfulness'),
('ชีวิตคือสิ่งที่เกิดขึ้นกับคุณขณะที่คุณกำลังวางแผนสิ่งอื่น', 'จอห์น เลนนอน', 'wisdom'),
('ความรักคือการมอบความสุขให้ผู้อื่นโดยไม่คาดหวังสิ่งตอบแทน', 'ไม่ระบุ', 'happiness'),
('การยอมรับความจริงคือจุดเริ่มต้นของการเปลี่ยนแปลง', 'คาร์ล ยุง', 'reflection'),
('ใจที่สงบเป็นใจที่แข็งแกร่ง', 'ไม่ระบุ', 'mindfulness');

-- สร้าง trigger สำหรับอัพเดทสถิติผู้ใช้
DELIMITER //
CREATE TRIGGER update_user_stats_after_entry
AFTER INSERT ON entries
FOR EACH ROW
BEGIN
    INSERT INTO user_stats (user_id, total_entries, last_entry_date)
    VALUES (NEW.user_id, 1, DATE(NEW.created_at))
    ON DUPLICATE KEY UPDATE
        total_entries = total_entries + 1,
        last_entry_date = DATE(NEW.created_at),
        updated_at = CURRENT_TIMESTAMP;
END//
DELIMITER ;

-- สร้าง view สำหรับสถิติผู้ใช้
CREATE VIEW user_dashboard_stats AS
SELECT 
    u.id as user_id,
    u.name,
    COALESCE(us.total_entries, 0) as total_entries,
    COALESCE(us.active_days, 0) as active_days,
    COALESCE(
        AVG(CASE 
            WHEN e.mood = 'happy' THEN 3 
            WHEN e.mood = 'neutral' THEN 2 
            WHEN e.mood = 'sad' THEN 1 
        END), 0
    ) as avg_mood_score,
    CASE 
        WHEN AVG(CASE 
            WHEN e.mood = 'happy' THEN 3 
            WHEN e.mood = 'neutral' THEN 2 
            WHEN e.mood = 'sad' THEN 1 
        END) >= 2.5 THEN 'happy'
        WHEN AVG(CASE 
            WHEN e.mood = 'happy' THEN 3 
            WHEN e.mood = 'neutral' THEN 2 
            WHEN e.mood = 'sad' THEN 1 
        END) >= 1.5 THEN 'neutral'
        ELSE 'sad'
    END as avg_mood
FROM users u
LEFT JOIN user_stats us ON u.id = us.user_id
LEFT JOIN entries e ON u.id = e.user_id
GROUP BY u.id, u.name, us.total_entries, us.active_days;

-- ตัวอย่างผู้ใช้ (password: 123456)
INSERT INTO users (name, email, password, role) VALUES
('เเอดมิน', 'test@mindvault.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFx7aS8bHeq3/5kn2S6f9sC6FZcGZx2u', 'admin');

-- ตัวอย่างบันทึก
INSERT INTO entries (user_id, content, mood) VALUES
(1, 'วันนี้เป็นวันที่ดีมาก ได้เรียนรู้สิ่งใหม่ๆ มากมาย รู้สึกมีความสุขและพร้อมที่จะเผชิญกับความท้าทายใหม่ๆ', 'happy'),
(1, 'รู้สึกเหนื่อยนิดหน่อยจากการทำงาน แต่ก็ยังโอเค มีแรงใจที่จะต่อสู้ต่อไป', 'neutral'),
(1, 'วันนี้เจอปัญหาที่ทำงาน รู้สึกหดหู่และเครียดมาก หวังว่าพรุ่งนี้จะดีกว่านี้', 'sad'),
(2, 'ช่วงเช้าไปออกกำลังกาย รู้สึกสดชื่นและมีพลังมาก ความสุขเล็กๆ น้อยๆ ในชีวิตประจำวัน', 'happy'),
(2, 'วันนี้เป็นวันธรรมดา ไม่มีอะไรพิเศษ แต่ก็ผ่านไปได้ด้วยดี', 'neutral');

-- ตัวอย่างการสะท้อน
INSERT INTO reflections (entry_id, reflection_text) VALUES
(1, 'ความสุขที่คุณรู้สึกวันนี้ มาจากสิ่งใดภายในตัวคุณเอง? การเรียนรู้ใหม่ๆ ช่วยให้คุณเติบโตได้อย่างไร?'),
(2, 'แม้จะรู้สึกเหนื่อย แต่คุณยังมีแรงใจต่อสู้ แสดงว่าความแข็งแกร่งของคุณอยู่ตรงไหน?'),
(3, 'ความเศร้าที่คุณรู้สึก กำลังสอนอะไรเกี่ยวกับตัวคุณเอง? ถ้าเพื่อนรู้สึกเหมือนคุณ คุณจะให้คำแนะนำอะไรกับเขา?'),
(4, 'การออกกำลังกายทำให้คุณรู้สึกดี บอกอะไรเกี่ยวกับการดูแลตัวเองของคุณ?'),
(5, 'การที่วันนี้เป็นวันธรรมดา มีคุณค่าอย่างไรในชีวิตคุณ? ความสงบนี้กำลังเตรียมคุณสำหรับสิ่งใดต่อไป?');
