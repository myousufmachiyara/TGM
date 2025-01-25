<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductMeasurementUnit;
use Illuminate\Database\QueryException;

class ProductMeasurementUnitsController extends Controller
{
  
    public function index()
    {
        $items = ProductMeasurementUnit::all(); // Retrieve all products
        return view('product-attributes.measurement-units', compact('items')); // Return the view
    }

    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $request->validate([
                'name' => 'required|string|unique:product_measurement_units|max:255',
            ]);
    
            // Create a new measurement unit
            ProductMeasurementUnit::create($request->only('name'));
    
            // Redirect to the index route with a success message
            return redirect()->route('product-measurement-units.index') // Ensure this route matches your route file
                ->with('success', 'Measurement Unit created successfully.');
        } catch (QueryException $e) {
            // Handle database-related exceptions
            return back()->withErrors(['error' => 'A database error occurred while creating the Measurement Unit. Please try again.']);
        } catch (\Exception $e) {
            // Handle any other exceptions
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:product_measurement_units,name,' . $id . '|max:255',
        ]);

        $item = ProductMeasurementUnit::findOrFail($id); // Find the unit by ID
        $item->update($request->all()); // Update the measurement unit

        return redirect()->route('measurement-units.index')
            ->with('success', 'Measurement Unit updated successfully.'); // Redirect with success message
    }

    public function destroy($id)
    {
        $item = ProductMeasurementUnit::findOrFail($id); // Find the unit by ID
        $item->delete(); // Delete the measurement unit

        return redirect()->route('product-measurement-units.index')->with('success', 'Measurement Unit deleted successfully.'); // Redirect with success message
    }
}
