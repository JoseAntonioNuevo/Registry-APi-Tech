<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Documentation for SmartPoint API",
 *     version="1.0.0",
 *     description="API documentation for SmartPoint API",
 *     @OA\Contact(
 *         email="joseantonionuevo@gmail.com"
 *     )
 * ),
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local server"
 * )
 */

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Inverted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/check/{item}",
     *     summary="Check if an item exists",
     *     tags={"Registry"},
     *     @OA\Parameter(
     *         name="item",
     *         in="path",
     *         required=true,
     *         description="The name of the item to check",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=500, description="NOT OK")
     * )
     */
    public function check($item)
    {
        try {
            $itemName = $this->validateItem($item);
            $exists = Item::where('name', $itemName)->exists();

            $inversionState = Inverted::first();
            $isInverted = $inversionState ? $inversionState->inverted : false;

            if ($isInverted) {
                $exists = !$exists;
            }

            return response()->json(['exists' => $exists]);
        } catch (\Exception $e) {
            Log::error('Error in check method: ' . $e->getMessage());
            return response()->json(['message' => 'NOT OK'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/add",
     *     summary="Add a new item",
     *     tags={"Registry"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="item", type="string", description="The name of the item to add")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=400, description="Item already exists"),
     *     @OA\Response(response=500, description="NOT OK")
     * )
     */
    public function add(Request $request)
    {
        DB::beginTransaction();
        try {
            $itemName = $this->validateItem($request->input('item'));
            $item = Item::where('name', $itemName)->first();
            if ($item) {
                return response()->json(['message' => 'Item already exists'], 400);
            }
            Item::create(['name' => $itemName]);
            DB::commit();
            return response()->json(['message' => 'OK']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in add method: ' . $e->getMessage());
            return response()->json(['message' => 'NOT OK'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/remove",
     *     summary="Remove an item",
     *     tags={"Registry"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="item", type="string", description="The name of the item to remove")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=500, description="NOT OK")
     * )
     */
    public function remove(Request $request)
    {
        DB::beginTransaction();
        try {
            $itemName = $this->validateItem($request->input('item'));
            Item::where('name', $itemName)->delete();
            DB::commit();
            return response()->json(['message' => 'OK']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in remove method: ' . $e->getMessage());
            return response()->json(['message' => 'NOT OK'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/diff",
     *     summary="Get the difference between submitted and current items",
     *     tags={"Registry"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="items", type="array", @OA\Items(type="string"), description="The list of items to compare")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=500, description="NOT OK")
     * )
     */
    public function diff(Request $request)
    {
        DB::beginTransaction();
        try {
            $submittedSet = array_map('trim', $this->validateSet($request->input('items')));
            $currentSet = Item::pluck('name')->toArray();
            $diff = array_values(array_diff($submittedSet, $currentSet));
            DB::commit();

            if (!empty($diff)) {
                return response()->json(['message' => 'OK', 'diff' => implode(', ', $diff)]);
            } else {
                return response()->json(['message' => 'diff', 'items' => implode(', ', $submittedSet)]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in diff method: ' . $e->getMessage());
            return response()->json(['message' => 'NOT OK'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/invert",
     *     summary="Invert the state of the registry",
     *     tags={"Registry"},
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=500, description="NOT OK")
     * )
     */
    public function invert()
    {
        DB::beginTransaction();
        try {
            $inversionState = Inverted::first();
            if (!$inversionState) {
                $inversionState = Inverted::create(['inverted' => false]);
            }

            $inversionState->inverted = !$inversionState->inverted;
            $inversionState->save();

            DB::commit();
            return response()->json(['message' => 'OK']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in invert method: ' . $e->getMessage());
            return response()->json(['message' => 'NOT OK'], 500);
        }
    }

    private function validateItem($item)
    {
        if (!preg_match('/^[a-zA-Z0-9 ]+$/', $item)) {
            abort(400, 'Invalid item. Only alphanumeric characters and spaces are allowed.');
        }
        return trim($item);
    }

    private function validateSet($items)
    {
        if (empty($items) || !is_array($items)) {
            abort(400, 'Invalid set. Provide an array of items.');
        }
        return $items;
    }
}
