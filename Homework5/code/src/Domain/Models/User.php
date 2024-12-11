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

    public function getUserBirthday(): ?int
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
        $result =  true;

        if (!(
            isset($_POST['name']) && !empty($_POST['name']) &&
            isset($_POST['lastname']) && !empty($_POST['lastname']) &&
            isset($_POST['birthday']) && !empty($_POST['birthday'])
        )) {
            $result = false;
        }

        $name = $_POST['name'];
        $lastname = $_POST['lastname'];
        $post_birthday = $_POST['birthday'];
        $dateParts = explode('-', $post_birthday);
        $birthday = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];

        if (!preg_match('/^(\d{2}-\d{2}-\d{4})$/', $birthday)) {
            throw new \Exception("Переданная дата рождения не корректна");
            $result =  false;
        }

        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] != $_POST['csrf_token']) {
            throw new \Exception("CSRF токен отсутствует в сессии или не совпадает с токеном, отправленным в запросе");
            $result = false;
        }

        // Проверка имени на наличие HTML-тегов
        if (preg_match('/<[^>]*>/', $name)) {
            throw new \Exception("Имя содержит запрещённые символы");
            $result = false;
        }

        // Проверка фамилии на наличие HTML-тегов
        if (preg_match('/<[^>]*>/', $lastname)) {
            throw new \Exception("Фамилия содержит запрещённые символы");
            $result = false;
        }

        if (strlen($name) < 2) {
            throw new \Exception("Переданное имя не корректно");
            $result =  false;
        }

        if (strlen($lastname) < 2) {
            throw new \Exception("Переданная фамилия не корректна");
            $result =  false;
        }
        return $result;
    }

    public function setParamsFromRequestData(): void
    {
        $this->userName = htmlspecialchars($_POST['name']);
        $this->userLastName = htmlspecialchars($_POST['lastname']);
        $this->setUserBirthday($_POST['birthday']);
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

    public static function destroyToken(): array
    {
        $userSql = "UPDATE users SET token = :token WHERE id_user = :id";

        $handler = Application::$storage->get()->prepare($userSql);
        $handler->execute(['token' => md5(bin2hex(random_bytes(16))), 'id' => $_SESSION['auth']['id_user']]);
        $result = $handler->fetchAll();

        return $result[0] ?? [];
    }

    public static function verifyToken(string $token): array
    {
        $userSql = "SELECT * FROM users WHERE token = :token";


        $handler = Application::$storage->get()->prepare($userSql);
        $handler->execute(['token' => $token]);
        $result = $handler->fetchAll();

        return $result[0] ?? [];
    }

    public static function setToken(int $userID, string $token): void
    {
        $userSql = "UPDATE users SET token = :token WHERE id_user = :id";


        $handler = Application::$storage->get()->prepare($userSql);
        $handler->execute(['id' => $userID, 'token' => $token]);


        setcookie(
            'auth_token',
            $token,
            time() + 60 * 60 * 24 * 30,
            '/'
        );
    }

    public static function deleteFromStorage(int $user_id): void
    {
        $sql = "DELETE FROM users WHERE id_user = :id_user";

        $handler = Application::$storage->get()->prepare($sql);
        $handler->execute(['id_user' => $user_id]);
    }
}
