<?php

namespace Sciarcinski\LaravelTransformer\Contracts;

interface TransformerContract
{
    public function get();
    
    public function toJson($object, $code = 200);
    
    public function transforms($object);
    
    public function transform($object, $empty = false);
}
