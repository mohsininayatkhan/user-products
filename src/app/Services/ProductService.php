<?php
namespace App\Services;

use App\Models\Product;
use DB;

class ProductService
{
    public function getAll(int $recordPerPage=15)
    {
        return DB::table('products')->paginate(15);
    }

    public function getByUserId(int $userId, int $recordPerPage=15)
    {        
        return DB::table('products')
            ->select('products.sku', 'products.name')
            ->join('product_user','products.sku', '=', 'product_user.product_sku')
            ->where('product_user.user_id', $userId)
            ->paginate(15);
    }

    public function addUserProduct(int $userId, string $sku)
    {
        $id = DB::table('product_user')->insertGetId(
            ['user_id' => $userId, 'product_sku' => $sku]
        );

        return $id;
    }

    public function isAlreadyPurchased(int $userId, string $sku)
    {
        return DB::table('product_user')->where([
            ['user_id', '=', $userId],
            ['product_sku', '=', $sku]
        ])->exists();
    }

    public function removePurchasedProduct(int $userId, string $sku)
    {
        return DB::table('product_user')->where([
            ['user_id', '=', $userId],
            ['product_sku', '=', $sku]
        ])->delete();
    }

    public function getUserUnpurchasedProducts(int $userId)
    {
        $purchased = DB::table('product_user')
            ->where('user_id', $userId)
            ->pluck('product_sku')
            ->toArray();
        
        return DB::table('products')->whereNotIn('sku', $purchased)->get();
    }
}