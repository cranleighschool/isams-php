<?php

namespace spkm\isams;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use spkm\isams\Contracts\Institution;
use spkm\isams\Exceptions\ControllerNotFound;
use spkm\isams\Exceptions\IsamsInstanceNotFound;
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
     * @return $this
     */
    public function school(Institution $institution): self
    {
        return $this->institution($institution);
    }

    /**
     * @return $this
     *
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
     *
     * @throws ControllerNotFound
     */
    private function getController(string $controllerClassName): string
    {
        if (Str::contains($controllerClassName, self::CONTROLLER_NAMESPACE) && class_exists($controllerClassName)) {
            return $controllerClassName;
        }

        if (class_exists(self::CONTROLLER_NAMESPACE.$controllerClassName)) {
            return self::CONTROLLER_NAMESPACE.$controllerClassName;
        }

        throw new ControllerNotFound('Could not find Controller: '.$controllerClassName,
            500);
    }

    /**
     * @return false|mixed
     *
     * @throws MethodNotFound
     * @throws IsamsInstanceNotFound
     */
    public function callMethod(string $method, array $args = [])
    {
        if (! method_exists($this->controller, $method)) {
            throw new MethodNotFound('Method '.$method.' not found on '.get_class($this->controller));
        }
        try {
            return call_user_func_array([$this->controller, $method], $args);
        } catch (ClientException $exception) {
            if ($exception->getCode() === 404) {
                throw new IsamsInstanceNotFound(
                    'ISAMS returned a 404 Not Found.',
                    404,
                    $exception
                );
            }
            throw $exception;
        }
    }
}
