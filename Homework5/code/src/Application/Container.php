<?php

namespace Geekbrains\Application1\Application;

class Container {
    private $services = [];

    // Метод для регистрации сервиса в контейнере
    public function set($name, $service) {
        $this->services[$name] = $service;
    }

    // Метод для получения сервиса из контейнера
    public function get($name) {
        if (!isset($this->services[$name])) {
            throw new \Exception("Service not found: $name");
        }
        return $this->services[$name];
    }
}
