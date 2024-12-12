<?php

namespace Geekbrains\Application1\Domain\Controllers;

use Geekbrains\Application1\Application\Application;
use Geekbrains\Application1\Application\Render;
use Geekbrains\Application1\Application\Auth;
use Geekbrains\Application1\Domain\Models\User;
use Geekbrains\Application1\Domain\Controllers\AbstractController;
use Geekbrains\Application1\Application\Container;

class UserController extends AbstractController
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected array $actionsPermissions = [
        'actionIndex' => ['admin', 'user'],
        'actionIndexRefresh' => ['admin', 'user'],
        'addUserForm' => ['admin'],
        'updateUserForm' => ['admin'],
        'deleteUserForm' => ['admin'],
        'actionSave' => ['admin'],
        'actionUpdate' => ['admin'],
        'actionDelete' => ['admin'],
        'actionHash' => ['admin'],
        'actionAuth' => ['admin', 'user'],
        'actionLogin' => ['admin', 'user'],
        'actionLogout' => ['admin', 'user'],
    ];

    public function actionSave(): string
    {
        if (User::validateRequestData()) {
            $name = $_POST['name'] ?? null;
            $lastname = $_POST['lastname'] ?? null;
            $birthday = $_POST['birthday'] ?? null;

            $user = new User();
            $user->setParamsFromRequestData();
            $user->saveToStorage();

            $render = $this->container->get(Render::class);

            return $render->renderPage(
                'success.twig',
                [
                    'title' => 'Успешное добавление пользователя',
                    'message' => "Пользователь $name $lastname с датой рождения $birthday успешно добавлен."
                ]
            );
        } else {
            $logMessage = "Пользователь " . $_SESSION['auth']['user_name'] . " ввёл некорректные данные.";
            Application::$logger->error($logMessage);
            throw new \Exception("Переданные данные некорректны.");
        }
    }

    public function actionUpdate(): string
    {
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? null;
        $lastname = $_POST['lastname'] ?? null;
        $birthday = $_POST['birthday'] ?? null;

        if (!$id) {
            throw new \Exception("ID пользователя обязателен для обновления.");
        }

        $user = User::findById($id);

        if (!$user) {
            throw new \Exception("Пользователь с ID $id не найден.");
        }

        $userDataArray = [];

        if ($name !== null && $name !== '') {
            $userDataArray['user_name'] = $name;
        }

        if ($lastname !== null && $lastname !== '') {
            $userDataArray['user_lastname'] = $lastname;
        }

        if ($birthday !== null && $birthday !== '') {
            $userDataArray['user_birthday_timestamp'] = strtotime($birthday);
        }

        if (!empty($userDataArray)) {
            $user->updateUser($userDataArray);
        }

        $render = $this->container->get(Render::class);

        return $render->renderPage(
            'success.twig',
            [
                'title' => 'Обновление пользователя',
                'message' => "Пользователь с ID $id успешно обновлён."
            ]
        );
    }

    // Удаление пользователя
    public function actionDelete(): string
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            throw new \Exception("ID пользователя обязателен для удаления.");
        }

        $user = User::findById($id);

        if (!$user) {
            throw new \Exception("Пользователь с ID $id не найден.");
        }

        $user->deleteFromStorage($id);

        $render = $this->container->get(Render::class);

        return $render->renderPage(
            'success.twig',
            [
                'title' => 'Удаление пользователя',
                'message' => "Пользователь с ID $id успешно удалён."
            ]
        );
    }

    // Рендр страницы с формой добавления пользователя
    public function addUserForm(): string
    {
        $render = $this->container->get(Render::class);
        return $render->renderPageWithForm('form-add-user.twig', [
            'title' => 'Добавление пользователя'
        ]);
    }

    // Рендр страницы с формой изменения пользователя
    public function updateUserForm(): string
    {
        $render = $this->container->get(Render::class);
        return $render->renderPageWithForm('form-update-user.twig', [
            'title' => 'Изменение пользователя'
        ]);
    }

    // Рендр страницы с формой удаления пользователя
    public function deleteUserForm(): string
    {
        $render = $this->container->get(Render::class);
        return $render->renderPageWithForm('form-delete-user.twig', [
            'title' => 'Удаление пользователя'
        ]);
    }

    // Список пользователей
    public function actionIndex()
    {
        $users = User::getAllUsersFromStorage();
        $render = $this->container->get(Render::class);

        if (!$users) {
            return $render->renderPage(
                'message.twig',
                [
                    'title' => 'Список пользователей',
                    'message' => "Список пуст"
                ]
            );
        } else {
            return $render->renderPage(
                'user-index.twig',
                [
                    'title' => 'Список пользователей',
                    'users' => $users
                ]
            );
        }
    }

    public function actionAuth(): string
    {
        $render = $this->container->get(Render::class);

        return $render->renderPageWithForm(
            'form-auth.twig',
            [
                'title' => 'Форма логина'
            ]
        );
    }

    public function actionHash(): string
    {
        if (isset($_GET['pass_string']) && !empty($_GET['pass_string'])) {
            return Auth::getPasswordHash($_GET['pass_string']);
        } else {
            throw new \Exception("Невозможно сгенерировать хэш. Не передан пароль.");
        }
    }

    public function actionLogin(): string
    {
        $result = false;

        if (isset($_POST['login']) && isset($_POST['password'])) {
            $result = Application::$auth->proceedAuth($_POST['login'], $_POST['password']);
        }

        if (!$result) {
            $render = $this->container->get(Render::class);

            return $render->renderPageWithForm(
                'form-auth.twig',
                [
                    'title' => 'Форма логина',
                    'error' => true,
                    'error_decription' => 'Неверные логин или пароль!',
                ]
            );
        } else {
            // Запомнить меня
            if (!empty($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_me', $token, time() + (3600 * 24 * 30), '/', '', false, true);

                $sql = "UPDATE users SET remember_token = :token WHERE id_user = :id_user";
                $handler = Application::$storage->get()->prepare($sql);
                $handler->execute(['token' => $token, 'id_user' => $_SESSION['auth']['id_user']]);
            }

            header('Location: /');
            return "";
        }
    }

    public function actionLogout(): void
    {
        if (isset($_COOKIE['remember_me'])) {
            $sql = "UPDATE users SET remember_token = NULL WHERE id_user = :id_user";
            $handler = Application::$storage->get()->prepare($sql);
            $handler->execute(['id_user' => $_SESSION['auth']['id_user']]);
            setcookie('remember_me', '', time() - 3600, '/');
        }

        $_SESSION = [];
        session_unset();
        session_destroy();

        header('Location: /user/auth/');
        exit;
    }

    public function actionIndexRefresh()
    {
        $limit = null;

        if (isset($_POST['maxId']) && ($_POST['maxId'] > 0)) {
            $limit = $_POST['maxId'];
        }
        
        $users = User::getAllUsersFromStorage($limit);
        $usersData = [];

        if (count($users) > 0) {
            foreach ($users as $user) {
                $usersData[] = $user->getUserDataAsArray();
            }
        }

        return json_encode($usersData);
    }
}
