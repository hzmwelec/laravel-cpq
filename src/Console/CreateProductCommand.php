<?php

namespace Hzmwelec\CPQ\Console;

use Exception;
use Hzmwelec\CPQ\Services\ProductService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class CreateProductCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cpq:create-product {version_id} {product_name} {product_code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create CPQ product';

    /**
     * @var \Hzmwelec\CPQ\Services\ProductService
     */
    protected $productService;

    /**
     * @param \Hzmwelec\CPQ\Services\ProductService $productService
     */
    public function __construct(ProductService $productService)
    {
        parent::__construct();

        $this->productService = $productService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $product = $this->productService->createProduct($this->argument('version_id'), [
                'name' => $this->argument('product_name'),
                'code' => $this->argument('product_code'),
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();

            return $this->error(reset($errors)[0]);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        $this->info('Product Id: ' . $product->id);
        $this->info('Product Name: ' . $product->name);
        $this->info('Product Code: ' . $product->code);
    }
}
