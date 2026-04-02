<?php
namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CustomerRepository extends BaseRepository
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    public function getAll(): Collection
    {
        return $this->all();
    }

    public function search(string $query): Collection
    {
        return $this->model->with('addresses')
            ->where('name', 'like', "%$query%")
            ->orWhere('phone', 'like', "%$query%")
            ->limit(10)
            ->get();
    }
}
