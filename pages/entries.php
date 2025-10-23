<?php
// หน้าบันทึกทั้งหมด
// ตัวแปร $conn, $user_id ถูกส่งมาจาก index.php

$limit = 10;
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

// ดึงจำนวนบันทึกทั้งหมด
$countQuery = "SELECT COUNT(*) as total FROM entries WHERE user_id = ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$totalEntries = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$totalPages = ceil($totalEntries / $limit);

// ดึงบันทึกตามหน้า
$entriesQuery = "SELECT id, content, mood, created_at FROM entries 
                 WHERE user_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT ? OFFSET ?";
$entriesStmt = $conn->prepare($entriesQuery);
$entriesStmt->bind_param("iii", $user_id, $limit, $offset);
$entriesStmt->execute();
$entries = $entriesStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$entriesStmt->close();
?>

<div class="entries-page">
    <div class="page-header">
        <h2 class="page-title">📚 บันทึกทั้งหมด</h2>
        <div class="entries-stats">
            <span class="stat-item">ทั้งหมด <?php echo $totalEntries; ?> รายการ</span>
        </div>
    </div>

    <?php if(empty($entries)): ?>
        <div class="empty-state">
            <div class="empty-icon">📝</div>
            <h3>ยังไม่มีบันทึก</h3>
            <p>เริ่มเขียนบันทึกแรกของคุณได้ที่<a href="?page=dashboard">หน้าหลัก</a></p>
        </div>
    <?php else: ?>
        <div class="entries-grid">
            <?php foreach($entries as $entry): ?>
                <div class="entry-item">
                    <div class="entry-header">
                        <span class="entry-mood">
                            <?php 
                            switch($entry['mood']){
                                case 'happy': echo '😊 ดี'; break;
                                case 'neutral': echo '😐 ปกติ'; break;
                                case 'sad': echo '😔 ไม่ดี'; break;
                            }
                            ?>
                        </span>
                        <span class="entry-date"><?php echo date('d/m/Y H:i', strtotime($entry['created_at'])); ?></span>
                    </div>
                    <div class="entry-content">
                        <?php echo nl2br(htmlspecialchars($entry['content'])); ?>
                    </div>
                    <div class="entry-actions">
                        <button class="btn-view" onclick="viewEntry(<?php echo $entry['id']; ?>)">ดูเต็ม</button>
                        <button class="btn-edit" onclick="editEntry(<?php echo $entry['id']; ?>)">แก้ไข</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
            <div class="pagination">
                <?php if($page_num > 1): ?>
                    <a href="?page=entries&p=<?php echo $page_num-1; ?>" class="page-btn">« ก่อนหน้า</a>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=entries&p=<?php echo $i; ?>" 
                       class="page-btn <?php echo $i === $page_num ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if($page_num < $totalPages): ?>
                    <a href="?page=entries&p=<?php echo $page_num+1; ?>" class="page-btn">ถัดไป »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.entries-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(100, 255, 218, 0.2);
}

.page-title {
    color: var(--accent);
    font-size: 1.8rem;
    margin: 0;
}

.entries-stats {
    color: var(--muted);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--muted);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.entries-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
}

.entry-item {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
    border: 1px solid rgba(100, 255, 218, 0.08);
    border-radius: var(--radius);
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.entry-item:hover {
    border-color: rgba(100, 255, 218, 0.2);
    transform: translateY(-2px);
}

.entry-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.entry-mood {
    color: var(--accent);
    font-weight: 500;
}

.entry-date {
    color: var(--muted);
}

.entry-content {
    color: var(--text);
    line-height: 1.6;
    margin-bottom: 1rem;
    max-height: 200px;
    overflow: hidden;
    position: relative;
}

.entry-content::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 30px;
    background: linear-gradient(transparent, rgba(15, 23, 36, 0.8));
}

.entry-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-view, .btn-edit {
    padding: 0.4rem 0.8rem;
    border: 1px solid rgba(100, 255, 218, 0.3);
    background: transparent;
    color: var(--accent);
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: all 0.3s ease;
}

.btn-view:hover, .btn-edit:hover {
    background: rgba(100, 255, 218, 0.1);
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.page-btn {
    padding: 0.5rem 1rem;
    border: 1px solid rgba(100, 255, 218, 0.3);
    background: transparent;
    color: var(--accent);
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.page-btn:hover, .page-btn.active {
    background: rgba(100, 255, 218, 0.2);
}

@media (max-width: 768px) {
    .entries-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}
</style>

<script>
function viewEntry(id) {
    // TODO: แสดงป๊อปอัพดูบันทึกเต็ม
    alert('ฟีเจอร์ดูเต็มจะพัฒนาในอนาคต');
}

function editEntry(id) {
    // TODO: แก้ไขบันทึก
    alert('ฟีเจอร์แก้ไขจะพัฒนาในอนาคต');
}
</script>