<?php
require 'db.php';

crudPage($pdo, [
    'title' => 'Author',
    'table' => 'author',
    'pk'    => 'authID',
    'fields' => [
        'authLName' => ['label' => 'Last Name',   'type' => 'text'],
        'authFName' => ['label' => 'First Name',  'type' => 'text'],
        'authMName' => ['label' => 'Middle Name', 'type' => 'text', 'optional' => true],
    ],
    'list' => "SELECT * FROM author ORDER BY authID",
    'cols' => [
        'authID' => 'Author ID', 'authLName' => 'Last Name',
        'authFName' => 'First Name', 'authMName' => 'Middle Name',
    ],
]);
