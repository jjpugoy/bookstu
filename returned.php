<?php
require 'db.php';


$rows = $pdo->query(
    "SELECT bb.borrowID, bb.studID, CONCAT(s.studLName, ', ', s.studFName) AS student,
            bb.bookID, bk.bookTitle, bb.status
     FROM borrowedbook bb
     JOIN student s ON bb.studID = s.studID
     JOIN book bk   ON bb.bookID = bk.bookID
     WHERE bb.status = 'Returned'
     ORDER BY bb.borrowID DESC"
)->fetchAll();

pageHeader("Returned Books");
?>
<h2>Returned Books</h2>

<table>
    <tr><th>Borrow ID</th><th>Student</th><th>Book</th><th>Status</th></tr>
    <?php if (!$rows): ?><tr><td colspan="4">No returned books yet.</td></tr><?php endif; ?>
    <?php foreach ($rows as $r): ?>
    <tr>
        <td><?= e($r['borrowID']) ?></td>
        <td><?= e($r['studID'] . ' - ' . $r['student']) ?></td>
        <td><?= e($r['bookID'] . ' - ' . $r['bookTitle']) ?></td>
        <td><?= e($r['status']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php pageFooter(); ?>
