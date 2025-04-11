<?php

namespace Hzmwelec\CPQ\Services;

use Hzmwelec\CPQ\DTOs\CostQuoteDTO;
use Hzmwelec\CPQ\DTOs\LeadtimeQuoteDTO;
use Hzmwelec\CPQ\DTOs\ProductQuoteDTO;
use Illuminate\Support\Collection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class QuoteService
{
    /**
     * @param \Hzmwelec\CPQ\Models\Product $product
     * @param array $params
     * @return \Hzmwelec\CPQ\DTOs\ProductQuoteDTO
     */
    public function quoteProduct($product, $params)
    {
        $costQuoteDTOs = $this->quoteCosts($product, $params);

        $leadtimeQuoteDTO = $this->quoteLeadtime($product, $params);

        return new ProductQuoteDTO($costQuoteDTOs, $leadtimeQuoteDTO);
    }

    /**
     * @param \Hzmwelec\CPQ\Models\Product $product
     * @param array $params
     * @return \Illuminate\Support\Collection
     */
    public function quoteCosts($product, $params)
    {
        $costQuoteDTOs = new Collection();

        foreach ($product->costs as $cost) {
            $quoteCostDto = $this->quoteCost($cost, $params);

            if (!is_null($quoteCostDto)) {
                $costQuoteDTOs->push($quoteCostDto);
            }
        }

        return $costQuoteDTOs;
    }

    /**
     * @param \Hzmwelec\CPQ\Models\Cost $cost
     * @param array $params
     * @return \Hzmwelec\CPQ\DTOs\CostQuoteDTO|null
     */
    public function quoteCost($cost, $params)
    {
        $expression = new ExpressionLanguage();

        foreach ($cost->rules as $rule) {
            if (empty($rule->condition) || (bool) $expression->evaluate($rule->condition, $params)) {
                $price = (float) $expression->evaluate($rule->action, $params);

                return new CostQuoteDTO($price, $cost, $rule);
            }
        }

        return null;
    }

    /**
     * @param \Hzmwelec\CPQ\Models\Product $product
     * @param array $params
     * @return \Hzmwelec\CPQ\DTOs\LeadtimeQuoteDTO|null
     */
    public function quoteLeadtime($product, $params)
    {
        $expression = new ExpressionLanguage();

        foreach ($product->leadtimes as $leadtime) {
            if (empty($leadtime->condition) || (bool) $expression->evaluate($leadtime->condition, $params)) {
                return new LeadtimeQuoteDTO($leadtime);
            }
        }

        return null;
    }
}
