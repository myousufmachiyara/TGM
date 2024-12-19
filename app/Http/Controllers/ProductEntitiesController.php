<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\ProductColor;
use App\Models\ProductMeasurementUnit;

class ProductEntitiesController extends Controller
{
    // Handle different entities dynamically
    private function getModel($entity)
    {
        return match ($entity) {
            'product_categories' => ProductCategory::class,
            'product_colors' => ProductColor::class,
            'product_measurement_units' => ProductMeasurementUnit::class,
            default => throw new \Exception("Invalid entity"),
        };
    }

    // Index function for listing entities
    public function index($entity)
    {
        $model = $this->getModel($entity);
        $items = $model::all();  // Get all records for the specified entity
        return view('product-entities.index', compact('items', 'entity'));
    }

    // Store new entity data
    public function store(Request $request, $entity)
    {
        $model = $this->getModel($entity);

        // Validate input data (optional, based on your form fields)
        $validatedData = $request->validate([
            'name' => 'required|string|max:255', // Adjust based on your form fields
        ]);

        // Create the entity using mass assignment
        $model::create($validatedData);

        // Return a success response
        return response()->json(['success' => true]);
    }

    public function update(Request $request, $entity, $id)
    {
        // Dynamically retrieve the model
        $model = $this->getModel($entity);

        try {
            // Find the item by ID
            $item = $model::findOrFail($id);

            // Update the item's fields with request data
            $item->name = $request->input('name'); // You can add more fields here
            $item->save();  // Save the updated item

            // Return success response
            return response()->json([
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $entity)) . ' updated successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating ' . $entity . ': ' . $e->getMessage()); // Log the error message

            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Error updating ' . ucfirst(str_replace('_', ' ', $entity)) . '.',
            ], 500);
        }
    }

    public function destroy($entity, $id)
    {
        $model = $this->getModel($entity);  // Get the model based on the entity
        $item = $model::findOrFail($id);    // Find the record to delete
        
        // Delete the record
        $item->delete();

        // Return response or redirect
        return redirect()->route($entity . '.index')->with('success', ucfirst(str_replace('_', ' ', $entity)) . ' deleted successfully!');
    }
}
