<?php

namespace Sciarcinski\LaravelTransformer\Contracts;

interface ChartTransformerContract
{
    public function startEndDate($start, $end);
    
    public function label($object);
    
    public function getEmpty();
    
    public function chartOptions();
}
