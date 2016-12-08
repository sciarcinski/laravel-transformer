<?php

namespace Sciarcinski\LaravelTransformer;

use Exception;
use Carbon\Carbon;
use Sciarcinski\LaravelTransformer\Contracts\ChartTransformerContract;

abstract class AbstractChartTransformer extends AbstractTransformer implements ChartTransformerContract
{
    protected $labels = [];
    
    protected $type = 'line';
    
    protected $responsive = true;
    
    protected $legend = false;
    
    protected $options = [];
    
    protected $empty;
    
    /** @var Carbon */
    protected $date_start;
    
    /** @var Carbon */
    protected $date_end;
    
    protected $date_format = 'Y-m-d';

    /**
     * @return array
     */
    public function get()
    {
        $this->transformsBetweenDate();
        $this->transformsChart();
        
        return $this->transform;
    }

    /**
     * @param $start
     * @param $end
     *
     * @return $this
     */
    public function startEndDate($start, $end)
    {
        $this->date_start = new Carbon($start);
        $this->date_end = new Carbon($end);
        
        return $this;
    }
    
    /**
     * Add labels chart
     *
     * @param $item
     *
     * @return mixed
     */
    protected function transformClosure($item)
    {
        $this->labels[] = $this->label($item);
    }
    
    /**
     * Transforms between date
     */
    protected function transformsBetweenDate()
    {
        $start = clone $this->date_start;
        $end = clone $this->date_end;
        $end->addDay();
        
        if ($start > $end) {
            throw new Exception('Incorrect date');
        }
        
        $labels = array_flip($this->labels);
        
        $new_labels = [];
        $new_transform = [];
        
        while ($start->diffInDays($end)) {
            $date = $start->format($this->date_format);
            $has = array_has($labels, $date);
            
            $new_labels[] = $date;
            
            $new_transform[] = $has ?
                array_get($this->transform, array_get($labels, $date), []) :
                $this->transform($this->getEmpty());
               
            $start->addDay();
        }
        
        $this->labels = $new_labels;
        $this->transform = $new_transform;
    }
    
    /**
     * Transforms chart
     */
    protected function transformsChart()
    {
        $datasets = $this->chartDatasets($this->transform);
        
        $transforms = [
            'type' => $this->type,
            'data' => [
                'labels' => $this->labels,
                'datasets' => $datasets,
            ],
            'options' => $this->getChartOptions(),
        ];
        
        $this->transform = $transforms;
    }
    
    /**
     * Allows to change options
     */
    public function chartOptions()
    {
    }
    
    /**
     * @return mixed
     */
    public function getEmpty()
    {
        return new $this->empty();
    }

    /**
     * @param array $transforms
     *
     * @return array
     */
    protected function chartDatasets(array $transforms)
    {
        $datas = [];
        
        foreach ($transforms as $datasets) {
            foreach ($datasets as $key => $dataset) {
                if (!isset($datas[$key])) {
                    $data = array_pull($dataset, 'data');
                    
                    $datas[$key] = $dataset;
                    $datas[$key]['data'][] = $data;
                } else {
                    $datas[$key]['data'][] = $dataset['data'];
                }
            }
        }
        
        return $datas;
    }
    
    /**
     * @return array
     */
    protected function getChartOptions()
    {
        $this->chartOptions();
        
        $options = [
            'responsive' => $this->responsive,
            'legend' => $this->legend,
        ];
        
        return array_merge($options, $this->options);
    }
}
