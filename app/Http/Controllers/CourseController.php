<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use App\Models\AcademicStream;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // Get all departments and academic streams
        $departments = Department::orderBy('name')->get();
        $academicStreams = AcademicStream::orderBy('stream_name')->get();
        
        // Build query with filters
        $query = Course::query();
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('course_name', 'like', "%{$search}%")
                  ->orWhere('course_code', 'like', "%{$search}%");
            });
        }
        
        // Apply department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        // Apply stream filter
        if ($request->filled('stream_id')) {
            $query->where('academic_stream_id', $request->stream_id);
        }
        
        // Apply level filter
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        
        // Get paginated courses (10 per page)
        $courses = $query->with(['department', 'academicStream'])
            ->latest()
            ->paginate(10)
            ->through(function ($course) {
                return [
                    'id' => $course->id,
                    'course_name' => $course->course_name,
                    'course_code' => $course->course_code,
                    'level' => $course->level,
                    'department_id' => $course->department_id,
                    'academic_stream_id' => $course->academic_stream_id,
                    'department_name' => $course->department_name,
                    'stream_name' => $course->stream_name,
                    'created_at' => $course->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $course->updated_at->format('Y-m-d H:i:s'),
                ];
            });
        
        // Group courses by department for display
        $groupedCourses = [];
        foreach ($courses as $course) {
            $deptId = $course['department_id'] ?? 'unassigned';
            $deptName = $course['department_name'] ?? 'Unassigned Courses';
            
            if (!isset($groupedCourses[$deptId])) {
                $groupedCourses[$deptId] = [
                    'department_id' => $deptId,
                    'department_name' => $deptName,
                    'courses' => [],
                    'total_courses' => 0
                ];
            }
            
            $groupedCourses[$deptId]['courses'][] = $course;
            $groupedCourses[$deptId]['total_courses']++;
        }
        
        // Sort groups
        $sortedGroups = collect($groupedCourses)->sortBy(function($group) {
            return $group['department_id'] === 'unassigned' ? 1 : 0;
        })->sortBy('department_name')->values();
        
        // Get statistics
        $totalCourses = Course::count();
        $totalWithStream = Course::whereNotNull('academic_stream_id')->count();
        $totalWithLevel = Course::whereNotNull('level')->count();
        
        return Inertia::render('CreateCourse', [
            'courses' => [
                'data' => $sortedGroups,
                'links' => $courses->linkCollection()->toArray(),
                'from' => $courses->firstItem(),
                'to' => $courses->lastItem(),
                'total' => $courses->total(),
                'total_with_stream' => $totalWithStream,
                'total_with_level' => $totalWithLevel,
            ],
            'departments' => $departments->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                ];
            }),
            'academicStreams' => $academicStreams->map(function ($stream) {
                return [
                    'id' => $stream->id,
                    'stream_name' => $stream->stream_name,
                    'department_id' => $stream->department_id,
                ];
            }),
            'filters' => [
                'search' => $request->search,
                'department_id' => $request->department_id,
                'level' => $request->level,
                'stream_id' => $request->stream_id,
            ],
        ]);
    }

    public function create(Request $request)
    {
        // Redirect to index with filters preserved
        return redirect()->route('courses.index', $request->only(['search', 'department_id', 'level', 'stream_id']));
    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:50|unique:courses,course_code',
            'level' => 'required|string|max:10',
            'department_id' => 'nullable|exists:departments,id',
            'academic_stream_id' => 'nullable|exists:academic_streams,id',
        ], [
            'course_name.required' => 'Course name is required.',
            'course_code.required' => 'Course code is required.',
            'course_code.unique' => 'This course code already exists.',
            'level.required' => 'Level is required.',
        ]);

        // Get department and stream names
        $department = null;
        $stream = null;
        
        if ($request->department_id) {
            $department = Department::find($request->department_id);
        }
        
        if ($request->academic_stream_id) {
            $stream = AcademicStream::find($request->academic_stream_id);
        }

        // Create the course with additional data
        Course::create([
            'course_name' => $validated['course_name'],
            'course_code' => $validated['course_code'],
            'level' => $validated['level'],
            'department_id' => $validated['department_id'],
            'academic_stream_id' => $validated['academic_stream_id'],
            'department_name' => $department ? $department->name : null,
            'stream_name' => $stream ? $stream->stream_name : null,
        ]);

        return redirect()->route('courses.index', $request->only(['search', 'department_id', 'level', 'stream_id']))
            ->with('success', 'Course created successfully!');
    }

    public function update(Request $request, Course $course)
    {
        // Validate the request
        $validated = $request->validate([
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:50|unique:courses,course_code,' . $course->id,
            'level' => 'required|string|max:10',
            'department_id' => 'nullable|exists:departments,id',
            'academic_stream_id' => 'nullable|exists:academic_streams,id',
        ], [
            'course_name.required' => 'Course name is required.',
            'course_code.required' => 'Course code is required.',
            'course_code.unique' => 'This course code already exists.',
            'level.required' => 'Level is required.',
        ]);

        // Get department and stream names
        $department = null;
        $stream = null;
        
        if ($request->department_id) {
            $department = Department::find($request->department_id);
        }
        
        if ($request->academic_stream_id) {
            $stream = AcademicStream::find($request->academic_stream_id);
        }

        // Update the course
        $course->update([
            'course_name' => $validated['course_name'],
            'course_code' => $validated['course_code'],
            'level' => $validated['level'],
            'department_id' => $validated['department_id'],
            'academic_stream_id' => $validated['academic_stream_id'],
            'department_name' => $department ? $department->name : null,
            'stream_name' => $stream ? $stream->stream_name : null,
        ]);

        return redirect()->route('courses.index', $request->only(['search', 'department_id', 'level', 'stream_id']))
            ->with('success', 'Course updated successfully!');
    }

    public function destroy(Request $request, Course $course)
    {
        $course->delete();

        return redirect()->route('courses.index', $request->only(['search', 'department_id', 'level', 'stream_id']))
            ->with('success', 'Course deleted successfully!');
    }

    // Get streams by department (for AJAX)
    public function getStreamsByDepartment($departmentId)
    {
        $streams = AcademicStream::where('department_id', $departmentId)
            ->orderBy('stream_name')
            ->get()
            ->map(function ($stream) {
                return [
                    'id' => $stream->id,
                    'stream_name' => $stream->stream_name,
                ];
            });

        return response()->json($streams);
    }
}