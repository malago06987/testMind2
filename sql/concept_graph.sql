-- --------------------------------------------------
-- MindVault Concept Graph Schema (MySQL InnoDB / utf8mb4)
-- Tables: concepts, relations, entry_concepts
-- Adds: philosophy_quotes.related_concept_id (nullable FK)
-- --------------------------------------------------

-- Concepts ที่ผู้ใช้สร้างเอง
CREATE TABLE IF NOT EXISTS concepts (
  concept_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสแนวคิด',
  user_id INT NOT NULL COMMENT 'เจ้าของแนวคิด',
  concept_name VARCHAR(120) NOT NULL COMMENT 'ชื่อแนวคิด (ไม่ควรยาวเกินไป)',
  description TEXT NULL COMMENT 'คำอธิบายเพิ่มเติม',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'เวลาสร้าง',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'เวลาอัปเดตล่าสุด',
  CONSTRAINT fk_concepts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_concepts_user (user_id),
  INDEX idx_concepts_user_name (user_id, concept_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='แนวคิดส่วนบุคคลของผู้ใช้';

-- Relations ระหว่าง Concepts (มีทิศทาง: from -> to)
CREATE TABLE IF NOT EXISTS relations (
  relation_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'รหัสความสัมพันธ์',
  user_id INT NOT NULL COMMENT 'เจ้าของกราฟ (เพื่อกำหนดขอบเขต)',
  source_concept_id INT NOT NULL COMMENT 'แนวคิดต้นทาง',
  target_concept_id INT NOT NULL COMMENT 'แนวคิดปลายทาง',
  relation_type VARCHAR(50) NOT NULL COMMENT 'ประเภท เช่น related_to, part_of, opposite_of',
  weight DECIMAL(5,2) NOT NULL DEFAULT 1.00 COMMENT 'น้ำหนักความสำคัญ',
  frequency INT NOT NULL DEFAULT 0 COMMENT 'จำนวนครั้งที่พบ/ใช้งาน',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'เวลาสร้าง',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'เวลาอัปเดตล่าสุด',
  CONSTRAINT fk_relations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_relations_source FOREIGN KEY (source_concept_id) REFERENCES concepts(concept_id) ON DELETE CASCADE,
  CONSTRAINT fk_relations_target FOREIGN KEY (target_concept_id) REFERENCES concepts(concept_id) ON DELETE CASCADE,
  INDEX idx_relations_user (user_id),
  INDEX idx_relations_source (source_concept_id),
  INDEX idx_relations_target (target_concept_id),
  INDEX idx_relations_user_source_target (user_id, source_concept_id, target_concept_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ความสัมพันธ์ระหว่างแนวคิดของผู้ใช้';

-- Mapping ระหว่าง Entry กับ Concepts (Composite PK)
CREATE TABLE IF NOT EXISTS entry_concepts (
  entry_id INT NOT NULL COMMENT 'บันทึก',
  concept_id INT NOT NULL COMMENT 'แนวคิด',
  relevance_score FLOAT DEFAULT 1 COMMENT 'คะแนนความเกี่ยวข้อง (0..1 หรือสเกลอื่น)',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'เวลาสร้าง',
  PRIMARY KEY (entry_id, concept_id),
  CONSTRAINT fk_entry_concepts_entry FOREIGN KEY (entry_id) REFERENCES entries(id) ON DELETE CASCADE,
  CONSTRAINT fk_entry_concepts_concept FOREIGN KEY (concept_id) REFERENCES concepts(concept_id) ON DELETE CASCADE,
  INDEX idx_entry_concepts_concept (concept_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='เชื่อมโยงบันทึกกับแนวคิด';

-- เพิ่มคอลัมน์ใน philosophy_quotes สำหรับเชื่อมกับ concept (nullable)
ALTER TABLE philosophy_quotes 
  ADD COLUMN IF NOT EXISTS related_concept_id INT NULL AFTER author;

ALTER TABLE philosophy_quotes 
  ADD CONSTRAINT fk_quotes_concept 
  FOREIGN KEY (related_concept_id) REFERENCES concepts(concept_id) ON DELETE SET NULL;
