<?php

namespace Geekbrains\Application1\Application;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Geekbrains\Application1\Domain\Controllers\AbstractController;
use Geekbrains\Application1\Infrastructure\Config;
use Geekbrains\Application1\Infrastructure\Storage;
use Geekbrains\Application1\Application\Auth;


class Application
{

    private const APP_NAMESPACE = 'Geekbrains\Application1\Domain\Controllers\\';

    private string $controllerName;
    private string $methodName;

    public static Config $config;

    public static Storage $storage;

    public static Auth $auth;

    public function __construct()
    {
        Application::$config = new Config();
        Application::$storage = new Storage();
        Application::$auth = new Auth();
    }


    public function run(): string
    {
        session_start();

        $routeArray = explode('/', ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
        $controllerName = isset($routeArray[0]) && $routeArray[0] !== '' ? $routeArray[0] : 'page';

        // Определяем контроллер и метод по умолчанию
        $this->controllerName = Application::APP_NAMESPACE . ucfirst($controllerName) . 'Controller';
        $this->methodName = isset($routeArray[2]) && $routeArray[2] !== '' ? 'action' . ucfirst($routeArray[2]) : 'actionIndex';

        if ($controllerName === 'user') {
            $this->controllerName = Application::APP_NAMESPACE . 'UserController';
            $actionMap = [
                'POST' => [
                    'save' => 'actionSave',
                    'update' => 'actionUpdate',
                    'delete' => 'actionDelete',
                    'login' => 'actionLogin',
                ],
                'GET' => [
                    'hash' => 'actionHash',
                    'auth' => 'actionAuth',
                    'logout' => 'actionLogout',
                ],
            ];
            $method = $_SERVER['REQUEST_METHOD'];
            $route = $routeArray[1] ?? null;
            $this->methodName = $actionMap[$method][$route] ?? $this->methodName;
        } elseif (in_array($controllerName, ['adduser', 'updateuser', 'deleteuser'])) {
            $this->controllerName = Application::APP_NAMESPACE . 'UserController';
            $methodMap = [
                'adduser' => 'addUserForm',
                'updateuser' => 'updateUserForm',
                'deleteuser' => 'deleteUserForm',
            ];
            $this->methodName = $methodMap[$controllerName];
        }

        // Проверяем существование контроллера
        if (class_exists($this->controllerName)) {
            // Проверяем существование метода
            if (method_exists($this->controllerName, $this->methodName)) {
                $controllerInstance = new $this->controllerName();
                if ($controllerInstance instanceof AbstractController) {
                    if ($this->checkAccessToMethod($controllerInstance, $this->methodName)) {
                        return call_user_func_array(
                            [$controllerInstance, $this->methodName],
                            []
                        );
                    } else {
                        throw new \Exception("Нет доступа к методу");
                    }
                } else {
                    return call_user_func_array(
                        [$controllerInstance, $this->methodName],
                        []
                    );
                }
            } else {
                throw new \Exception("Метод не существует");
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

    private function checkAccessToMethod(AbstractController $controllerInstance, string $methodName): bool
    {
        $isAllowed = false;

        if ($methodName === 'actionAuth') {
            $isAllowed = true;
            return $isAllowed;
        }

        $userRoles = $controllerInstance->getUserRoles();
        
        $rules = $controllerInstance->getActionsPermissions($methodName);

        if (!empty($rules)) {
            foreach ($rules as $rolePermission) {
                if (in_array($rolePermission, $userRoles)) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        return $isAllowed;
    }
}
