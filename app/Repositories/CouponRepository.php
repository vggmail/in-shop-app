<?php
namespace App\Repositories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CouponRepository extends BaseRepository
{
    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    public function getAll(): Collection
    {
        return $this->all();
    }

    public function findByCode(string $code): ?Coupon
    {
        return $this->model->where('code', $code)->where('status', 1)->first();
    }
}
