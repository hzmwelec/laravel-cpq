<?php

namespace Hzmwelec\CPQ\Imports;

use Hzmwelec\CPQ\Models\Factor;
use Hzmwelec\CPQ\Models\FactorOption;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FactorImport implements ToCollection, WithHeadingRow
{
    /**
     * @var \Hzmwelec\CPQ\Models\Product
     */
    protected $product;

    /**
     * @param \Hzmwelec\CPQ\Models\Product $product
     */
    public function __construct($product)
    {
        $this->product = $product;
    }

    /**
     * @param \Illuminate\Support\Collection $rows
     * @return void
     */
    public function collection(Collection $rows)
    {
        $grouped = $this->group($rows);

        $this->save($grouped);
    }

    /**
     * @param \Illuminate\Support\Collection $rows
     * @return array
     */
    protected function group(Collection $rows)
    {
        $grouped = [];

        foreach ($rows as $row) {
            if (!empty($row['name']) && !empty($row['code'])) {
                if ($row['type'] == 1) {
                    $grouped[] = [
                        'name' => $row['name'],
                        'code' => $row['code'],
                        'description' => $row['description'] ?? '',
                        'options' => [],
                    ];
                } else {
                    $lastIndex = count($grouped) - 1;

                    if ($lastIndex >= 0) {
                        $grouped[$lastIndex]['options'][] = [
                            'name' => $row['name'],
                            'code' => $row['code'],
                            'description' => $row['description'] ?? '',
                        ];
                    }
                }
            }
        }

        return $grouped;
    }

    /**
     * @param array $groupedFactors
     * @return void
     */
    protected function save($groupedFactors)
    {
        foreach ($groupedFactors as $factorItem) {
            $factor = Factor::updateOrCreate(
                [
                    'product_id' => $this->product->id,
                    'code' => $factorItem['code'],
                ],
                [
                    'name' => $factorItem['name'],
                    'description' => $factorItem['description'],
                    'is_optional' => !empty($factorItem['options']),
                ]
            );

            foreach ($factorItem['options'] as $optionItem) {
                FactorOption::updateOrCreate(
                    [
                        'factor_id' => $factor->id,
                        'code' => $optionItem['code'],
                    ],
                    [
                        'name' => $optionItem['name'],
                        'description' => $optionItem['description'],
                    ]
                );
            }
        }
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'type',
            'name',
            'code',
            'description',
        ];
    }
}
