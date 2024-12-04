<?php

namespace Geekbrains\Application1\Controllers;

use Geekbrains\Application1\Models\User;
use Geekbrains\Application1\Render;

class UserController
{
    // Cохранение пользователя
    public function actionSave(): string
    {
        $name = $_GET['name'] ?? null;
        $birthday = $_GET['birthday'] ?? null;

        $render = new Render();

        if (!$this->validateInputs($name, $birthday)) {
            return $render->renderPage(
                'error.twig',
                [
                    'message' => "Ошибка: Неверные или отсутствующие параметры 'name' или 'birthday'. Убедитесь, что дата указана в формате DD-MM-YYYY."
                ]
            );
        }

        $user = new User();
        $user->setName($name);
        $user->setBirthdayFromString($birthday);

        if ($user->saveToStorage()) {
            return $render->renderPage(
                'success.twig',
                [
                    'title' => 'Успешное добавление пользователя',
                    'name' => $name,
                    'birthday' => $birthday
                ]
            );
        } else {
            return $render->renderPage(
                'error.twig',
                [
                    'message' => "Ошибка: Не удалось сохранить данные."
                ]
            );
        }
    }

    // Валидация параметров
    private function validateInputs(?string $name, ?string $birthday): bool
    {
        if (empty($name) || strlen($name) < 2) {
            return false;
        }

        // Проверка формата даты (YYYY-MM-DD)
        $datePattern = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($datePattern, $birthday)) {
            return false;
        }

        // Преобразуем строку даты в DateTime объект
        $date = \DateTime::createFromFormat('Y-m-d', $birthday);
        if (!$date || $date->format('Y-m-d') !== $birthday) {
            return false;
        }

        return true;
    }


    // Страница с формой добавления пользователя
    public function actionShowForm(): string
    {
        $render = new Render();
        return $render->renderPage('adduser.twig', [
            'title' => 'Добавление пользователя'
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
                    'title' => 'Список пользователей в хранилище',
                    'message' => "Список пуст или не найден"
                ]
            );
        } else {
            return $render->renderPage(
                'user-index.twig',
                [
                    'title' => 'Список пользователей в хранилище',
                    'users' => $users
                ]
            );
        }
    }
}
