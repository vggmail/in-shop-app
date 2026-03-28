<?php
namespace App\Repositories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;

class ExpenseRepository extends BaseRepository
{
    public function __construct(Expense $model)
    {
        parent::__construct($model);
    }

    public function getAll(): Collection
    {
        return $this->model->orderBy('date', 'DESC')->get();
    }
}
