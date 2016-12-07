<?php

namespace Sciarcinski\LaravelTransformer;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LaravelTransformer\Contracts\TransformerContract;

abstract class AbstractTransformer implements TransformerContract
{
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
     * @param $object
     * @param $code
     *
     * @return JsonResponse
     */
    public function toJson($object, $code = 200)
    {
        $this->transforms($object);
        
        return new JsonResponse($this->get(), $code);
    }

    /**
     * @param mixed $object
     *
     * @return array
     */
    public function transforms($object)
    {
        if (is_null($object)) {
            $transforms = [];
        }
        
        switch (true) {
            // array
            case is_array($object):
                $transforms = array_map(function ($object) {
                    return $this->transform($object);
                }, $object);
                break;
            
            // collection
            case ($object instanceof Collection):
                $transforms = $object->map(function ($item) {
                    return $this->transform($item);
                })->all();
                break;
            
            // model or default
            case ($object instanceof Model):
            default:
                $transforms =  $this->transform($object);
                break;
        }
        
        $this->transform = $transforms;
    }
}
