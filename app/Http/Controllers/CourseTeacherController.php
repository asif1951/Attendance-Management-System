<?php

namespace App\Http\Controllers;

use App\Models\CourseTeacher;
use App\Models\User;
use App\Models\Department;
use App\Models\AcademicStream;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseTeacherController extends Controller
{
    public function index(Request $request)
    {
        // Build query with filters
        $query = CourseTeacher::with(['user', 'department', 'academicStream', 'course']);
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('session', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        if ($request->filled('academic_stream_id')) {
            $query->where('academic_stream_id', $request->academic_stream_id);
        }
        
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        
        if ($request->filled('session')) {
            $query->where('session', 'like', "%{$request->session}%");
        }
        
        // Get paginated results (15 per page)
        $courseTeachers = $query->latest()->paginate(15);
        
        // Transform the data
        $courseTeachers->getCollection()->transform(function ($ct) {
            return [
                'id' => $ct->id,
                'user_id' => $ct->user_id,
                'teacher_name' => $ct->user->name,
                'teacher_email' => $ct->user->email,
                'phone_number' => $ct->phone_number,
                'department_id' => $ct->department_id,
                'department_name' => $ct->department ? $ct->department->name : null,
                'academic_stream_id' => $ct->academic_stream_id,
                'stream_name' => $ct->academicStream ? $ct->academicStream->stream_name : null,
                'course_id' => $ct->course_id,
                'course_name' => $ct->course ? $ct->course->course_name : null,
                'course_code' => $ct->course ? $ct->course->course_code : null,
                'level' => $ct->level,
                'session' => $ct->session,
                'created_at' => $ct->created_at->format('Y-m-d H:i:s'),
            ];
        });
        
        // Get all users for dropdowns
        $users = User::orderBy('name')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });
        
        $departments = Department::orderBy('name')->get();
        $academicStreams = AcademicStream::orderBy('stream_name')->get();
        $courses = Course::orderBy('course_name')->get();
        
        return Inertia::render('CourseTeacher', [
            'courseTeachers' => $courseTeachers,
            'users' => $users,
            'departments' => $departments,
            'academicStreams' => $academicStreams,
            'courses' => $courses,
            'filters' => $request->only(['search', 'department_id', 'academic_stream_id', 'level', 'session']),
        ]);
    }
    
    public function create()
    {
        return redirect()->route('course-teachers.index');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'phone_number' => 'required|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'academic_stream_id' => 'nullable|exists:academic_streams,id',
            'course_id' => 'nullable|exists:courses,id',
            'level' => 'nullable|string|max:10',
            'session' => 'nullable|string|max:255',
        ]);
        
        // Check if teacher already assigned to this course
        $existing = CourseTeacher::where('user_id', $validated['user_id'])
            ->where('course_id', $validated['course_id'])
            ->first();
        
        if ($existing) {
            return redirect()->back()->withErrors([
                'course_id' => 'This teacher is already assigned to this course.'
            ]);
        }
        
        CourseTeacher::create($validated);
        
        return redirect()->route('course-teachers.index')
            ->with('success', 'Course teacher assigned successfully!');
    }
    
    public function show(CourseTeacher $courseTeacher)
    {
        return redirect()->route('course-teachers.index');
    }
    
    public function edit(CourseTeacher $courseTeacher)
    {
        return redirect()->route('course-teachers.index');
    }
    
    public function update(Request $request, CourseTeacher $courseTeacher)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'phone_number' => 'required|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'academic_stream_id' => 'nullable|exists:academic_streams,id',
            'course_id' => 'nullable|exists:courses,id',
            'level' => 'nullable|string|max:10',
            'session' => 'nullable|string|max:255',
        ]);
        
        // Check if teacher already assigned to this course (excluding current)
        $existing = CourseTeacher::where('user_id', $validated['user_id'])
            ->where('course_id', $validated['course_id'])
            ->where('id', '!=', $courseTeacher->id)
            ->first();
        
        if ($existing) {
            return redirect()->back()->withErrors([
                'course_id' => 'This teacher is already assigned to this course.'
            ]);
        }
        
        $courseTeacher->update($validated);
        
        return redirect()->route('course-teachers.index')
            ->with('success', 'Course teacher updated successfully!');
    }
    
    public function destroy(CourseTeacher $courseTeacher)
    {
        $courseTeacher->delete();
        
        return redirect()->route('course-teachers.index')
            ->with('success', 'Course teacher removed successfully!');
    }
    
    // Get courses by department, stream and level
    public function getCoursesByFilters(Request $request)
    {
        $query = Course::query();
        
        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        
        if ($request->academic_stream_id) {
            $query->where('academic_stream_id', $request->academic_stream_id);
        }
        
        if ($request->level) {
            $query->where('level', $request->level);
        }
        
        $courses = $query->orderBy('course_name')->get()->map(function ($course) {
            return [
                'id' => $course->id,
                'course_name' => $course->course_name,
                'course_code' => $course->course_code,
            ];
        });
        
        return response()->json($courses);
    }
    
    // Get email by user ID
    public function getEmailByUser($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        return response()->json(['email' => $user->email]);
    }
    
    // Get streams by department
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