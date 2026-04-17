<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Department;
use App\Models\AcademicStream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch departments for dropdown
        $departments = Department::orderBy('name')->get(['id', 'name']);
        
        // Fetch all streams for dropdown
        $streams = AcademicStream::orderBy('stream_name')->get(['id', 'stream_name as name']);
        
        // Fetch unique levels from students table for dropdown
        $levels = Student::select('level')
            ->whereNotNull('level')
            ->where('level', '!=', '')
            ->distinct()
            ->orderBy('level', 'asc')
            ->get();
        
        // Fetch unique sessions from students table for dropdown
        $sessions = Student::select('session')
            ->whereNotNull('session')
            ->where('session', '!=', '')
            ->distinct()
            ->orderBy('session', 'asc')
            ->get();
        
        return Inertia::render('students/Index', [
            'departmentsData' => $departments,
            'streamsData' => $streams,
            'levelsData' => $levels,
            'sessionsData' => $sessions
        ]);
    }

    /**
     * Get paginated list of students with filters
     */
    public function getStudentsList(Request $request)
    {
        try {
            $query = Student::with(['department', 'academicStream']);
            
            // Search functionality - searches in first_name, last_name (combined), email, phone, student_id
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('student_id', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            }
            
            // Filter by department
            if ($request->has('department_id') && !empty($request->department_id)) {
                $query->where('department_id', $request->department_id);
            }
            
            // Filter by academic stream
            if ($request->has('academic_stream_id') && !empty($request->academic_stream_id)) {
                $query->where('academic_stream_id', $request->academic_stream_id);
            }
            
            // Filter by level
            if ($request->has('level') && !empty($request->level)) {
                $query->where('level', $request->level);
            }
            
            // Filter by session
            if ($request->has('session') && !empty($request->session)) {
                $query->where('session', $request->session);
            }
            
            // Filter by status
            if ($request->has('is_active') && $request->is_active !== '') {
                $query->where('is_active', $request->is_active);
            }
            
            // Pagination
            $perPage = $request->get('per_page', 10);
            $students = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
            // Transform the data to include department and stream names
            $students->getCollection()->transform(function($student) {
                return [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'phone' => $student->phone,
                    'email' => $student->email,
                    'student_id' => $student->student_id,
                    'level' => $student->level,
                    'session' => $student->session,
                    'is_active' => $student->is_active,
                    'department_id' => $student->department_id,
                    'academic_stream_id' => $student->academic_stream_id,
                    'department_name' => $student->department ? $student->department->name : null,
                    'stream_name' => $student->academicStream ? $student->academicStream->stream_name : null,
                    'created_at' => $student->created_at,
                    'updated_at' => $student->updated_at,
                ];
            });
            
            return response()->json($students);
            
        } catch (\Exception $e) {
            Log::error('Error fetching students list: ' . $e->getMessage());
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'total' => 0
            ], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|min:2|max:50',
            'last_name' => 'required|string|min:2|max:50',
            'phone' => 'required|string|unique:students,phone',
            'email' => 'required|email|unique:students,email',
            'department_id' => 'required|exists:departments,id',
            'academic_stream_id' => [
                'required',
                Rule::exists('academic_streams', 'id')->where('department_id', $request->department_id)
            ],
            'student_id' => 'required|string|unique:students,student_id',
            'level' => 'required|regex:/^\d+\/\d+$/',
            'session' => 'required|regex:/^[a-zA-Z]+-\d{4}$/',
        ], [
            'academic_stream_id.exists' => 'The selected academic stream does not belong to the selected department.',
            'level.regex' => 'Level must be in format like 4/2, 3/1',
            'session.regex' => 'Session must be in format like Spring-2022, Fall-2023'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $student = Student::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'department_id' => $request->department_id,
                'academic_stream_id' => $request->academic_stream_id,
                'student_id' => $request->student_id,
                'level' => $request->level,
                'session' => $request->session,
                'is_active' => true
            ]);

            return redirect()
                ->back()
                ->with('success', 'Student added successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating student: ' . $e->getMessage());
            
            return back()
                ->withErrors(['error' => 'Failed to create student. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $student = Student::with(['department', 'academicStream'])->find($id);
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $student
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $student = Student::find($id);
        
        if (!$student) {
            return back()
                ->withErrors(['error' => 'Student not found'])
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|min:2|max:50',
            'last_name' => 'required|string|min:2|max:50',
            'phone' => ['required', 'string', Rule::unique('students', 'phone')->ignore($id)],
            'email' => ['required', 'email', Rule::unique('students', 'email')->ignore($id)],
            'department_id' => 'required|exists:departments,id',
            'academic_stream_id' => [
                'required',
                Rule::exists('academic_streams', 'id')->where('department_id', $request->department_id)
            ],
            'student_id' => ['required', 'string', Rule::unique('students', 'student_id')->ignore($id)],
            'level' => 'required|regex:/^\d+\/\d+$/',
            'session' => 'required|regex:/^[a-zA-Z]+-\d{4}$/',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $student->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'department_id' => $request->department_id,
                'academic_stream_id' => $request->academic_stream_id,
                'student_id' => $request->student_id,
                'level' => $request->level,
                'session' => $request->session,
                'is_active' => $request->is_active ?? $student->is_active
            ]);

            return redirect()
                ->back()
                ->with('success', 'Student updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating student: ' . $e->getMessage());
            
            return back()
                ->withErrors(['error' => 'Failed to update student. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $student = Student::find($id);
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        try {
            $student->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting student: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student'
            ], 500);
        }
    }

    /**
     * Get all departments for dropdown
     */
    public function getDepartments()
    {
        try {
            $departments = Department::orderBy('name')->get(['id', 'name']);
            
            return response()->json([
                'success' => true,
                'data' => $departments
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching departments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => []
            ], 500);
        }
    }

    /**
     * Get streams by department for dropdown
     */
    public function getStreamsByDepartment($departmentId)
    {
        try {
            $streams = AcademicStream::where('department_id', $departmentId)
                ->orderBy('stream_name')
                ->get(['id', 'stream_name as name']);
                
            return response()->json([
                'success' => true,
                'data' => $streams
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching academic streams: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => []
            ], 500);
        }
    }

    /**
     * Get all streams for dropdown
     */
    public function getAllStreams()
    {
        try {
            $streams = AcademicStream::orderBy('stream_name')
                ->get(['id', 'stream_name as name', 'department_id']);
                
            return response()->json([
                'success' => true,
                'data' => $streams
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching all streams: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => []
            ], 500);
        }
    }

    /**
     * Check email uniqueness
     */
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'exists' => false
            ], 422);
        }

        try {
            $query = Student::where('email', $request->email);
            
            if ($request->has('exclude_id')) {
                $query->where('id', '!=', $request->exclude_id);
            }
            
            $exists = $query->exists();

            return response()->json([
                'success' => true,
                'exists' => $exists
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'exists' => false
            ], 500);
        }
    }

    /**
     * Check phone uniqueness
     */
    public function checkPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'exists' => false
            ], 422);
        }

        try {
            $query = Student::where('phone', $request->phone);
            
            if ($request->has('exclude_id')) {
                $query->where('id', '!=', $request->exclude_id);
            }
            
            $exists = $query->exists();

            return response()->json([
                'success' => true,
                'exists' => $exists
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking phone: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'exists' => false
            ], 500);
        }
    }

    /**
     * Check student ID uniqueness
     */
    public function checkStudentId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'exists' => false
            ], 422);
        }

        try {
            $query = Student::where('student_id', $request->student_id);
            
            if ($request->has('exclude_id')) {
                $query->where('id', '!=', $request->exclude_id);
            }
            
            $exists = $query->exists();

            return response()->json([
                'success' => true,
                'exists' => $exists
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking student ID: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'exists' => false
            ], 500);
        }
    }
}