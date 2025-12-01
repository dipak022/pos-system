<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Repositories\ProductRepositoryInterface;
use App\Services\Offers\OfferCalculatorFactory;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ProductNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class POSService
{
    private ProductRepositoryInterface $productRepository;
    private OfferCalculatorFactory $offerCalculatorFactory;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        OfferCalculatorFactory $offerCalculatorFactory
    ) {
        $this->productRepository = $productRepository;
        $this->offerCalculatorFactory = $offerCalculatorFactory;
    }

    /**
     * Process a sale transaction
     *
     * @param int $userId
     * @param array $items
     * @return array
     * @throws InsufficientStockException
     * @throws ProductNotFoundException
     * @throws \Throwable
     */
    public function processSale(int $userId, array $items): array
    {
        return DB::transaction(function () use ($userId, $items) {
            // Validate and prepare items
            $preparedItems = $this->prepareItems($items);

            // Validate stock availability for all items
            $this->validateStock($preparedItems);

            // Calculate totals
            $calculation = $this->calculateTotals($preparedItems);

            // Create sale record
            $sale = $this->createSale($userId, $calculation);

            // Create sale items and update stock
            $saleItems = $this->createSaleItems($sale, $preparedItems);

            // Update product stock
            $this->updateProductStock($preparedItems);

            // Prepare response
            return $this->prepareResponse($sale, $saleItems, $calculation);
        });
    }

    /**
     * Prepare and validate items
     *
     * @param array $items
     * @return array
     * @throws ProductNotFoundException
     */
    private function prepareItems(array $items): array
    {
        $productIds = array_column($items, 'product_id');
        $products = $this->productRepository->findByIds($productIds)
            ->keyBy('id');

        $preparedItems = [];

        foreach ($items as $item) {
            $productId = $item['product_id'];

            if (!$products->has($productId)) {
                throw new ProductNotFoundException(
                    "Product with ID {$productId} not found"
                );
            }

            $product = $products->get($productId);
            $quantity = $item['quantity'];

            // Calculate offer using strategy pattern
            $strategy = $this->offerCalculatorFactory->getStrategy($product);
            $offerCalculation = $strategy->calculate($product, $quantity);

            // Calculate total quantity needed (including free items)
            $totalQuantityNeeded = $quantity + $offerCalculation['free_quantity'];

            $preparedItems[] = [
                'product' => $product,
                'requested_quantity' => $quantity,
                'total_quantity_needed' => $totalQuantityNeeded,
                'calculation' => $offerCalculation,
            ];
        }

        return $preparedItems;
    }

    /**
     * Validate stock availability
     *
     * @param array $preparedItems
     * @throws InsufficientStockException
     */
    private function validateStock(array $preparedItems): void
    {
        foreach ($preparedItems as $item) {
            $product = $item['product'];
            $totalNeeded = $item['total_quantity_needed'];

            if ($product->stock < $totalNeeded) {
                throw new InsufficientStockException(
                    sprintf(
                        'Insufficient stock for product "%s". Available: %d, Required: %d',
                        $product->name,
                        $product->stock,
                        $totalNeeded
                    )
                );
            }
        }
    }

    /**
     * Calculate totals for the sale
     *
     * @param array $preparedItems
     * @return array
     */
    private function calculateTotals(array $preparedItems): array
    {
        $subtotal = 0;
        $totalDiscount = 0;
        $itemBreakdown = [];

        foreach ($preparedItems as $item) {
            $calculation = $item['calculation'];
            $product = $item['product'];

            $subtotal += $calculation['subtotal'];
            $totalDiscount += $calculation['discount_amount'];

            $itemBreakdown[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $calculation['quantity'],
                'free_quantity' => $calculation['free_quantity'],
                'unit_price' => $calculation['unit_price'],
                'discounted_unit_price' => $calculation['discounted_unit_price'],
                'discount_percentage' => $calculation['discount_percentage'],
                'discount_amount' => $calculation['discount_amount'],
                'subtotal' => $calculation['subtotal'],
                'offer_type' => $calculation['offer_type'],
                'offer_details' => $calculation['offer_details'],
            ];
        }

        $total = $subtotal;

        return [
            'subtotal' => round($subtotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'total' => round($total, 2),
            'items_breakdown' => $itemBreakdown,
        ];
    }

    /**
     * Create sale record
     *
     * @param int $userId
     * @param array $calculation
     * @return Sale
     */
    private function createSale(int $userId, array $calculation): Sale
    {
        return Sale::create([
            'user_id' => $userId,
            'subtotal' => $calculation['subtotal'],
            'discount_amount' => $calculation['total_discount'],
            'total' => $calculation['total'],
        ]);
    }

    /**
     * Create sale items
     *
     * @param Sale $sale
     * @param array $preparedItems
     * @return array
     */
    private function createSaleItems(Sale $sale, array $preparedItems): array
    {
        $saleItems = [];

        foreach ($preparedItems as $item) {
            $calculation = $item['calculation'];
            $product = $item['product'];

            $saleItem = SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $calculation['quantity'],
                'free_quantity' => $calculation['free_quantity'],
                'unit_price' => $calculation['unit_price'],
                'discount_percentage' => $calculation['discount_percentage'],
                'discount_amount' => $calculation['discount_amount'],
                'subtotal' => $calculation['subtotal'],
                'offer_type' => $calculation['offer_type'],
                'offer_details' => $calculation['offer_details'],
            ]);

            $saleItems[] = $saleItem;
        }

        return $saleItems;
    }

    /**
     * Update product stock
     *
     * @param array $preparedItems
     */
    private function updateProductStock(array $preparedItems): void
    {
        foreach ($preparedItems as $item) {
            $product = $item['product'];
            $totalQuantityNeeded = $item['total_quantity_needed'];

            $product->decrement('stock', $totalQuantityNeeded);

            Log::info('Stock updated', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity_deducted' => $totalQuantityNeeded,
                'remaining_stock' => $product->fresh()->stock,
            ]);
        }
    }

    /**
     * Prepare response
     *
     * @param Sale $sale
     * @param array $saleItems
     * @param array $calculation
     * @return array
     */
    private function prepareResponse(Sale $sale, array $saleItems, array $calculation): array
    {
        return [
            'success' => true,
            'message' => 'Sale processed successfully',
            'data' => [
                'sale_id' => $sale->id,
                'subtotal' => $calculation['subtotal'],
                'total_discount' => $calculation['total_discount'],
                'total' => $calculation['total'],
                'items' => $calculation['items_breakdown'],
                'created_at' => $sale->created_at->toISOString(),
            ],
        ];
    }
}
