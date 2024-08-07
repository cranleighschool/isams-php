<?php

namespace spkm\isams;

#[\AllowDynamicProperties]
abstract class Wrapper
{
    /**
     * Hide the following properties from being set in $this->>setPublicProperties().
     *
     * @array
     */
    protected $hidden = [];

    protected $item;

    public function __construct($item)
    {
        $this->item = $item;
        $this->setAttributes($item);
        $this->handle();
    }

    /**
     * Handle the data.
     */
    abstract protected function handle(): void;

    /**
     * Set the contents of $this->item to public properties. Use $this->handle() to unset/override if required.
     */
    protected function setAttributes(object|array $item): void
    {
        foreach ($item as $key => $value) {
            if (in_array($key, $this->hidden) === false && property_exists($this, $key) === false) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Get the item array.
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
