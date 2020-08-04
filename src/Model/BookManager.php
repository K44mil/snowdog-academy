<?php

namespace Snowdog\Academy\Model;

use Snowdog\Academy\Core\Database;

class BookManager
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function create(string $title, string $author, string $isbn, bool $forChildren = FALSE): int
    {
        $statement = $this->database->prepare('INSERT INTO books (title, author, isbn, for_children) VALUES (:title, :author, :isbn, :for_children)');
        $binds = [
            ':title' => $title,
            ':author' => $author,
            ':isbn' => $isbn,
            ':for_children' => $forChildren
        ];
        $statement->execute($binds);

        return (int) $this->database->lastInsertId();
    }

    public function update(int $id, string $title, string $author, string $isbn): void
    {
        $statement = $this->database->prepare('UPDATE books SET title = :title, author = :author, isbn = :isbn WHERE id = :id');
        $binds = [
            ':id' => $id,
            ':title' => $title,
            ':author' => $author,
            ':isbn' => $isbn
        ];

        $statement->execute($binds);
    }

    public function getBookById(int $id)
    {
        $query = $this->database->prepare('SELECT * FROM books WHERE id = :id');
        $query->setFetchMode(Database::FETCH_CLASS, Book::class);
        $query->execute([':id' => $id]);

        return $query->fetch(Database::FETCH_CLASS);
    }

    public function getAllBooks(): array
    {
        $query = $this->database->query('SELECT * FROM books');

        return $query->fetchAll(Database::FETCH_CLASS, Book::class);
    }

    public function getAllBorrowedBooks(): array
    {
        $queryString = "SELECT b.*, br.borrowed_at
                        FROM books b
                        INNER JOIN borrows br
                        WHERE b.id = br.book_id";
        
        $query = $this->database->query($queryString);

        return $query->fetchAll(Database::FETCH_CLASS, Book::class);
    }

    public function getAvailableBooks(): array
    {
        $query = $this->database->query('SELECT * FROM books WHERE borrowed = 0');

        return $query->fetchAll(Database::FETCH_CLASS, Book::class);
    }
}
