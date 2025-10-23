<?php
/**
 * Concept Graph helper functions for MindVault (MySQLi)
 * - Text extraction to existing/new concepts
 * - Graph-aware reflection generation
 * - Personalized daily quote
 *
 * Notes:
 * - Keep logic simple and transparent; can be upgraded later (e.g., better NLP)
 */

/**
 * Normalize Thai/English text lightly for matching.
 * - Lowercase
 * - Trim spaces
 * - Collapse multiple spaces
 */
function mv_normalize_text($text) {
    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);
    return $text;
}

/**
 * Very light Thai stopwords; extend as needed.
 */
function mv_stopwords() {
    return [
        'และ','กับ','ของ','ว่า','ที่','ได้','ให้','ใน','เป็น','มี','ไป','มา','ก็','จะ','ไม่','ให้','หรือ','คือ','มัน','ฉัน','คุณ'
    ];
}

/**
 * Extract concepts from free text, creating missing concepts for the user.
 * Returns array of concept_id (ints).
 */
function extractConceptsFromText($text, $userId, mysqli $db) {
    $conceptIds = [];
    if (!$userId) return $conceptIds;

    $normalized = mv_normalize_text($text);
    $stops = mv_stopwords();

    // 1) Load all user concepts (id, name)
    $stmt = $db->prepare("SELECT concept_id, concept_name FROM concepts WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $userConcepts = [];
    while ($row = $res->fetch_assoc()) {
        $userConcepts[$row['concept_id']] = mv_normalize_text($row['concept_name']);
    }
    $stmt->close();

    // 2) Exact substring matching (simple)
    foreach ($userConcepts as $cid => $cname) {
        if ($cname === '') continue;
        if (mb_strpos($normalized, $cname, 0, 'UTF-8') !== false) {
            $conceptIds[] = (int)$cid;
        }
    }

    // 3) Simple keyword candidates (split by spaces and dedupe)
    $tokens = array_values(array_unique(preg_split('/\s+/u', $normalized)));
    $tokens = array_filter($tokens, function($t) use ($stops) {
        return $t !== '' && !in_array($t, $stops, true) && mb_strlen($t, 'UTF-8') >= 2;
    });

    // 4) For tokens not represented, optionally create new concepts
    if (!empty($tokens)) {
        // Check which tokens already exist
        $existingMap = [];
        if (!empty($userConcepts)) {
            foreach ($userConcepts as $cid => $cname) {
                $existingMap[$cname] = (int)$cid;
            }
        }

        foreach ($tokens as $tok) {
            if (isset($existingMap[$tok])) {
                $cid = $existingMap[$tok];
                if (!in_array($cid, $conceptIds, true)) $conceptIds[] = $cid;
                continue;
            }
            // Create a new concept if token looks like a meaningful noun-ish token
            if (mb_strlen($tok, 'UTF-8') <= 24) {
                $ins = $db->prepare("INSERT INTO concepts (user_id, concept_name, description) VALUES (?, ?, NULL)");
                $ins->bind_param('is', $userId, $tok);
                if ($ins->execute()) {
                    $newId = (int)$ins->insert_id;
                    $conceptIds[] = $newId;
                    $existingMap[$tok] = $newId;
                }
                $ins->close();
            }
        }
    }

    // Dedupe
    $conceptIds = array_values(array_unique(array_map('intval', $conceptIds)));
    return $conceptIds;
}

/**
 * Link entry to concepts with optional relevance score.
 */
function linkEntryConcepts($entryId, array $conceptIds, mysqli $db, $relevance = 1.0) {
    if ($entryId <= 0 || empty($conceptIds)) return;
    $stmt = $db->prepare("INSERT IGNORE INTO entry_concepts (entry_id, concept_id, relevance_score) VALUES (?, ?, ?)");
    foreach ($conceptIds as $cid) {
        $cid = (int)$cid;
        $stmt->bind_param('iid', $entryId, $cid, $relevance);
        $stmt->execute();
    }
    $stmt->close();
}

/**
 * Graph-aware reflection generator based on concepts and relations.
 * The function will:
 *  - fetch entry mood
 *  - fetch concepts linked to the entry
 *  - fetch relations for each concept (both directions)
 *  - compose questions referencing relation_type and neighbor concepts
 */
function generatePhilosophyReflection($entryId, $userId, mysqli $db) {
    // 0) Fetch mood from entry to tune tone
    $mood = null;
    $stmt = $db->prepare("SELECT mood, content FROM entries WHERE id = ? AND EXISTS (SELECT 1 FROM users u WHERE u.id = entries.user_id)");
    $stmt->bind_param('i', $entryId);
    $stmt->execute();
    $res = $stmt->get_result();
    $entry = $res->fetch_assoc();
    $stmt->close();

    if (!$entry) {
        return 'วันนี้คุณได้เรียนรู้อะไรจากประสบการณ์ของคุณ?';
    }

    $mood = $entry['mood'] ?? 'neutral';

    // 1) Concepts for this entry
    $stmt = $db->prepare("SELECT ec.concept_id, c.concept_name FROM entry_concepts ec JOIN concepts c ON c.concept_id = ec.concept_id WHERE ec.entry_id = ?");
    $stmt->bind_param('i', $entryId);
    $stmt->execute();
    $rs = $stmt->get_result();
    $entryConcepts = $rs->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($entryConcepts)) {
        // Fallback: extract concepts from text now and link
        $conceptIds = extractConceptsFromText($entry['content'] ?? '', $userId, $db);
        linkEntryConcepts($entryId, $conceptIds, $db, 1.0);
        if (empty($conceptIds)) {
            return 'หากมองจากภาพรวม วันนี้แนวคิดใดมีอิทธิพลต่อความรู้สึกของคุณมากที่สุด และเพราะเหตุใด?';
        }
        // Refresh entryConcepts
        $placeholders = implode(',', array_fill(0, count($conceptIds), '?'));
        // Not strictly needed to reload names; simple generic question:
        return 'ในบันทึกนี้ คุณกำลังกล่าวถึงแนวคิดบางอย่าง คุณคิดว่าแนวคิดเหล่านั้นเชื่อมโยงกันอย่างไร และส่งผลต่อคุณอย่างไร?';
    }

    // 2) For each concept, pull relations (both out-going and in-coming)
    $questions = [];
    foreach ($entryConcepts as $c) {
        $cid = (int)$c['concept_id'];
        $cname = $c['concept_name'];

        // Outgoing relations
        $q1 = $db->prepare("SELECT r.relation_type, r.weight, c2.concept_name AS neighbor_name FROM relations r JOIN concepts c2 ON c2.concept_id = r.target_concept_id WHERE r.user_id = ? AND r.source_concept_id = ? ORDER BY r.weight DESC, r.frequency DESC LIMIT 5");
        $q1->bind_param('ii', $userId, $cid);
        $q1->execute();
        $out = $q1->get_result()->fetch_all(MYSQLI_ASSOC);
        $q1->close();

        // Incoming relations
        $q2 = $db->prepare("SELECT r.relation_type, r.weight, c2.concept_name AS neighbor_name FROM relations r JOIN concepts c2 ON c2.concept_id = r.source_concept_id WHERE r.user_id = ? AND r.target_concept_id = ? ORDER BY r.weight DESC, r.frequency DESC LIMIT 5");
        $q2->bind_param('ii', $userId, $cid);
        $q2->execute();
        $in = $q2->get_result()->fetch_all(MYSQLI_ASSOC);
        $q2->close();

        $rels = array_merge($out, $in);

        if (!empty($rels)) {
            foreach ($rels as $r) {
                $neighbor = $r['neighbor_name'];
                $rtype = $r['relation_type'];
                $tone = ($mood === 'sad') ? 'ในมุมมองที่อ่อนโยนต่อใจ' : (($mood === 'happy') ? 'ในมุมมองที่เปิดรับการเติบโต' : 'ในมุมมองที่สมดุล');
                $questions[] = "คุณกล่าวถึง ‘{$cname}’ ซึ่งคุณเคยระบุว่า ‘{$rtype}’ กับ ‘{$neighbor}’ — {$tone} ความเชื่อมโยงนี้กำลังมีความหมายอย่างไรสำหรับคุณวันนี้?";
            }
        } else {
            // No relations => encourage building the graph
            $questions[] = "คุณกล่าวถึง ‘{$cname}’ คุณคิดว่ามันเชื่อมโยงกับแนวคิดอื่นๆ ของคุณ (เช่น สิ่งที่คุณให้ความสำคัญสูง) อย่างไรบ้าง? มีตัวอย่างสถานการณ์ล่าสุดไหม?";
        }
    }

    if (empty($questions)) {
        return 'เมื่อมองย้อนกลับไป คุณเห็นความเชื่อมโยงระหว่างแนวคิดสำคัญในชีวิตคุณกับบันทึกนี้อย่างไร?';
    }

    // Choose one question (could be improved with scoring later)
    return $questions[array_rand($questions)];
}

