<?php

abstract class Book
{
    protected $name;
    protected $author;
    protected $year;
    protected static $readCount = 0;

    public function __construct($name, $author, $year)
    {
        $this->name = $name;
        $this->author = $author;
        $this->year = $year;
    }

    // Получение информации о книге
    public function getDetails()
    {
        return "Название: {$this->name}\nАвтор: {$this->author}\nГод издания: {$this->year}.";
    }

    // Абстрактный метод получения книги
    abstract public function getBook();

    // Увеличить количество прочтений
    public function markAsRead()
    {
        self::$readCount++;
    }

    // Вывести общее количество прочитанных книг
    public static function getReadCount()
    {
        return self::$readCount;
    }
}

class ElectronicBook extends Book
{
    private $link;
    public function __construct($name, $author, $year, $link)
    {
        parent::__construct($name, $author, $year);
        $this->link = $link;
    }

    public function getBook()
    {
        return "{$this->getDetails()}\nСкачать по ссылке: {$this->link}";
    }
}

class PaperBook extends Book
{
    private $adress;
    public function __construct($name, $author, $year, $adress)
    {
        parent::__construct($name, $author, $year);
        $this->adress = $adress;
    }
    public function getBook()
    {
        return "{$this->getDetails()}\nПолучить по адресу: {$this->adress}";
    }
}

$ebook = new ElectronicBook("1984", "Джордж Оруэлл", 1949, "http://test.ru/1984");
$paperbook = new PaperBook("Властелин Колец", "Толкин Д. Р. Р", 1954, "ул. Литературная, д. 1");

echo $ebook->getBook() . "\n\n";
echo $paperbook->getBook() . "\n\n";

$ebook->markAsRead();
$paperbook->markAsRead();

echo "Всего прочитано книг: " . Book::getReadCount() . "\n";

// docker run --rm -v ${pwd}/php-cli/:/cli php:8.2-cli php /cli/start.php