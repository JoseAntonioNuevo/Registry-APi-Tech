<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Inverted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistryController extends Controller
{

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
            return response()->json(['message' => 'KO'], 500);
        }
    }

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
            return response()->json(['message' => 'KO'], 500);
        }
    }

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
            return response()->json(['message' => 'KO'], 500);
        }
    }

    public function diff(Request $request)
    {
        DB::beginTransaction();
        try {
            $submittedSet = array_map('trim', $this->validateSet($request->input('items')));
            $currentSet = Item::pluck('name')->toArray();
            DB::commit();
            return response()->json(['diff' => array_values(array_diff($submittedSet, $currentSet))]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in diff method: ' . $e->getMessage());
            return response()->json(['message' => 'KO'], 500);
        }
    }

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
            return response()->json(['message' => 'KO'], 500);
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
