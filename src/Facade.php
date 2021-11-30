<?php

namespace spkm\isams;

use Illuminate\Support\Str;
use spkm\isams\Contracts\Institution;
use spkm\isams\Exceptions\ControllerNotFound;
use spkm\isams\Exceptions\MethodNotFound;

class Facade
{
    private const CONTROLLER_NAMESPACE = 'spkm\\isams\\Controllers\\';
    /**
     * @var Institution
     */
    protected $institution;

    /**
     * @var Endpoint
     */
    protected $controller;

    /**
     * @param Institution $institution
     * @return $this
     */
    public function institution(Institution $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Because 'Institution' is such an awkwardly
     * long word to write when you're in a hurry!
     *
     * @param Institution $institution
     * @return $this
     */
    public function school(Institution $institution): self
    {
        return $this->institution($institution);
    }

    /**
     * @param  string  $controller
     * @return $this
     * @throws ControllerNotFound
     */
    public function endpoint(string $controller): self
    {
        $controllerClass = $this->getController($controller);
        $this->controller = new $controllerClass($this->institution);

        return $this;
    }

    /**
     * Sanitizes the controller name for us, so people can use ::class notation if they wish.
     *
     * @param  string  $controllerClassName
     * @return string
     *
     * @throws ControllerNotFound
     */
    private function getController(string $controllerClassName): string
    {
        if (Str::contains($controllerClassName, self::CONTROLLER_NAMESPACE) && class_exists($controllerClassName)) {
            return $controllerClassName;
        }

        if (class_exists(self::CONTROLLER_NAMESPACE . $controllerClassName)) {
            return self::CONTROLLER_NAMESPACE . $controllerClassName;
        }

        throw new ControllerNotFound('Could not find Controller: ' . $controllerClassName,
            500);
    }

    /**
     * @param  string  $method
     * @param  array  $args
     * @return false|mixed
     * @throws MethodNotFound
     */
    public function callMethod(string $method, array $args = [])
    {
        if (! method_exists($this->controller, $method)) {
            throw new MethodNotFound('Method ' . $method . ' not found on ' . get_class($this->controller));
        }

        return call_user_func_array([$this->controller, $method], $args);
    }
}
