<?php
/**
 * MindVault Functions
 * ฟังก์ชันสำหรับการทำงานของระบบ MindVault
 */

/**
 * Philosophy Engine - สร้างข้อคิดและคำถามเชิงปรัชญาสำหรับการสะท้อน
 */
function generatePhilosophyReflection($content, $mood) {
    // คำสำคัญสำหรับวิเคราะห์
    $keywords = [
        'happiness' => ['ความสุข', 'ดีใจ', 'มีความสุข', 'สุข', 'ปลื้ม', 'ยินดี', 'ร่าเริง'],
        'sadness' => ['เศร้า', 'หดหู่', 'ไม่สบายใจ', 'ผิดหวัง', 'ท้อ', 'เสียใจ'],
        'stress' => ['เครียด', 'กดดัน', 'วิตก', 'กังวล', 'ตึงเครียด', 'ทน'],
        'work' => ['งาน', 'ทำงาน', 'เพื่อนร่วมงาน', 'หัวหน้า', 'บริษัท', 'อาชีพ'],
        'relationship' => ['เพื่อน', 'ครอบครัว', 'คู่รัก', 'พ่อ', 'แม่', 'พี่', 'น้อง'],
        'learning' => ['เรียน', 'ความรู้', 'ทักษะ', 'เรียนรู้', 'พัฒนา', 'เข้าใจ'],
        'change' => ['เปลี่ยน', 'ใหม่', 'แตกต่าง', 'ปรับ', 'พัฒนา', 'ก้าวหน้า']
    ];

    // แม่แบบคำถามตามหัวข้อและอารมณ์
    $reflectionTemplates = [
        'happiness' => [
            'happy' => [
                'ความสุขที่คุณรู้สึกวันนี้ มาจากสิ่งใดภายในตัวคุณเอง?',
                'คุณคิดว่าความสุขนี้จะช่วยให้คุณเผชิญหน้ากับสิ่งท้าทายได้อย่างไร?',
                'สิ่งที่ทำให้คุณมีความสุขวันนี้ สามารถแบ่งปันให้ผู้อื่นได้หรือไม่?'
            ],
            'neutral' => [
                'ช่วงเวลาที่คุณรู้สึกมีความสุข มีอะไรที่แตกต่างจากปกติ?',
                'คุณคิดว่าอะไรคือสิ่งเล็กๆ ที่อาจเพิ่มความสุขให้กับวันนี้?'
            ],
            'sad' => [
                'แม้จะรู้สึกไม่สบายใจ แต่คุณยังคิดถึงความสุขได้ แสดงว่าความแข็งแกร่งของคุณอยู่ตรงไหน?',
                'ความสุขที่คุณเคยรู้สึก สามารถเป็นแสงสว่างในช่วงเวลานี้ได้หรือไม่?'
            ]
        ],
        'sadness' => [
            'sad' => [
                'ความเศร้าที่คุณรู้สึก กำลังสอนอะไรเกี่ยวกับตัวคุณเอง?',
                'ถ้าเพื่อนรู้สึกเหมือนคุณ คุณจะให้คำแนะนำอะไรกับเขา?',
                'ความเศร้านี้ เป็นส่วนหนึ่งของการเติบโตของคุณหรือไม่?'
            ],
            'neutral' => [
                'การที่คุณสามารถสังเกตและเขียนเกี่ยวกับความเศร้าได้ แสดงถึงความแข็งแกร่งแบบใด?',
                'คุณคิดว่าประสบการณ์นี้จะทำให้คุณเข้าใจผู้อื่นมากขึ้นหรือไม่?'
            ],
            'happy' => [
                'การที่คุณสามารถมองความเศร้าและยังรู้สึกดีได้ บอกอะไรเกี่ยวกับภูมิต้านทานของคุณ?',
                'ความเศร้าในอดีตได้สอนอะไรที่ทำให้วันนี้คุณแข็งแกร่งขึ้น?'
            ]
        ],
        'stress' => [
            'sad' => [
                'ความเครียดนี้ กำลังชี้ให้เห็นสิ่งใดที่สำคัญกับคุณ?',
                'ถ้าความเครียดนี้หายไป คุณจะรู้สึกเป็นอย่างไร และนั่นบอกอะไรเกี่ยวกับสิ่งที่คุณต้องการ?'
            ],
            'neutral' => [
                'ความเครียดบางครั้งเป็นสัญญาณว่าเรากำลังเติบโต คุณเห็นด้วยกับข้อนี้หรือไม่?',
                'สิ่งใดในสถานการณ์นี้ที่คุณสามารถควบคุมได้?'
            ],
            'happy' => [
                'การที่คุณสามารถรู้สึกดีได้แม้มีความเครียด แสดงถึงทักษะการจัดการอารมณ์แบบใด?',
                'ความเครียดนี้อาจเป็นเครื่องมือให้คุณค้นพบความสามารถใหม่ๆ ได้อย่างไร?'
            ]
        ],
        'general' => [
            'happy' => [
                'วันนี้คุณได้เรียนรู้อะไรใหม่เกี่ยวกับตัวเอง?',
                'ความรู้สึกดีนี้ สะท้อนคุณค่าใดของคุณ?',
                'คุณจะใช้พลังงานบวกนี้เพื่ออะไรต่อไป?'
            ],
            'neutral' => [
                'การที่วันนี้เป็นวันปกติ มีคุณค่าอย่างไรในชีวิตคุณ?',
                'ความสงบนี้ กำลังเตรียมคุณสำหรับสิ่งใดต่อไป?',
                'คุณคิดว่าความสมดุลในชีวิตหมายความว่าอย่างไร?'
            ],
            'sad' => [
                'ความยากลำบากที่คุณเผชิญ กำลังปั้นแต่งคุณให้เป็นคนแบบใด?',
                'ถ้าคุณมองวันนี้จากมุมของตัวเองในอนาคต คุณจะเห็นอะไร?',
                'สิ่งใดในตัวคุณที่ไม่เปลี่ยนแปลง แม้เมื่อรู้สึกหนักใจ?'
            ]
        ]
    ];

    // หาหัวข้อที่เกี่ยวข้อง
    $detectedTopics = [];
    $contentLower = mb_strtolower($content);
    
    foreach($keywords as $topic => $words) {
        foreach($words as $word) {
            if(mb_strpos($contentLower, $word) !== false) {
                $detectedTopics[] = $topic;
                break;
            }
        }
    }

    // เลือกแม่แบบคำถาม
    if(!empty($detectedTopics)) {
        $selectedTopic = $detectedTopics[0];
        if(isset($reflectionTemplates[$selectedTopic][$mood])) {
            $templates = $reflectionTemplates[$selectedTopic][$mood];
        } else {
            $templates = $reflectionTemplates['general'][$mood];
        }
    } else {
        $templates = $reflectionTemplates['general'][$mood];
    }

    // สุ่มเลือกคำถาม
    return $templates[array_rand($templates)];
}

