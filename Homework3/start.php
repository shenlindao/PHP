<?php
/* Задание 1
    // 1. Обработка ошибок. Посмотрите на реализацию функции в файле fwrite-cli.php в исходниках. Может ли пользователь ввести некорректную информацию (например, дату в виде 12-50-1548)?
    // Какие еще некорректные данные могут быть введены? Исправьте это, добавив соответствующие обработки ошибок.

    $address = '/cli/birthdays.txt';

    $name = readline("Введите имя: ");
    $date = readline("Введите дату рождения в формате ДД-ММ-ГГГГ: ");

    if (validate($date)) {
        $data = $name . ", " . $date . "\r\n";

        $file = fopen($address, 'a');

        if (fwrite($file, $data)) {
            echo "Запись $data добавлена в файл $address";
        } else {
            echo "Произошла ошибка записи. Данные не сохранены";
        }

        fclose($file);
    } else {
        echo "Введена некорректная информация";
    }

    function validate(string $date): bool
    {
        $dateBlocks = explode("-", $date);

        if (count($dateBlocks) !== 3) {
            return false;
        }

        $day = (int)$dateBlocks[0];
        $month = (int)$dateBlocks[1];
        $year = (int)$dateBlocks[2];

        if ($day < 1 || $day > 31) {
            return false;
        }

        if ($month < 1 || $month > 12) {
            return false;
        }

        if ($year < 1000 || $year > date('Y')) {
            return false;
        }

        if (!checkdate($month, $day, $year)) {
            return false;
        }

        return true;
    }
*/

/* Задание 2
    // 2. Поиск по файлу. Когда мы научились сохранять в файле данные, нам может быть интересно не только чтение, но и поиск по нему.
    // Например, нам надо проверить, кого нужно поздравить сегодня с днем рождения среди пользователей, хранящихся в формате:
    // Василий Васильев, 05-06-1992
    // И здесь нам на помощь снова приходят циклы.file
    // Понадобится цикл, который будет построчно читать файл и искать совпадения в дате.
    // Для обработки строки пригодится функция explode, а для получения текущей даты – date.

    $address = '/cli/birthdays.txt';
    $currentDate = date('d-m');
    $file = fopen($address, 'r');

    if ($file) {
        $found = false;

        while (($line = fgets($file)) !== false) {
            $line = trim($line);
            $parts = explode(", ", $line);

            if (count($parts) === 2) {
                $name = $parts[0];
                $birthDate = $parts[1];

                $birthDate = explode("-", $birthDate);
                $birthDayMonth = $birthDate[0] . '-' . $birthDate[1];

                if ($birthDayMonth === $currentDate) {
                    echo "Поздравляем с днем рождения: $name, Дата: $birthDate[0]-$birthDate[1]-$birthDate[2]\n";
                    $found = true;
                }
            }
        }

        if (!$found) {
            echo "Сегодня нет ни у кого дня рождения.\n";
        }

        fclose($file);
    } else {
        echo "Не удалось открыть файл для чтения.\n";
    }
*/

/* Задание 3
    // 3. Удаление строки. Когда мы научились искать, надо научиться удалять конкретную строку.
    // Запросите у пользователя имя или дату для удаляемой строки.
    // После ввода либо удалите строку, оповестив пользователя, либо сообщите о том, что строка не найдена.

    $address = '/cli/birthdays.txt';
    $searchTerm = readline("Введите имя или дату рождения для удаления: ");
    $fileContents = file($address, FILE_IGNORE_NEW_LINES);
    $found = false;
    $newContents = [];

    foreach ($fileContents as $line) {
        if (strpos($line, $searchTerm) !== false) {
            echo "Строка '{$line}' удалена из файла.\n";
            $found = true;
        } else {
            $newContents[] = $line;
        }
    }

    if ($found) {
        file_put_contents($address, implode(PHP_EOL, $newContents) . PHP_EOL);
    } else {
        echo "Строка с '{$searchTerm}' не найдена.\n";
    }
*/

// /* Задание 4
    // 4. Добавьте новые функции в итоговое приложение работы с файловым хранилищем.

    $address = '/cli/birthdays.txt';

    // Главное меню
    while (true) {
        echo "\nВыберите действие:\n";
        echo "1. Добавить запись\n";
        echo "2. Поиск по дню рождения\n";
        echo "3. Удалить запись\n";
        echo "4. Выход\n";
        $choice = readline("Введите номер действия: ");

        switch ($choice) {
            case 1:
                addRecord($address);
                break;
            case 2:
                searchBirthday($address);
                break;
            case 3:
                deleteRecord($address);
                break;
            case 4:
                echo "Выход из приложения.\n";
                exit;
            default:
                echo "Некорректный выбор. Попробуйте снова.\n";
        }
    }

    function addRecord($address) {
        $name = readline("Введите имя: ");
        $date = readline("Введите дату рождения в формате ДД-ММ-ГГГГ: ");

        if (validate($date)) {
            $data = $name . ", " . $date . "\r\n";
            $file = fopen($address, 'a');
            if (fwrite($file, $data)) {
                echo "Запись добавлена в файл $address\n";
            } else {
                echo "Произошла ошибка при записи в файл.\n";
            }
            fclose($file);
        } else {
            echo "Введена некорректная информация. Проверьте формат даты.\n";
        }
    }

    // Функция для поиска пользователей по дню рождения сегодня
    function searchBirthday($address) {
        $currentDate = date('d-m');
        $file = fopen($address, 'r');

        if ($file) {
            $found = false;

            while (($line = fgets($file)) !== false) {
                $line = trim($line);
                $parts = explode(", ", $line);

                if (count($parts) === 2) {
                    $name = $parts[0];
                    $birthDate = $parts[1];

                    $birthDate = explode("-", $birthDate);
                    $birthDayMonth = $birthDate[0] . '-' . $birthDate[1];

                    if ($birthDayMonth === $currentDate) {
                        echo "Поздравляем с днем рождения: $name, Дата: $birthDate[0]-$birthDate[1]-$birthDate[2]\n";
                        $found = true;
                    }
                }
            }

            if (!$found) {
                echo "Сегодня нет ни у кого дня рождения.\n";
            }

            fclose($file);
        } else {
            echo "Не удалось открыть файл для чтения.\n";
        }
    }

    // Функция для удаления записи
    function deleteRecord($address) {
        $searchTerm = readline("Введите имя или дату рождения для удаления: ");
        $fileContents = file($address, FILE_IGNORE_NEW_LINES);
        $found = false;
        $newContents = [];

        foreach ($fileContents as $line) {
            if (strpos($line, $searchTerm) !== false) {
                echo "Строка '{$line}' удалена из файла.\n";
                $found = true;
            } else {
                $newContents[] = $line;
            }
        }

        if ($found) {
            file_put_contents($address, implode(PHP_EOL, $newContents) . PHP_EOL);
        } else {
            echo "Строка с '{$searchTerm}' не найдена.\n";
        }
    }

    // Функция для валидации формата даты
    function validate(string $date): bool {
        $dateBlocks = explode("-", $date);

        if (count($dateBlocks) !== 3) {
            return false;
        }

        $day = (int)$dateBlocks[0];
        $month = (int)$dateBlocks[1];
        $year = (int)$dateBlocks[2];

        if ($day < 1 || $day > 31) {
            return false;
        }

        if ($month < 1 || $month > 12) {
            return false;
        }

        if ($year < 1000 || $year > date('Y')) {
            return false;
        }

        if (!checkdate($month, $day, $year)) {
            return false;
        }

        return true;
    }
// */

// docker run --rm -it -v ${PWD}:/cli php:8.2-cli php /cli/start.php