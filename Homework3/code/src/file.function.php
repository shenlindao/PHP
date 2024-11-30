<?php

function readAllFunction(array $config): string
{
    $address = $config['storage']['address'];

    if (file_exists($address) && is_readable($address)) {
        $file = fopen($address, "rb");

        $contents = '';

        while (!feof($file)) {
            $contents .= fread($file, 100);
        }

        fclose($file);
        return $contents;
    } else {
        return handleError("Файл не существует");
    }
}

function addFunction(array $config): string
{
    $address = $config['storage']['address'];

    $name = readline("Введите имя: ");
    $date = readline("Введите дату рождения в формате ДД-ММ-ГГГГ: ");
    if (validate($date)) {
        $data = $name . ", " . $date . "\r\n";

        $fileHandler = fopen($address, 'a');

        if (fwrite($fileHandler, $data)) {
            return "Запись $data добавлена в файл $address";
        } else {
            return handleError("Произошла ошибка записи. Данные не сохранены");
        }

        fclose($fileHandler);
    } else {
        return handleError("Введена некорректная информация. Проверьте формат даты.\n");
    }
}

function searchFunction(array $config): string
{
    $address = $config['storage']['address'];
    $currentDate = date('d-m');

    $searchTerm = readline("Введите имя или дату рождения: ");
    $fileHandler = fopen($address, 'r');

    if ($fileHandler) {
        $found = false;
        $result = "";

        while (($line = fgets($fileHandler)) !== false) {
            $line = trim($line);
            $parts = explode(", ", $line);

            if (count($parts) === 2) {
                $name = $parts[0];
                $birthDate = $parts[1];

                if (strpos($name, $searchTerm) !== false || strpos($birthDate, $searchTerm) !== false) {
                    $result .= "Найдена запись: $name, Дата рождения: $birthDate";

                    if (strpos($birthDate, $currentDate) !== false) {
                        $result .= " (Сегодня день рождения!)";
                    }

                    $result .= "\n";
                    $found = true;
                }
            }
        }

        fclose($fileHandler);

        if ($found) {
            return $result;
        } else {
            return handleError("Данной записи не найдено.");
        }
    } else {
        return handleError("Не удалось открыть файл для чтения.");
    }
}

function deleteFunction(array $config): string
{
    $address = $config['storage']['address'];

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
        return "Удаление успешно завершено.";
    } else {
        return handleError("Строка с '{$searchTerm}' не найдена.");
    }
}

function clearFunction(array $config): string
{
    $address = $config['storage']['address'];

    if (file_exists($address) && is_readable($address)) {
        $file = fopen($address, "w");

        fwrite($file, '');

        fclose($file);
        return "Файл очищен";
    } else {
        return handleError("Файл не существует");
    }
}

function helpFunction()
{
    return handleHelp();
}

function readConfig(string $configAddress): array|false
{
    return parse_ini_file($configAddress, true);
}

function readProfilesDirectory(array $config): string
{
    $profilesDirectoryAddress = $config['profiles']['address'];

    if (!is_dir($profilesDirectoryAddress)) {
        mkdir($profilesDirectoryAddress);
    }

    $files = scandir($profilesDirectoryAddress);

    $result = "";

    if (count($files) > 2) {
        foreach ($files as $file) {
            if (in_array($file, ['.', '..']))
                continue;

            $result .= $file . "\r\n";
        }
    } else {
        $result .= "Директория пуста \r\n";
    }

    return $result;
}

function readProfile(array $config): string
{
    $profilesDirectoryAddress = $config['profiles']['address'];

    if (!isset($_SERVER['argv'][2])) {
        return handleError("Не указан файл профиля");
    }

    $profileFileName = $profilesDirectoryAddress . $_SERVER['argv'][2] . ".json";

    if (!file_exists($profileFileName)) {
        return handleError("Файл $profileFileName не существует");
    }

    $contentJson = file_get_contents($profileFileName);
    $contentArray = json_decode($contentJson, true);

    $info = "Имя: " . $contentArray['name'] . "\r\n";
    $info .= "Фамилия: " . $contentArray['lastname'] . "\r\n";

    return $info;
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
