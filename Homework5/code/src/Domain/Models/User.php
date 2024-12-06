<?php

namespace Geekbrains\Application1\Domain\Models;

use Geekbrains\Application1\Application\Application;

class User
{
    private ?int $idUser;
    private ?string $userName;
    private ?string $userLastName;
    private ?int $userBirthday;

    public function __construct(string $name = null, string $lastName = null, int $birthday = null, int $id_user = null)
    {
        $this->userName = $name;
        $this->userLastName = $lastName;
        $this->userBirthday = $birthday;
        $this->idUser = $id_user;
    }

    public function getUserId(): ?int
    {
        return $this->idUser;
    }

    public function setUserId(int $id_user): void
    {
        $this->idUser = $id_user;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    public function getUserLastName(): string
    {
        return $this->userLastName;
    }

    public function setUserLastName(string $userLastName): void
    {
        $this->userLastName = $userLastName;
    }

    public function getUserBirthday(): int
    {
        return $this->userBirthday;
    }

    public function setUserBirthday(string $birthdayString): void
    {
        $timestamp = strtotime($birthdayString);
        if ($timestamp === false) {
            throw new \Exception("Неверный формат даты");
        }
        $this->userBirthday = $timestamp;
    }

    public static function findById(int $id): ?User
    {
        $sql = "SELECT * FROM users WHERE id_user = :id_user";
        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute(['id_user' => $id]);

        $result = $handler->fetch();

        if ($result) {
            return new self($result['user_name'], $result['user_lastname'], $result['user_birthday_timestamp'], $result['id_user']);
        }

        return null;
    }

    public static function getAllUsersFromStorage(): array
    {
        $sql = "SELECT * FROM users";
        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute();
        $result = $handler->fetchAll();

        $users = [];
        foreach ($result as $item) {
            $users[] = new self($item['user_name'], $item['user_lastname'], $item['user_birthday_timestamp'], $item['id_user']);
        }

        return $users;
    }

    public static function validateRequestData(): bool
    {
        if (
            isset($_GET['name']) && !empty($_GET['name']) &&
            isset($_GET['lastname']) && !empty($_GET['lastname']) &&
            isset($_GET['birthday']) && !empty($_GET['birthday'])
        ) {

            $name = $_GET['name'];
            $lastname = $_GET['lastname'];

            if (strlen($name) < 2) {
                throw new \Exception("Переданное имя не корректно");
                return false;
            }

            if (strlen($lastname) < 2) {
                throw new \Exception("Переданная фамилия не корректна");
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    public function setParamsFromRequestData(): void
    {
        $this->userName = $_GET['name'];
        $this->userLastName = $_GET['lastname'];
        $this->setUserBirthday($_GET['birthday']);
    }

    public function saveToStorage(): void
    {
        $sql = "INSERT INTO users (user_name, user_lastname, user_birthday_timestamp) VALUES (:user_name, :user_lastname, :user_birthday)";

        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute([
            'user_name' => $this->userName,
            'user_lastname' => $this->userLastName,
            'user_birthday' => $this->userBirthday
        ]);
    }

    public function updateUser(array $userDataArray): void
    {
        if (empty($userDataArray)) {
            return;
        }

        $sql = "UPDATE users SET ";
        $counter = 0;

        foreach ($userDataArray as $key => $value) {
            if ($value !== null && $value !== '') {
                $sql .= $key . " = :" . $key;

                if ($counter != count($userDataArray) - 1) {
                    $sql .= ",";
                }

                $counter++;
            }
        }

        $sql .= " WHERE id_user = :id_user";

        $userDataArray['id_user'] = $this->idUser;

        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute($userDataArray);
    }


    public static function exists(int $id): bool
    {
        $sql = "SELECT COUNT(id_user) as user_count FROM users WHERE id_user = :id_user";
        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute(['id_user' => $id]);

        $result = $handler->fetch();

        return $result['user_count'] > 0;
    }

    public static function deleteFromStorage(int $user_id): void
    {
        $sql = "DELETE FROM users WHERE id_user = :id_user";

        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute(['id_user' => $user_id]);
    }
}