/**
 * วิเคราะห์ความถี่ของคำที่ใช้
 */
function getWordFrequency($user_id, $conn) {
    $query = "SELECT content FROM entries WHERE user_id = ? ORDER BY created_at DESC LIMIT 50";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $allText = '';
    while($row = $result->fetch_assoc()) {
        $allText .= ' ' . $row['content'];
    }
    $stmt->close();

    if(empty($allText)) {
        return [];
    }

    // คำที่ไม่นับ (stop words)
    $stopWords = [
        'ที่', 'เป็น', 'ใน', 'ของ', 'จะ', 'และ', 'ก็', 'คือ', 'มี', 'ได้', 'ไป', 
        'มา', 'แล้ว', 'หรือ', 'เพื่อ', 'กับ', 'จาก', 'โดย', 'ใหม่', 'อยู่', 'เขา',
        'เธอ', 'ฉัน', 'เรา', 'พวกเขา', 'มัน', 'นี้', 'นั้น', 'อัน', 'การ', 'ความ',
        'ครั้ง', 'วัน', 'เวลา', 'ปี', 'เดือน', 'สัปดาห์', 'ชั่วโมง', 'นาที'
    ];

    // แยกคำและนับความถี่
    $words = preg_split('/\s+/', mb_strtolower($allText));
    $wordCount = [];
    
    foreach($words as $word) {
        $word = trim($word, '.,!?;:"()[]{}');
        if(mb_strlen($word) >= 2 && !in_array($word, $stopWords)) {
            $wordCount[$word] = ($wordCount[$word] ?? 0) + 1;
        }
    }

    // เรียงลำดับตามความถี่
    arsort($wordCount);
    
    return array_slice($wordCount, 0, 20, true);
}

