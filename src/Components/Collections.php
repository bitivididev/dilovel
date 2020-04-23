<?php
namespace App\Components;

use App\interfaces\ArrayAble;
use App\interfaces\toJson;
use App\Macro\ModelMacro;
use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonException;
use JsonSerializable;
use Traversable;

/**
 * Class Collection
 * @noinspection PhpUnused
 */
class Collections implements ArrayAccess, IteratorAggregate, JsonSerializable, Countable,ArrayAble,toJson
{
    /**
     * @var array $collection
     */
    private array  $collection;

    public function __construct(array $collection)
    {
        $this->collection=$collection;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->collection[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->collection[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        if (empty($offset)) {
            return $this->collection[] = $value;
        }

        return $this->collection[$offset] = $value;

    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->collection[$offset]);
    }



    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return serialize($this->collection);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return $this->collection;
    }


    /**
     * @return mixed
     */
    public function first()
    {
        return $this->collection[0];
    }

    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->collection);
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __toString()
    {
        return json_encode($this->collection, JSON_THROW_ON_ERROR, 512);
    }

    /**
     * @return mixed
     */
    public function last()
    {
        return $this->collection[$this->count()-1];
    }


    /**
     * @return array
     */
    public function toArray():array
    {
        return array_map('get_object_vars',$this->collection);
    }
    /**
     * @return false|string
     * @throws JsonException
     */
    public function toJson():string
    {
        return json_encode($this->collection, JSON_THROW_ON_ERROR, 512);
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
       $this->collection= ModelMacro::getMethod($name)->call($this,$arguments);
       return $this;
    }

}