/**
 * Personalized daily quote by user’s active/weighted concepts (last 30 days favored).
 * Returns string "quote — author".
 */
function getPersonalizedDailyQuote($userId, mysqli $db) {
    // 1) Pick interesting concepts by weight/frequency in the last 30 days
    $stmt = $db->prepare(
        "SELECT c.concept_id
         FROM concepts c
         LEFT JOIN relations r ON r.source_concept_id = c.concept_id AND r.user_id = c.user_id
         WHERE c.user_id = ?
         GROUP BY c.concept_id
         ORDER BY COALESCE(MAX(r.weight), 0) DESC, COALESCE(SUM(r.frequency), 0) DESC
         LIMIT 3"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $cids = array_map(fn($row) => (int)$row['concept_id'], $res->fetch_all(MYSQLI_ASSOC));
    $stmt->close();

    if (!empty($cids)) {
        // Try to find quotes linked to these concepts, prefer weighted random
        $in = implode(',', array_fill(0, count($cids), '?'));
        $types = str_repeat('i', count($cids));
        $sql = "SELECT quote_text, author FROM philosophy_quotes WHERE related_concept_id IN ($in) ORDER BY RAND() LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$cids);
        $stmt->execute();
        $qres = $stmt->get_result();
        $quote = $qres->fetch_assoc();
        $stmt->close();
        if ($quote) {
            return $quote['quote_text'] . ' — ' . ($quote['author'] ?: 'ไม่ระบุ');
        }
    }

    // Fallback: any quote
    $qres = $db->query("SELECT quote_text, author FROM philosophy_quotes ORDER BY RAND() LIMIT 1");
    if ($qres && $row = $qres->fetch_assoc()) {
        return $row['quote_text'] . ' — ' . ($row['author'] ?: 'ไม่ระบุ');
    }

    return 'ทุกวันคือโอกาสใหม่ในการเติบโต — MindVault';
}

/** Simple helpers to update learning signals */
function updateRelationFrequency($relationId, mysqli $db) {
    $stmt = $db->prepare("UPDATE relations SET frequency = frequency + 1, updated_at = NOW() WHERE relation_id = ?");
    $stmt->bind_param('i', $relationId);
    $stmt->execute();
    $stmt->close();
}

function updateRelationWeight($relationId, $newWeight, mysqli $db) {
    $stmt = $db->prepare("UPDATE relations SET weight = ?, updated_at = NOW() WHERE relation_id = ?");
    $stmt->bind_param('di', $newWeight, $relationId);
    $stmt->execute();
    $stmt->close();
}
