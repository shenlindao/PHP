<?php
/* Задания 1 и 2
    // 1. Реализовать основные 4 арифметические операции в виде функции с тремя параметрами – два параметра это числа, третий – операция. Обязательно использовать оператор return.
    // 2. Реализовать функцию с тремя параметрами: function mathOperation($arg1, $arg2, $operation), где $arg1, $arg2 – значения аргументов, $operation – строка с названием операции.
    // В зависимости от переданного значения операции выполнить одну из арифметических операций (использовать функции из пункта 3) и вернуть полученное значение (использовать switch).

    function add($a, $b) {
        return $a + $b;
    }

    function subtract($a, $b) {
        return $a - $b;
    }

    function multiply($a, $b) {
        return $a * $b;
    }

    function divide($a, $b) {
        if ($b != 0) {
            return $a / $b;
        } else {
            return "Ошибка: деление на ноль.";
        }
    }

    function mathOperation($arg1, $arg2, $operation) {
        switch ($operation) {
            case 'add':
                return add($arg1, $arg2);
            case 'subtract':
                return subtract($arg1, $arg2);
            case 'multiply':
                return multiply($arg1, $arg2);
            case 'divide':
                return divide($arg1, $arg2);
            default:
                return "Неизвестная операция.";
        }
    }

    echo mathOperation(10, 5, 'add');        // 15
    echo mathOperation(10, 5, 'subtract');   // 5
    echo mathOperation(10, 5, 'multiply');   // 50
    echo mathOperation(10, 0, 'divide');     // Ошибка: деление на ноль.
    echo mathOperation(10, 5, 'mod');        // Неизвестная операция.
*/

/* Задание 3
    // 3. Объявить массив, в котором в качестве ключей будут использоваться названия областей, а в качестве значений – массивы с названиями городов из соответствующей области.
    // Вывести в цикле значения массива, чтобы результат был таким:
    // Московская область: Москва, Зеленоград, Клин
    // Ленинградская область: Санкт-Петербург, Всеволожск, Павловск, Кронштадт
    // Рязанская область … (названия городов можно найти на maps.yandex.ru).

    $regions = [
        'Московская область' => ['Москва', 'Зеленоград', 'Клин'],
        'Ленинградская область' => ['Санкт-Петербург', 'Всеволожск', 'Павловск', 'Кронштадт'],
        'Рязанская область' => ['Рязань', 'Скопин', 'Михайлов'],
        'Воронежская область' => ['Воронеж', 'Борисоглебск', 'Россошь']
    ];

    // Вывод значений массива
    foreach ($regions as $region => $cities) {
        echo $region . ': ' . implode(', ', $cities) . PHP_EOL;
    }
*/

/* Задание 4
    // 4. Объявить массив, индексами которого являются буквы русского языка, а значениями – соответствующие латинские буквосочетания
    // (‘а’=> ’a’, ‘б’ => ‘b’, ‘в’ => ‘v’, ‘г’ => ‘g’, …, ‘э’ => ‘e’, ‘ю’ => ‘yu’, ‘я’ => ‘ya’).
    // Написать функцию транслитерации строк.

    $translit = [
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'yo',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'y',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'ts',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'shch',
        'ы' => 'y',
        'э' => 'e',
        'ю' => 'yu',
        'я' => 'ya',
        ' ' => ' ',
        '.' => '.',
        ',' => ',',
        '?' => '?',
        '!' => '!',
        '"' => '"',
        "'" => "'",
        ';' => ';',
        ':' => ':'
    ];


    function transliterate($str, $translit)
    {
        $str = mb_strtolower($str, 'UTF-8');
        $result = '';

        for ($i = 0; $i < mb_strlen($str, 'UTF-8'); $i++) {
            $char = mb_substr($str, $i, 1, 'UTF-8');

            if (isset($translit[$char])) {
                $result .= $translit[$char];
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    $originalText = 'Привет, как дела?';
    $transliteratedText = transliterate($originalText, $translit);

    echo "Исходный текст: $originalText\n";
    echo "Транслитерированный текст: $transliteratedText\n";

*/

/* Задание 5
    // 5. *С помощью рекурсии организовать функцию возведения числа в степень. Формат: function power($val, $pow), где $val – заданное число, $pow – степень.

    function power($val, $pow) {
        if ($pow == 0) {
            return 1;
        }
    
        return $val * power($val, $pow - 1);
    }

    echo power(5, 0); // 5^0 = 1
    echo power(2, 3); // 2^3 = 8
*/

/* Задание 6
    // 6. *Написать функцию, которая вычисляет текущее время и возвращает его в формате с правильными склонениями, например:
    // 22 часа 15 минут
    // 21 час 43 минуты.

    function timeWithDeclension() {
        date_default_timezone_set('Europe/Moscow');
        $currentTime = time();
        $hours = date('G', $currentTime);
        $minutes = date('i', $currentTime);

        if ($hours % 10 == 1 && $hours % 100 != 11) {
            $hourWord = "час";
        } elseif (($hours % 10 >= 2 && $hours % 10 <= 4) && !($hours % 100 >= 12 && $hours % 100 <= 14)) {
            $hourWord = "часа";
        } else {
            $hourWord = "часов";
        }

        if ($minutes % 10 == 1 && $minutes % 100 != 11) {
            $minuteWord = "минута";
        } elseif (($minutes % 10 >= 2 && $minutes % 10 <= 4) && !($minutes % 100 >= 12 && $minutes % 100 <= 14)) {
            $minuteWord = "минуты";
        } else {
            $minuteWord = "минут";
        }

        return "$hours $hourWord $minutes $minuteWord";
    }

    echo timeWithDeclension();
*/

// docker run --rm -v ${pwd}/php-cli/:/cli php:8.2-cli php /cli/start.php