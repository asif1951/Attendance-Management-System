<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\AcademicStream;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassArmController extends Controller
{
    /**
     * Show the form for creating a new class arm.
     */
    public function create(Request $request)
    {
        // Build query with filters
        $query = AcademicStream::with('department');
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('stream_name', 'like', "%{$search}%")
                  ->orWhereHas('department', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Apply department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Get paginated results (10 per page)
        $academicStreams = $query->latest()->paginate(10);
        
        // Transform the data
        $academicStreams->getCollection()->transform(function ($stream) {
            return [
                'id' => $stream->id,
                'department_name' => $stream->department->name,
                'stream_name' => $stream->stream_name,
                'status' => $stream->status,
                'created_at' => $stream->created_at,
            ];
        });
        
        // Get all departments for dropdown
        $departments = Department::all();
        
        return Inertia::render('CreateClassArms', [
            'departments' => $departments,
            'academicStreams' => $academicStreams,
            'filters' => $request->only(['search', 'department_id', 'status']),
        ]);
    }

    /**
     * Store a newly created class arm in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'stream_name' => 'required|string|max:255|unique:academic_streams,stream_name'
        ]);

        AcademicStream::create([
            'department_id' => $validated['department_id'],
            'stream_name' => $validated['stream_name'],
            'status' => 'UnAssigned'
        ]);
        
        return redirect()->back()->with('success', 'Academic stream created successfully!');
    }

    /**
     * Update the academic stream.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'stream_name' => 'required|string|max:255|unique:academic_streams,stream_name,' . $id,
            'status' => 'required|in:Assigned,UnAssigned'
        ]);

        $stream = AcademicStream::findOrFail($id);
        $stream->update([
            'stream_name' => $validated['stream_name'],
            'status' => $validated['status']
        ]);
        
        return redirect()->back()->with('success', 'Academic stream updated successfully!');
    }

    /**
     * Delete the academic stream.
     */
    public function destroy($id)
    {
        $stream = AcademicStream::findOrFail($id);
        $stream->delete();
        
        return redirect()->back()->with('success', 'Academic stream deleted successfully!');
    }
}