<?php

namespace Geekbrains\Application1\Domain\Controllers;

use Geekbrains\Application1\Domain\Models\User;
use Geekbrains\Application1\Application\Render;

class UserController
{
    // Cохранение пользователя
    public function actionSave(): string
    {
        if (User::validateRequestData()) {
            $name = $_GET['name'] ?? null;
            $lastname = $_GET['lastname'] ?? null;
            $birthday = $_GET['birthday'] ?? null;

            $user = new User();
            $user->setParamsFromRequestData();
            $user->saveToStorage();

            $render = new Render();

            return $render->renderPage(
                'success.twig',
                [
                    'title' => 'Успешное добавление пользователя',
                    'message' => "Пользователь $name $lastname с датой рождения $birthday успешно добавлен."
                ]
            );
        } else {
            throw new \Exception("Переданные данные некорректны.");
        }
    }

    public function actionUpdate(): string
    {
        $id = $_GET['id'] ?? null;
        $name = $_GET['name'] ?? null;
        $lastname = $_GET['lastname'] ?? null;
        $birthday = $_GET['birthday'] ?? null;

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

        $render = new Render();

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
        $id = $_GET['id'] ?? null;

        if (!$id) {
            throw new \Exception("ID пользователя обязателен для удаления.");
        }

        $user = User::findById($id);

        if (!$user) {
            throw new \Exception("Пользователь с ID $id не найден.");
        }

        $user->deleteFromStorage($id);

        $render = new Render();

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
        $render = new Render();
        return $render->renderPage('adduser.twig', [
            'title' => 'Добавление пользователя'
        ]);
    }

    // Рендр страницы с формой изменения пользователя
    public function updateUserForm(): string
    {
        $render = new Render();
        return $render->renderPage('updateuser.twig', [
            'title' => 'Изменение пользователя'
        ]);
    }

    // Рендр страницы с формой удаления пользователя
    public function deleteUserForm(): string
    {
        $render = new Render();
        return $render->renderPage('deleteuser.twig', [
            'title' => 'Удаление пользователя'
        ]);
    }

    // Список пользователей
    public function actionIndex()
    {
        $users = User::getAllUsersFromStorage();
        $render = new Render();

        if (!$users) {
            return $render->renderPage(
                'user-empty.twig',
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
}
