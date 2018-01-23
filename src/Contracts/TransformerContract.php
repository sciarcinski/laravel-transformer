<?php

namespace Sciarcinski\LaravelTransformer\Contracts;

interface TransformerContract
{
    /**
     * @return array
     */
    public function get();

    /**
     * @param mixed $object
     * @return array
     */
    public function toArray($object);

    /**
     * @param mixed $object
     * @param int $code
     * @return JsonResponse
     */
    public function toJson($object, $code = 200);

    /**
     * @param mixed $object
     * @return array
     */
    public function transforms($object);

    /**
     * @param mixed $object
     * @return array
     */
    public function transform($object);
}
