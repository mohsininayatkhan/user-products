<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\ProductService;

class UserController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
       $this->productService = $productService;
    }

    public function getUserProducts(Request $request)
    {
        $user = $request->user();
        return $this->productService->getByUserId($user->id);
    }

    public function addUserProduct(Request $request)
    {         
        $this->addProductvalidator($request->all())->validate();

        $user = $request->user(); 
        $sku = $request->sku; 

        $isPurchased = $this->productService->isAlreadyPurchased($user->id, $sku);

        if ($isPurchased) {
            return new JsonResponse(
                [
                    'message' => 'The given data was invalid.', 
                    'errors' => [ 'sku' => 
                        ['Already exists']
                    ]
                ], 409);
        }
        
        if ($this->productService->addUserProduct($user->id, $sku)) {
            return new JsonResponse(['sku' => $sku], 201);
        }

        return new JsonResponse(['error' => 'Error while creating'], 400);
    }

    public function removeUserProduct(Request $request, $sku)
    {
        $user = $request->user();

        $isPurchased = $this->productService->isAlreadyPurchased($user->id, $sku);

        if ($isPurchased) {
            $delete = $this->productService->removePurchasedProduct($user->id, $sku);
            
            if ($delete) {
                return new JsonResponse(['success' => 'Rmoved successfully'], 200);
            }
        }

        return new JsonResponse(['error' => 'Record not found'], 404);
    }

    protected function addProductvalidator(array $data)
    {
        return Validator::make($data, [
            'sku' => ['required', 'string', 'exists:products']
        ]);
    }
}
