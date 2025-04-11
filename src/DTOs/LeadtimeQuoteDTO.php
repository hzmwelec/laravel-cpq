<?php

namespace Hzmwelec\CPQ\DTOs;

use Illuminate\Contracts\Support\Arrayable;

class LeadtimeQuoteDTO implements Arrayable
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $hours;

    /**
     * @var string
     */
    public $condition;

    /**
     * @var string
     */
    public $description;

    /**
     * @param \Hzmwelec\CPQ\Models\Leadtime $leadtime
     */
    public function __construct($leadtime)
    {
        $this->id = $leadtime->id;
        $this->title = $leadtime->title;
        $this->hours = $leadtime->hours;
        $this->condition = $leadtime->condition;
        $this->description = $leadtime->description;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'hours' => $this->hours,
            'condition' => $this->condition,
            'description' => $this->description,
        ];
    }
}
