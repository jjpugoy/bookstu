<?php
require 'db.php';

crudPage($pdo, [
    'title' => 'Student',
    'table' => 'student',
    'pk'    => 'studID',
    'fields' => [
        'studFName' => ['label' => 'First Name',  'type' => 'text'],
        'studMName' => ['label' => 'Middle Name', 'type' => 'text', 'optional' => true],
        'studLName' => ['label' => 'Last Name',   'type' => 'text'],
    ],
    'list' => "SELECT * FROM student ORDER BY studID",
    'cols' => [
        'studID' => 'Student ID', 'studFName' => 'First Name',
        'studMName' => 'Middle Name', 'studLName' => 'Last Name',
    ],
]);
