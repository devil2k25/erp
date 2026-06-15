<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function all(array $filters = [], array $relations = []): Collection;
    public function paginate(int $perPage = 15, array $filters = [], array $relations = []): LengthAwarePaginator;
    public function find(string $id, array $relations = []): ?Model;
    public function findOrFail(string $id, array $relations = []): Model;
    public function create(array $data): Model;
    public function update(string $id, array $data): Model;
    public function delete(string $id): bool;
}
