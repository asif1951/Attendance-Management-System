<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    /**
     * Show the form for creating a new department.
     */
    public function create(Request $request)
    {
        // Build query with filters
        $query = Department::query();
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        
        // Get paginated results (10 per page)
        $departments = $query->latest()->paginate(10);
        
        return Inertia::render('CreateDepartment', [
            'departments' => $departments,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
        ]);

        // Store in database
        Department::create($validated);

        // Return success response
        return redirect()->route('departments.create')
            ->with('success', 'Department created successfully!');
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        // Update in database
        $department->update($validated);

        // Return success response
        return redirect()->route('departments.create')
            ->with('success', 'Department updated successfully!');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        // Delete from database
        $department->delete();

        // Return success response
        return redirect()->route('departments.create')
            ->with('success', 'Department deleted successfully!');
    }
}