/**
 * คำนวณแนวโน้มอารมณ์
 */
function getMoodTrend($user_id, $conn, $days = 7) {
    $query = "SELECT mood, DATE(created_at) as entry_date, 
              AVG(CASE 
                  WHEN mood = 'happy' THEN 3 
                  WHEN mood = 'neutral' THEN 2 
                  WHEN mood = 'sad' THEN 1 
              END) as mood_score
              FROM entries 
              WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
              GROUP BY DATE(created_at)
              ORDER BY entry_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $days);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $result;
}

/**
 * ตรวจสอบระดับการมีส่วนร่วม
 */
function getEngagementLevel($user_id, $conn) {
    $totalEntries = 0;
    $recentEntries = 0;
    
    // จำนวนบันทึกทั้งหมด
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM entries WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $totalEntries = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    // จำนวนบันทึก 7 วันล่าสุด
    $stmt = $conn->prepare("SELECT COUNT(*) as recent FROM entries WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $recentEntries = $stmt->get_result()->fetch_assoc()['recent'];
    $stmt->close();
    
    if($totalEntries < 5) return 'beginner';
    if($recentEntries >= 3) return 'active';
    if($totalEntries >= 20) return 'experienced';
    
    return 'moderate';
}

/**
 * สร้างคำแนะนำส่วนบุคคล
 */
function getPersonalizedTip($user_id, $conn) {
    $tips = [
        'beginner' => [
            'เริ่มต้นด้วยการเขียนแค่ 2-3 ประโยคต่อวันก็เพียงพอ',
            'ลองสังเกตความรู้สึกเล็กๆ น้อยๆ ในชีวิตประจำวัน',
            'การเขียนบันทึกเป็นการออกกำลังกายสำหรับจิตใจ'
        ],
        'moderate' => [
            'ลองมองย้อนกลับไปดูบันทึกเก่าๆ คุณอาจพบการเปลี่ยนแปลงที่น่าประหลาดใจ',
            'การเขียนในช่วงเวลาที่แตกต่างกันจะให้มุมมองที่หลากหลาย',
            'ลองเขียนเกี่ยวกับสิ่งที่คุณรู้สึกขอบคุณในแต่ละวัน'
        ],
        'experienced' => [
            'ความสม่ำเสมอของคุณน่าชื่นชม ลองลึกลงไปในการวิเคราะห์รูปแบบของตัวเอง',
            'คุณอาจลองเขียนจดหมายให้ตัวเองในอนาคต',
            'การแบ่งปันความคิดเห็นกับผู้อื่นอาจช่วยขยายมุมมอง'
        ],
        'active' => [
            'พลังในการเขียนของคุณแรงมาก! ลองใช้มันเป็นเครื่องมือสำหรับตั้งเป้าหมาย',
            'คุณกำลังสร้างขุมทรัพย์ของความทรงจำและการเรียนรู้',
            'ลองทดลองเขียนในรูปแบบใหม่ๆ เช่น รายการสิ่งดีๆ หรือบทกวี'
        ]
    ];
    
    $level = getEngagementLevel($user_id, $conn);
    $levelTips = $tips[$level] ?? $tips['moderate'];
    
    return $levelTips[array_rand($levelTips)];
}
?>
