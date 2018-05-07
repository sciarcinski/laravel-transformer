<?php

namespace Sciarcinski\LaravelTransformer;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Sciarcinski\LaravelTransformer\Contracts\TransformerContract;

abstract class AbstractTransformer implements TransformerContract
{
    /** @var array */
    protected $transform;
    
    /** @var Request */
    protected $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

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
     * @param mixed $object
     * @return array
     */
    public function transforms($object)
    {
        if (is_null($object)) {
            $transforms = $this->transformEmpty();
        }
        
        switch (true) {
            case is_array($object):
                $transforms = $this->transformsArray($object);
                break;
            
            case ($object instanceof Collection):
                $transforms = $this->transformsCollection($object);
                break;
            
            case ($object instanceof Model):
                $transforms = $this->transform($object);
                break;
        }
        
        $this->transform = $transforms;

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
