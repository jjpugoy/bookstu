<?php
require 'db.php';


$authors = [];
foreach ($pdo->query("SELECT authID, authLName, authFName FROM author ORDER BY authLName") as $a) {
    $authors[$a['authID']] = $a['authID'] . ' - ' . $a['authLName'] . ', ' . $a['authFName'];
}

crudPage($pdo, [
    'title' => 'Book',
    'table' => 'book',
    'pk'    => 'bookID',
    'fields' => [
        'bookTitle'  => ['label' => 'Title',  'type' => 'text'],
        'bookCopies' => ['label' => 'Copies', 'type' => 'number'],
        'authID'     => ['label' => 'Author', 'type' => 'select', 'options' => $authors],
    ],
    'list' => "SELECT b.bookID, b.bookTitle, b.bookCopies,
                      CONCAT(a.authLName, ', ', a.authFName) AS author
               FROM book b JOIN author a ON b.authID = a.authID
               ORDER BY b.bookID",
    'cols' => [
        'bookID' => 'Book ID', 'bookTitle' => 'Title',
        'bookCopies' => 'Copies', 'author' => 'Author',
    ],
]);
