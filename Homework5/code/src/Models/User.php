<?php

namespace Geekbrains\Application1\Models;

class User
{

    private ?string $userName;
    private ?int $userBirthday;

    private static string $storageAddress = '/storage/birthdays.txt';

    public function __construct(string $name = null, int $birthday = null)
    {
        $this->userName = $name;
        $this->userBirthday = $birthday;
    }

    public function setName(string $userName): void
    {
        $this->userName = $userName;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getUserBirthday(): int
    {
        return $this->userBirthday;
    }

    public function setBirthdayFromString(string $birthdayString): void
    {
        $this->userBirthday = strtotime($birthdayString);
    }

    public static function getAllUsersFromStorage(): array|false
    {
        $address = $_SERVER['DOCUMENT_ROOT'] . User::$storageAddress;

        if (file_exists($address) && is_readable($address)) {
            $file = fopen($address, "r");

            $users = [];

            while (!feof($file)) {
                $userString = trim(fgets($file));
                if (empty($userString)) {
                    continue;
                }

                $userArray = explode(",", $userString);

                if (count($userArray) < 2 || empty($userArray[0]) || empty($userArray[1])) {
                    continue;
                }

                $user = new User($userArray[0]);
                $user->setBirthdayFromString($userArray[1]);

                $users[] = $user;
            }

            fclose($file);

            return $users;
        } else {
            return false;
        }
    }

    public function saveToStorage(): bool
    {
        $address = $_SERVER['DOCUMENT_ROOT'] . self::$storageAddress;

        $userString = $this->userName . ', ' . date('d-m-Y', $this->userBirthday) . PHP_EOL;

        if (file_put_contents($address, $userString, FILE_APPEND | LOCK_EX) !== false) {
            return true;
        }

        return false;
    }
}
