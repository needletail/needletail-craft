<?php

namespace needletail\needletail\services;

use Closure;
use craft\base\Component;

class Hash extends Component
{
    public function get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (!is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if (!is_array($target)) {
                    return $this->value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = $this->get($item, $key);
                }

                return in_array('*', $key) ? $this->collapse($result) : $result;
            }

            if ($this->accessible($target) && $this->exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return $this->value($default);
            }
        }
        return $target;
    }

    public function set(&$array, $key, $value = null)
    {
        if ( is_array($key) ) {
            foreach ($key as $k => $v) {
                $this->set($array, $k, $v);
            }
            return $array;
        }

        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     * @return bool
     */
    private function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    private function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    private function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }
        return array_key_exists($key, $array);
    }

    private function collapse($array)
    {
        $results = [];

        foreach ($array as $values) {
            if (!is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        return array_merge([], ...$results);
    }
}