<?php

namespace Snowdog\Academy\Controller\Admin;

use Snowdog\Academy\Model\Book;
use Snowdog\Academy\Model\BookManager;

class Books extends AdminAbstract
{
    private BookManager $bookManager;
    private ?Book $book;

    public function __construct(BookManager $bookManager)
    {
        parent::__construct();
        $this->bookManager = $bookManager;
        $this->book = null;
    }

    public function index(): void
    {
        require __DIR__ . '/../../view/admin/books/list.phtml';
    }

    public function newBook(): void
    {
        require __DIR__ . '/../../view/admin/books/edit.phtml';
    }

    public function newBookPost(): void
    {
        $title = $_POST['title'];
        $author = $_POST['author'];
        $isbn = $_POST['isbn'];

        if (empty($title) || empty($author) || empty($isbn)) {
            $_SESSION['flash'] = 'Missing data';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            return;
        }

        $this->bookManager->create($title, $author, $isbn);

        $_SESSION['flash'] = "Book $title by $author saved!";
        header('Location: /admin');
    }

    public function edit(int $id): void
    {
        $book = $this->bookManager->getBookById($id);
        if ($book instanceof Book) {
            $this->book = $book;
            require __DIR__ . '/../../view/admin/books/edit.phtml';
        } else {
            header('HTTP/1.0 404 Not Found');
            require __DIR__ . '/../../view/errors/404.phtml';
        }
    }

    public function editPost(int $id): void
    {
        $title = $_POST['title'];
        $author = $_POST['author'];
        $isbn = $_POST['isbn'];

        if (empty($title) || empty($author) || empty($isbn)) {
            $_SESSION['flash'] = 'Missing data';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            return;
        }

        $this->bookManager->update($id, $title, $author, $isbn);

        $_SESSION['flash'] = "Book $title by $author saved!";
        header('Location: /admin');
    }

    public function loadBooksFromCsv(): void
    {
        require __DIR__ . '/../../view/admin/books/load_from_csv.phtml';
    }

    public function loadBooksFromCsvPost(): void
    {
        if (isset($_FILES['csvFile'])) {
            $filename = $_FILES['csvFile']['tmp_name'] ?? '';
            if (!file_exists($filename)) {
                $_SESSION['flash'] = 'Unable to read a file.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                return;
            }

            $filenameExploded = explode('.', $_FILES['csvFile']['name']);
            $fileExt = $filenameExploded[count($filenameExploded) - 1];
            if ($fileExt !== 'csv') {
                $_SESSION['flash'] = 'Wrong file type.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                return;
            }

            $booksCount = 0;
            $savedBooks = 0;
            $handle = fopen($filename, "r");
            while (($line = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if ($this->bookManager->create($line[0], $line[1], $line[2]))
                    $savedBooks++;
                $booksCount++;
            }
            fclose($handle);

            $_SESSION['flash'] = "$savedBooks of $booksCount books saved correctly.";
            header('Location: /admin');
        }
    }

    private function getBooks(): array
    {
        return $this->bookManager->getAllBooks();
    }
}
