<?php

$host = "localhost";
$db   = "library";
$user = "root";
$pass = "";   

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }


function nextId(PDO $pdo, string $table, string $pk): int {
    return (int)$pdo->query("SELECT COALESCE(MAX($pk), 0) + 1 FROM $table")->fetchColumn();
}


function pageHeader($title) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> - Library System</title>
<style>
    body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f8; }
    nav { background: #3b5a2a; padding: 12px 20px; }
    nav a { color: #fff; text-decoration: none; margin-right: 20px; font-weight: bold; }
    nav a:hover { text-decoration: underline; }
    .container { max-width: 900px; margin: 20px auto; background: #fff; padding: 20px 30px; border-radius: 6px; }
    h2 { margin-top: 0; }
    label { display: inline-block; width: 140px; margin: 5px 0; }
    input[type=text], input[type=number], select { padding: 6px; width: 260px; }
    button { padding: 8px 16px; margin: 6px 6px 6px 0; cursor: pointer; }
    .msg { padding: 10px; margin: 12px 0; background: #eef6ff; border-left: 4px solid #3b5a2a; font-weight: bold; }
    .err { background: #fdecea; border-left-color: #c62828; color: #c62828; }
    table { border-collapse: collapse; width: 100%; margin-top: 15px; }
    th, td { border: 1px solid #999; padding: 6px 10px; text-align: left; }
    th { background: #ddd; }
    td form { margin: 0; }
</style>
</head>
<body>
<nav>
    <a href="authors.php">Authors</a>
    <a href="books.php">Books</a>
    <a href="students.php">Students</a>
    <a href="borrow.php">Borrow a Book</a>
    <a href="borrowed.php">Borrowed Books</a>
    <a href="returned.php">Returned Books</a>
</nav>
<div class="container">
<?php }

function pageFooter() { ?>
</div>
</body>
</html>
<?php }


function crudPage(PDO $pdo, array $cfg) {
    $table  = $cfg['table'];
    $pk     = $cfg['pk'];
    $fields = $cfg['fields'];

    $blank = array_fill_keys(array_merge([$pk], array_keys($fields)), '');
    $f = $blank;
    $msg = "";
    $isError = false;

    $find = function ($id) use ($pdo, $table, $pk) {
        $s = $pdo->prepare("SELECT * FROM $table WHERE $pk = ?");
        $s->execute([$id]);
        return $s->fetch();
    };

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        foreach ($f as $k => $_) { $f[$k] = trim($_POST[$k] ?? ''); }

        
        $missing = false;
        $badNumber = false;
        foreach ($fields as $k => $def) {
            if ($f[$k] === '' && empty($def['optional'])) { $missing = true; }
            if ($def['type'] === 'number' && $f[$k] !== '' && (!ctype_digit($f[$k]))) { $badNumber = true; }
        }
        $idGiven = $f[$pk] !== '';
        $idValid = ctype_digit($f[$pk]);

       
        if ($action === 'add') {
            if ($missing) {
                $msg = "PLEASE FILL IN ALL REQUIRED FIELDS (the ID is optional - it is numbered automatically)"; $isError = true;
            } elseif ($badNumber || ($idGiven && !$idValid)) {
                $msg = "NUMBER FIELDS MUST CONTAIN WHOLE NUMBERS ONLY"; $isError = true;
            } elseif ($idGiven && $find($f[$pk])) {
                $msg = "RECORD ALREADY EXISTS"; $isError = true;
            } else {
                $id = $idGiven ? (int)$f[$pk] : nextId($pdo, $table, $pk);
                $cols  = implode(', ', array_merge([$pk], array_keys($fields)));
                $marks = implode(', ', array_fill(0, count($fields) + 1, '?'));
                $vals  = [$id];
                foreach ($fields as $k => $_) { $vals[] = $f[$k]; }
                $pdo->prepare("INSERT INTO $table ($cols) VALUES ($marks)")->execute($vals);
                $msg = $cfg['title'] . " SUCCESSFULLY ADDED (ID: $id)";
                $f = $blank;
            }
        }

        
        elseif ($action === 'search') {
            if (!$idGiven || !$idValid) {
                $msg = "ENTER THE " . strtoupper($cfg['title']) . " ID TO SEARCH"; $isError = true;
            } else {
                $row = $find($f[$pk]);
                if ($row) {
                    foreach ($blank as $k => $_) { $f[$k] = $row[$k]; }
                    $msg = "RECORD FOUND - you may now update or delete it";
                } else {
                    $f = $blank;
                    $msg = "RECORD NOT FOUND"; $isError = true;
                }
            }
        }

        
        elseif ($action === 'update') {
            if (!$idGiven || !$idValid || !$find($f[$pk])) {
                $msg = "SEARCH FOR A RECORD FIRST"; $isError = true;
            } elseif ($missing || $badNumber) {
                $msg = "PLEASE FILL IN ALL REQUIRED FIELDS CORRECTLY"; $isError = true;
            } else {
                $set  = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
                $vals = [];
                foreach ($fields as $k => $_) { $vals[] = $f[$k]; }
                $vals[] = $f[$pk];
                $pdo->prepare("UPDATE $table SET $set WHERE $pk = ?")->execute($vals);
                $msg = $cfg['title'] . " SUCCESSFULLY UPDATED";
                $f = $blank;
            }
        }

        
        elseif ($action === 'delete') {
            if (!$idGiven || !$idValid || !$find($f[$pk])) {
                $msg = "SEARCH FOR A RECORD FIRST"; $isError = true;
            } else {
                try {
                    $pdo->prepare("DELETE FROM $table WHERE $pk = ?")->execute([$f[$pk]]);
                    $msg = $cfg['title'] . " SUCCESSFULLY DELETED";
                    $f = $blank;
                } catch (PDOException $ex) {
                    $msg = "CANNOT DELETE: this record is still used by other records"; $isError = true;
                }
            }
        }
    }

    
    $rows = $pdo->query($cfg['list'])->fetchAll();

    pageHeader($cfg['title'] . " Management");
    ?>
    <h2><?= e($cfg['title']) ?> Management</h2>

    <form method="post">
        <label><?= e($cfg['title']) ?> ID:</label>
        <input type="number" name="<?= e($pk) ?>" value="<?= e($f[$pk]) ?>" placeholder="optional when adding"><br>

        <?php foreach ($fields as $k => $def): ?>
            <label><?= e($def['label']) ?>:</label>
            <?php if ($def['type'] === 'select'): ?>
                <select name="<?= e($k) ?>">
                    <option value="">-- select --</option>
                    <?php foreach ($def['options'] as $val => $label): ?>
                        <option value="<?= e($val) ?>" <?= (string)$f[$k] === (string)$val ? 'selected' : '' ?>>
                            <?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="<?= $def['type'] === 'number' ? 'number' : 'text' ?>" <?= $def['type'] === 'number' ? 'min="0"' : '' ?>
                       name="<?= e($k) ?>" value="<?= e($f[$k]) ?>">
            <?php endif; ?>
            <br>
        <?php endforeach; ?>

        <button name="action" value="add">ADD</button>
        <button name="action" value="search">SEARCH</button>
        <button name="action" value="update">UPDATE</button>
        <button name="action" value="delete" onclick="return confirm('Delete this record?')">DELETE</button>
    </form>

    <?php if ($msg): ?><div class="msg <?= $isError ? 'err' : '' ?>"><?= e($msg) ?></div><?php endif; ?>

    <table>
        <tr><?php foreach ($cfg['cols'] as $header): ?><th><?= e($header) ?></th><?php endforeach; ?></tr>
        <?php if (!$rows): ?><tr><td colspan="<?= count($cfg['cols']) ?>">No records yet.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): ?>
        <tr><?php foreach ($cfg['cols'] as $key => $_): ?><td><?= e($r[$key]) ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
    </table>
    <?php
    pageFooter();
}
