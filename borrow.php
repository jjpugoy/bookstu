<?php
require 'db.php';

$msg = "";
$isError = false;
$selBook = $_POST['bookID'] ?? '';
$selStud = $_POST['studID'] ?? '';


function bookAvailability(PDO $pdo, $bookID) {
    $s = $pdo->prepare(
        "SELECT b.bookID, b.bookTitle, b.bookCopies,
                (SELECT COUNT(*) FROM borrowedbook bb
                 WHERE bb.bookID = b.bookID AND bb.status = 'Borrowed') AS borrowed
         FROM book b WHERE b.bookID = ?"
    );
    $s->execute([$bookID]);
    $row = $s->fetch();
    if ($row) { $row['available'] = $row['bookCopies'] - $row['borrowed']; }
    return $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    
    if ($action === 'inquire') {
        if ($selBook === '') {
            $msg = "PLEASE SELECT A BOOK"; $isError = true;
        } else {
            $b = bookAvailability($pdo, $selBook);
            if (!$b) {
                $msg = "BOOK NOT FOUND"; $isError = true;
            } elseif ($b['available'] > 0) {
                $msg = "AVAILABLE: \"{$b['bookTitle']}\" has {$b['available']} of {$b['bookCopies']} copies available";
            } else {
                $msg = "NOT AVAILABLE: all {$b['bookCopies']} copies of \"{$b['bookTitle']}\" are borrowed";
                $isError = true;
            }
        }
    }

    
    elseif ($action === 'borrow') {
        if ($selStud === '' || $selBook === '') {
            $msg = "PLEASE SELECT A STUDENT AND A BOOK"; $isError = true;
        } else {
            $b = bookAvailability($pdo, $selBook);

            $dup = $pdo->prepare(
                "SELECT COUNT(*) FROM borrowedbook WHERE studID = ? AND bookID = ? AND status = 'Borrowed'"
            );
            $dup->execute([$selStud, $selBook]);

            if (!$b) {
                $msg = "BOOK NOT FOUND"; $isError = true;
            } elseif ($b['available'] <= 0) {
                $msg = "NOT AVAILABLE: no copies of \"{$b['bookTitle']}\" are left"; $isError = true;
            } elseif ($dup->fetchColumn() > 0) {
                $msg = "THIS STUDENT HAS ALREADY BORROWED THIS BOOK AND NOT YET RETURNED IT"; $isError = true;
            } else {
                $id = nextId($pdo, 'borrowedbook', 'borrowID');
                $pdo->prepare("INSERT INTO borrowedbook (borrowID, studID, bookID, status) VALUES (?,?,?,'Borrowed')")
                    ->execute([$id, $selStud, $selBook]);
                $msg = "BOOK SUCCESSFULLY BORROWED (Borrow ID: $id)";
                $selBook = $selStud = '';
            }
        }
    }
}

$books = $pdo->query("SELECT bookID, bookTitle FROM book ORDER BY bookTitle")->fetchAll();
$students = $pdo->query(
    "SELECT studID, CONCAT(studLName, ', ', studFName) AS name FROM student ORDER BY studLName"
)->fetchAll();


$all = $pdo->query(
    "SELECT b.bookID, b.bookTitle, b.bookCopies,
            (SELECT COUNT(*) FROM borrowedbook bb
             WHERE bb.bookID = b.bookID AND bb.status = 'Borrowed') AS borrowed
     FROM book b ORDER BY b.bookTitle"
)->fetchAll();

pageHeader("Borrow a Book");
?>
<h2>Book Borrowing</h2>

<form method="post">
    <label>Student:</label>
    <select name="studID">
        <option value="">-- select student --</option>
        <?php foreach ($students as $s): ?>
            <option value="<?= e($s['studID']) ?>" <?= (string)$selStud === (string)$s['studID'] ? 'selected' : '' ?>>
                <?= e($s['studID'] . ' - ' . $s['name']) ?></option>
        <?php endforeach; ?>
    </select><br>

    <label>Book:</label>
    <select name="bookID">
        <option value="">-- select book --</option>
        <?php foreach ($books as $b): ?>
            <option value="<?= e($b['bookID']) ?>" <?= (string)$selBook === (string)$b['bookID'] ? 'selected' : '' ?>>
                <?= e($b['bookID'] . ' - ' . $b['bookTitle']) ?></option>
        <?php endforeach; ?>
    </select><br>

    <button name="action" value="inquire">CHECK AVAILABILITY</button>
    <button name="action" value="borrow">BORROW</button>
</form>

<?php if ($msg): ?><div class="msg <?= $isError ? 'err' : '' ?>"><?= e($msg) ?></div><?php endif; ?>

<h3>Book Availability</h3>
<table>
    <tr><th>Book ID</th><th>Title</th><th>Copies</th><th>Borrowed</th><th>Available</th></tr>
    <?php foreach ($all as $a): $avail = $a['bookCopies'] - $a['borrowed']; ?>
    <tr>
        <td><?= e($a['bookID']) ?></td>
        <td><?= e($a['bookTitle']) ?></td>
        <td><?= e($a['bookCopies']) ?></td>
        <td><?= e($a['borrowed']) ?></td>
        <td><b><?= e($avail) ?></b></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php pageFooter(); ?>
