<?php
/**
 * KK-Framework
 * Author: kookxiang <r18@ikk.me>
 */

namespace Core;

class LazyLoadObject
{
    private $value;
    private $object = null;
    private $callback = null;
    private $hasValue = false;

    public function __construct($value, callable $callback)
    {
        $this->value = $value;
        $this->callback = $callback;
    }

    public function __debugInfo()
    {
        if (!$this->hasValue) {
            return array(
                'loaded' => false,
                'value' => $this->value
            );
        } else {
            return array(
                'loaded' => true,
                'value' => $this->object
            );
        }
    }

    public function __invoke()
    {
        if (!$this->hasValue) {
            $this->object = ($this->callback)($this->value);
            $this->hasValue = true;
        }
        return $this->object;
    }

    public function bindValue(&$newValue)
    {
        $this->value = $newValue;
        $this->object = null;
        $this->hasValue = false;
    }

    public function updateValue($newValue)
    {
        $this->value = $newValue;
        $this->object = null;
        $this->hasValue = false;
    }

    public function getValue()
    {
        return $this->value;
    }
}
