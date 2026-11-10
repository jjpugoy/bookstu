CREATE DATABASE IF NOT EXISTS library;
USE library;

CREATE TABLE IF NOT EXISTS author (
    authID    INT PRIMARY KEY,
    authLName VARCHAR(100) NOT NULL,
    authFName VARCHAR(100) NOT NULL,
    authMName VARCHAR(100) NULL
);

CREATE TABLE IF NOT EXISTS student (
    studID    INT PRIMARY KEY,
    studFName VARCHAR(100) NOT NULL,
    studMName VARCHAR(100) NULL,
    studLName VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS book (
    bookID     INT PRIMARY KEY,
    bookTitle  VARCHAR(200) NOT NULL,
    bookCopies INT NOT NULL,
    authID     INT NOT NULL,
    FOREIGN KEY (authID) REFERENCES author(authID)
);

CREATE TABLE IF NOT EXISTS borrowedbook (
    borrowID INT PRIMARY KEY,
    studID   INT NOT NULL,
    bookID   INT NOT NULL,
    status   VARCHAR(20) NOT NULL,      
    FOREIGN KEY (studID) REFERENCES student(studID),
    FOREIGN KEY (bookID) REFERENCES book(bookID)
);


INSERT IGNORE INTO author (authID, authLName, authFName, authMName) VALUES
(1, 'Rowling', 'Joanne', 'K'),
(2, 'Tolkien', 'John', 'R'),
(3, 'Sparks',  'Nicholas', '');

INSERT IGNORE INTO student (studID, studFName, studMName, studLName) VALUES
(1, 'Juan',  'D', 'Cruz'),
(2, 'Maria', 'S', 'Santos');

INSERT IGNORE INTO book (bookID, bookTitle, bookCopies, authID) VALUES
(1, 'Harry Potter',          3, 1),
(2, 'The Lord of the Rings', 2, 2),
(3, 'The Notebook',          1, 3);
