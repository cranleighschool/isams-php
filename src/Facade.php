<?php


namespace spkm\isams;


use spkm\isams\Contracts\Institution;

class Facade
{
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
     * @param  \spkm\isams\Endpoint  $controller
     */
    public function useController(Endpoint $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @param  string  $method
     * @param  array|null  $args
     *
     * @return false|mixed
     */
    public function method(string $method, array $args=null)
    {
        $controller = new $this->controller($this->institution);

        return call_user_func_array([$controller, $method], $args);
    }

}
