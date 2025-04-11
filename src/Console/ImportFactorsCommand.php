<?php

namespace Hzmwelec\CPQ\Console;

use Exception;
use Hzmwelec\CPQ\Exceptions\InvalidArgumentException;
use Hzmwelec\CPQ\Exceptions\RuntimeException;
use Hzmwelec\CPQ\Imports\FactorImport;
use Hzmwelec\CPQ\Models\Product;
use Hzmwelec\CPQ\Models\Version;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportFactorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cpq:import-factors {product_id} {factor_file_path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import CPQ factors from an excel file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $product = $this->getProduct();

            $filePath = $this->validateFactorFilePath();

            $this->importFactors($product, $filePath);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        $this->info('Import success');
    }

    /**
     * @return \Hzmwelec\CPQ\Models\Product
     * @throws \Hzmwelec\CPQ\Exceptions\InvalidArgumentException
     */
    protected function getProduct()
    {
        $productId = $this->argument('product_id');

        $product = Product::find($productId);

        if (!$product) {
            throw new InvalidArgumentException("Product with ID {$productId} not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Product's version not found");
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Product is not editable');
        }

        return $product;
    }

    /**
     * @return string
     * @throws \Hzmwelec\CPQ\Exceptions\InvalidArgumentException
     */
    protected function validateFactorFilePath()
    {
        $filePath = $this->argument('factor_file_path');

        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("The file '{$filePath}' does not exist");
        }

        return $filePath;
    }

    /**
     * @param \Hzmwelec\CPQ\Models\Product $product
     * @param string $filePath
     * @return void
     */
    protected function importFactors($product, $filePath)
    {
        Excel::import(new FactorImport($product), $filePath);
    }
}
