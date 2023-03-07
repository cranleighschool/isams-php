<?php

namespace spkm\isams;

abstract class Wrapper
{
    /**
     * Hide the following properties from being set in $this->>setPublicProperties().
     *
     * @array
     */
    protected $hidden = [];

    /**
     * @var mixed
     */
    protected $item;
    protected array $attributes = [];

    public function __construct($item)
    {
        $this->item = $item;
        $this->setAttributes();
        $this->handle();
    }

    public function __set(string $name, mixed $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __get(string $name)
    {
        return $this->attributes[$name];
    }

    /**
     * Handle the data.
     *
     * @return void
     */
    abstract protected function handle(): void;

    /**
     * Set the contents of $this->item to public properties. Use $this->handle() to unset/override if required.
     *
     * @return void
     */
    protected function setAttributes(): void
    {
        if (is_array($this->item) || is_object($this->item)) {
            foreach ($this->item as $key => $value) {
                if (in_array($key, $this->hidden) === false && property_exists($this, $key) === false) {
                    $this->{$key} = $value;
                }
            }
        }
    }

    /**
     * Get the item array.
     *
     * @return array
     */
    public function toArray(): array
    {
        if (is_object($this->item)) {
            return get_object_vars($this->item);
        }

        if ($this->item === null) {
            return [];
        }

        return $this->item;
    }
}
