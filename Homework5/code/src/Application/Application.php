<?php

namespace Geekbrains\Application1\Application;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Geekbrains\Application1\Infrastructure\Config;
use Geekbrains\Application1\Infrastructure\Storage;
use Exception;


class Application
{

    private const APP_NAMESPACE = 'Geekbrains\Application1\Domain\Controllers\\';

    private string $controllerName;
    private string $methodName;

    public static Config $config;
    public static Storage $storage;

    public function __construct()
    {
        Application::$config = new Config();
        Application::$storage = new Storage();
    }


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


        if ($controllerName === 'user') {
            // Проверка на сохранение пользователя
            if (isset($routeArray[1]) && $routeArray[1] === 'save' && isset($queryParams['name']) && isset($queryParams['lastname']) && isset($queryParams['birthday'])) {
                $this->controllerName = Application::APP_NAMESPACE . 'UserController';
                $this->methodName = 'actionSave';
                // Проверка на обновление пользователя
            } elseif (isset($routeArray[1]) && $routeArray[1] === 'update' && isset($queryParams['id'])) {
                $this->controllerName = Application::APP_NAMESPACE . 'UserController';
                $this->methodName = 'actionUpdate';
                // Проверка на удаление пользователя
            } elseif (isset($routeArray[1]) && $routeArray[1] === 'delete' && isset($queryParams['id'])) {
                $this->controllerName = Application::APP_NAMESPACE . 'UserController';
                $this->methodName = 'actionDelete';
            } else {
                $this->methodName = isset($routeArray[2]) && $routeArray[2] !== '' ? 'action' . ucfirst($routeArray[2]) : 'actionIndex';
            }
        } elseif ($controllerName === 'adduser') {
            $this->controllerName = Application::APP_NAMESPACE . 'UserController';
            $this->methodName = 'addUserForm';
        } elseif ($controllerName === 'updateuser') {
            $this->controllerName = Application::APP_NAMESPACE . 'UserController';
            $this->methodName = 'updateUserForm';
        } elseif ($controllerName === 'deleteuser') {
            $this->controllerName = Application::APP_NAMESPACE . 'UserController';
            $this->methodName = 'deleteUserForm';
        } else {
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
                return $this->renderError(404);
            }
        } else {
            return $this->renderError(404);
        }
    }

    // Рендер шаблона ошибки
    private function renderError(int $statusCode): string
    {
        http_response_code($statusCode);

        $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/src/Domain/Views');
        $twig = new Environment($loader);

        $contentTemplateName = "{$statusCode}.twig";

        $templateVariables = [
            'content_template_name' => $contentTemplateName,
            'message' => "Произошла ошибка с кодом: $statusCode",
        ];

        try {
            $template = $twig->load('layouts/main.twig');
            return $template->render($templateVariables);
        } catch (\Twig\Error\LoaderError $e) {
            return "Ошибка при загрузке шаблона: " . $e->getMessage();
        }
    }
}