<?php

namespace Hzmwelec\CPQ\DTOs;

use Illuminate\Contracts\Support\Arrayable;

class CostQuoteDTO implements Arrayable
{
    /**
     * @var float
     */
    public $price;

    /**
     * @var int
     */
    public $costId;

    /**
     * @var string
     */
    public $costTitle;

    /**
     * @var string
     */
    public $costCode;

    /**
     * @var int
     */
    public $ruleId;

    /**
     * @var string
     */
    public $ruleAction;

    /**
     * @var string
     */
    public $ruleCondition;

    /**
     * @var string
     */
    public $ruleDescription;

    /**
     * @param float $price
     * @param \Hzmwelec\CPQ\Models\Cost $cost
     * @param \Hzmwelec\CPQ\Models\CostRule $rule
     */
    public function __construct($price, $cost, $rule)
    {
        $this->price = (float) $price;
        $this->costId = $cost->id;
        $this->costTitle = $cost->title;
        $this->costCode = $cost->code;
        $this->ruleId = $rule->id;
        $this->ruleAction = $rule->action;
        $this->ruleCondition = $rule->condition;
        $this->ruleDescription = $rule->description;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'price' => $this->price,
            'cost_id' => $this->costId,
            'cost_title' => $this->costTitle,
            'cost_code' => $this->costCode,
            'rule_id' => $this->ruleId,
            'rule_action' => $this->ruleAction,
            'rule_condition' => $this->ruleCondition,
            'rule_description' => $this->ruleDescription,
        ];
    }
}
