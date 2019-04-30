<?php

namespace Sciarcinski\LaravelTransformer\Contracts;

use Sciarcinski\LaravelTransformer\Transformer;

interface TransformerContract
{
    /**
     * @param mixed $items
     * @return Transformer
     */
    public function set($items);

    /**
     * @return array
     */
    public function toArray();

    /**
     * @return string
     */
    public function toJson();

    /**
     * @param mixed $object
     * @return array
     */
    public function transform($object);
}
