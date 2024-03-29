<?php

namespace Sciarcinski\LaravelTransformer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Sciarcinski\LaravelTransformer\Contracts\TransformerContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class Transformer implements TransformerContract
{
    /** @var array */
    protected $transform = [];

    /**
     * @param mixed $items
     */
    public function __construct($items = null)
    {
        if (!is_null($items)) {
            $this->set($items);
        }
    }

    /**
     * @param mixed $items
     * @return $this
     */
    public function set($items)
    {
        $this->transforms($items);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->transform;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->transform);
    }

    /**
     * @param string $transform
     * @param mixed $object
     * @return array|TransformerContract
     */
    protected function transformWith($transform, $object)
    {
        $transform = new $transform($object);

        return $transform instanceof TransformerContract ? $transform->toArray() : [];
    }

    /**
     * @param mixed $object
     * @return $this
     */
    protected function transforms($object)
    {
        if (is_null($object)) {
            $this->transform = $this->transformEmpty();
        }

        switch (true) {
            case is_array($object):
                $this->transform = $this->transformsArray($object);
                break;

            case ($object instanceof LengthAwarePaginator):
            case ($object instanceof Collection):
                $this->transform = $this->transformsCollection($object);
                break;

            case ($object instanceof Model):
                $this->transform = $this->transform($object);
                break;
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function transformEmpty()
    {
        return [];
    }

    /**
     * @param $item
     * @return mixed
     */
    protected function transformClosure($item)
    {
        return $item;
    }

    /**
     * @param array $objects
     * @return array
     */
    private function transformsArray(array $objects)
    {
        return array_map(function ($object) {
            $this->transformClosure($object);

            return $this->transform($object);
        }, $objects);
    }

    /**
     * @param Collection|LengthAwarePaginator $objects
     * @return array
     */
    private function transformsCollection($objects)
    {
        $objects->transform(function ($item) {
            $this->transformClosure($item);

            return $this->transform($item);
        })->toArray();

        return $objects->toArray();
    }
}
