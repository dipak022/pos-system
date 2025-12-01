<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface ProductRepositoryInterface
{
    public function findById(int $id): Product;
    public function findByIds(array $ids): Collection;
    public function getAll(): Collection;
    public function create(array $data): Product;
    public function update(int $id, array $data): Product;
    public function decrementStock(int $productId, int $quantity): bool;
}


