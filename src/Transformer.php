<?php

namespace Sciarcinski\LaravelTransformer;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Sciarcinski\LaravelTransformer\Contracts\TransformerContract;

abstract class Transformer implements TransformerContract
{
    /** @var array */
    protected $transform;

    protected $only = [];

    /**
     * @return array
     */
    public function get()
    {
        return $this->transform;
    }

    /**
     * @param mixed $object
     * @return array
     */
    public function toArray($object)
    {
        $this->transforms($object);

        return $this->get();
    }

    /**
     * @param mixed $object
     * @param int $code
     * @return JsonResponse
     */
    public function toJson($object, $code = 200)
    {
        $this->transforms($object);

        return new JsonResponse($this->get(), $code);
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function only(array $columns)
    {
        $this->only = $columns;
        return $this;
    }

    /**
     * @param mixed $object
     * @return $this
     */
    public function transforms($object)
    {
        if (is_null($object)) {
            $this->transform = $this->transformEmpty();
        }

        switch (true) {
            case is_array($object):
                $this->transform = $this->transformsArray($object);
                break;

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
     * @param Collection $objects
     * @return array
     */
    private function transformsCollection(Collection $objects)
    {
        return $objects->map(function ($item) {
            $this->transformClosure($item);

            return $this->transform($item);
        })->all();
    }
}
