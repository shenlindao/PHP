<?php

namespace Geekbrains\Application1;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Application
{

    private const APP_NAMESPACE = 'Geekbrains\Application1\Controllers\\';

    private string $controllerName;
    private string $methodName;

    public function run(): string
    {
        // Разбиваем URI на части, игнорируя строку запроса
        $routeArray = explode('/', ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

        // Получаем строку запроса (если она есть)
        $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

        // Проверяем, если строка запроса существует, разбиваем её в массив
        $queryParams = [];
        if ($queryString) {
            parse_str($queryString, $queryParams);
        }

        // Если путь пустой, то считаем, что мы на главной странице
        $controllerName = isset($routeArray[0]) && $routeArray[0] !== '' ? $routeArray[0] : 'page';

        // Определяем имя контроллера (с добавлением имени пространства приложения)
        $this->controllerName = Application::APP_NAMESPACE . ucfirst($controllerName) . 'Controller';

        // Специальная обработка для добавления пользователя
        if ($controllerName === 'user' && isset($routeArray[1]) && $routeArray[1] === 'save' && isset($queryParams['name']) && isset($queryParams['birthday'])) {
            // Устанавливаем контроллер и метод для сохранения
            $this->controllerName = Application::APP_NAMESPACE . 'UserController';
            $this->methodName = 'actionSave';
        } elseif ($controllerName === 'adduser') {
            $this->controllerName = Application::APP_NAMESPACE . 'UserController';
            $this->methodName = 'actionShowForm';
        } else {
            // Формирование имя метода для других маршрутов
            $this->methodName = isset($routeArray[2]) && $routeArray[2] !== '' ? 'action' . ucfirst($routeArray[2]) : 'actionIndex';
        }

        // Проверяем существование контроллера
        if (class_exists($this->controllerName)) {

            // Проверяем существование метода в контроллере
            if (method_exists($this->controllerName, $this->methodName)) {
                $controllerInstance = new $this->controllerName();

                // Вызываем метод контроллера
                return call_user_func_array([$controllerInstance, $this->methodName], []);
            } else {
                return $this->renderError("Метод не существует", 404);
            }
        } else {
            return $this->renderError("Контроллер не существует", 404);
        }
    }


    // Метод для рендеринга шаблона ошибки
    private function renderError(string $errorMessage, int $statusCode): string
    {
        http_response_code($statusCode);

        // Загружаем Twig
        $loader = new FilesystemLoader(__DIR__ . '/views');
        $twig = new Environment($loader);

        // Рендерим страницу ошибки
        try {
            return $twig->render("{$statusCode}.twig", ['errorMessage' => $errorMessage]);
        } catch (\Twig\Error\LoaderError $e) {
            return "Ошибка при загрузке шаблона: " . $e->getMessage();
        }
    }
}
