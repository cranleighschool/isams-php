<?php

namespace spkm\isams;

use spkm\isams\Contracts\Institution;

abstract class Wrapper
{
    /**
     * Hide the following properties from being set in $this->>setPublicProperties()
     *
     * @array
     */
    protected $hidden = [];

    /**
     * @var array
     */
    protected $item;

    public function __construct($item, Institution $school)
    {
        $this->item = $item;
        $this->setPublicProperties();
        $this->handle();
    }

    /**
     * Handle the data.
     *
     * @return void
     */
    abstract protected function handle();

    /**
     * Set the contents of $this->item to public properties. Use $this->handle() to unset/override if required
     *
     * @return void
     */
    protected function setPublicProperties()
    {
        foreach ($this->item as $key => $value):
            if (in_array($key, $this->hidden) === false && property_exists($this, $key) === false):
                $this->{$key} = $value;
            endif;
        endforeach;
    }

    /**
     * Get the item array
     *
     * @return array
     */
    public function toArray() : array
    {
        return $this->item;
    }
}