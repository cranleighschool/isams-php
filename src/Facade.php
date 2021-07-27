<?php


namespace spkm\isams;


use spkm\isams\Contracts\Institution;

class Facade
{
    private const CONTROLLER_NAMESPACE = "\\spkm\\isams\\Controllers\\";
    /**
     * @var Institution
     */
    protected $institution;

    /**
     * @var Endpoint
     */
    protected $controller;

    /**
     * @param  \spkm\isams\Contracts\Institution  $institution
     */
    public function useInstitution(Institution $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * @param  string  $controller
     */
    public function useController(string $controller): self
    {
        if (class_exists(self::CONTROLLER_NAMESPACE.$controller)) {
            $this->controller = new $controller($this->institution);
            return $this;
        }
        throw new \Exception("Could not find Controller: ".self::CONTROLLER_NAMESPACE.$controller);


    }

    /**
     * @param  string  $method
     * @param  array|null  $args
     *
     * @return false|mixed
     */
    public function method(string $method, array $args=null)
    {

        return call_user_func_array([$this->controller, $method], $args);
    }

}
