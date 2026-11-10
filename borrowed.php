<?php
require 'db.php';

$msg = "";
$isError = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrowID = $_POST['borrowID'] ?? '';

    $s = $pdo->prepare("UPDATE borrowedbook SET status = 'Returned' WHERE borrowID = ? AND status = 'Borrowed'");
    $s->execute([$borrowID]);

    if ($s->rowCount() > 0) {
        $msg = "BOOK SUCCESSFULLY RETURNED (Borrow ID: " . $borrowID . ")";
    } else {
        $msg = "RECORD NOT FOUND OR ALREADY RETURNED"; $isError = true;
    }
}


$rows = $pdo->query(
    "SELECT bb.borrowID, bb.studID, CONCAT(s.studLName, ', ', s.studFName) AS student,
            bb.bookID, bk.bookTitle, bb.status
     FROM borrowedbook bb
     JOIN student s ON bb.studID = s.studID
     JOIN book bk   ON bb.bookID = bk.bookID
     WHERE bb.status = 'Borrowed'
     ORDER BY bb.borrowID DESC"
)->fetchAll();

pageHeader("Borrowed Books");
?>
<h2>Borrowed Books (not yet returned)</h2>

<?php if ($msg): ?><div class="msg <?= $isError ? 'err' : '' ?>"><?= e($msg) ?></div><?php endif; ?>

<table>
    <tr><th>Borrow ID</th><th>Student</th><th>Book</th><th>Status</th><th>Action</th></tr>
    <?php if (!$rows): ?><tr><td colspan="5">No borrowed books at the moment.</td></tr><?php endif; ?>
    <?php foreach ($rows as $r): ?>
    <tr>
        <td><?= e($r['borrowID']) ?></td>
        <td><?= e($r['studID'] . ' - ' . $r['student']) ?></td>
        <td><?= e($r['bookID'] . ' - ' . $r['bookTitle']) ?></td>
        <td><?= e($r['status']) ?></td>
        <td>
            <form method="post">
                <input type="hidden" name="borrowID" value="<?= e($r['borrowID']) ?>">
                <button type="submit" onclick="return confirm('Mark this book as returned?')">RETURN</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php pageFooter(); ?>
