<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SaleController extends Controller
{
    // Rule 1: Buy One, Get One Free (Equal or Lesser Value)
    public function rule1(Request $request)
    {
        $products = $request->input('products', []);
        if (count($products) < 2) {
            return response()->json([
                'message' => 'Insufficient products to apply Rule 1.'
            ], 400);
        }

        rsort($products);

        $discountedItems = [];
        $payableItems = [];

        while (count($products) > 1) {
            $payableItems[] = array_shift($products);
            $discountedItems[] = array_shift($products);
        }

        if (count($products) == 1) {
            $payableItems[] = array_shift($products);
        }

        return response()->json([
            'discountedItems' => $discountedItems,
            'payableItems' => $payableItems
        ]);
    }

    // Rule 2: Buy One, Get One Free (Strictly Lesser Value)
    public function rule2(Request $request)
    {
        $products = $request->input('products', []);
        if (count($products) < 2) {
            return response()->json([
                'message' => 'Insufficient products to apply Rule 2.'
            ], 400);
        }

        rsort($products);

        $discountedItems = [];
        $payableItems = [];

        while (count($products) > 1) {
            $payableItem = array_shift($products);
            $payableItems[] = $payableItem;

            $found = false;
            foreach ($products as $key => $product) {
                if ($product < $payableItem) {
                    $discountedItems[] = $product;
                    unset($products[$key]);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                return response()->json([
                    'message' => 'No eligible product found for discount.'
                ], 400);
            }
        }

        if (count($products) == 1) {
            $payableItems[] = array_shift($products);
        }

        return response()->json([
            'discountedItems' => $discountedItems,
            'payableItems' => $payableItems
        ]);
    }


    // Rule 3: Buy Two, Get Two Free (Strictly Lesser Value)

    public function rule3(Request $request)
    {
        $products = $request->input('products', []);
        if (count($products) < 4) {
            return response()->json([
                'message' => 'Insufficient products to apply Rule 3.'
            ], 400);
        }

        rsort($products);

        $discountedItems = [];
        $payableItems = [];

        while (count($products) >= 4) {
            $payableItem1 = array_shift($products);
            $payableItem2 = array_shift($products);
            $payableItems[] = $payableItem1;
            $payableItems[] = $payableItem2;

            $discountItem1 = null;
            $discountItem2 = null;

            foreach ($products as $key => $product) {
                if ($product < $payableItem1 && !$discountItem1) {
                    $discountItem1 = $product;
                    unset($products[$key]);
                } elseif ($product < $payableItem2 && !$discountItem2) {
                    $discountItem2 = $product;
                    unset($products[$key]);
                }

                if ($discountItem1 && $discountItem2) {
                    break;
                }
            }

            if ($discountItem1) {
                $discountedItems[] = $discountItem1;
            }

            if ($discountItem2) {
                $discountedItems[] = $discountItem2;
            }
        }

        foreach ($products as $product) {
            $payableItems[] = $product;
        }

        return response()->json([
            'discountedItems' => $discountedItems,
            'payableItems' => $payableItems
        ]);
    }
}
