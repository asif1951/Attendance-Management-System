<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function index()
    {
        // Check if user is logged in
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check user role and redirect accordingly
            if ($user->role === 'admin') {
                return redirect()->route('dashboard');
            }
            
            // Get assigned courses for the user from course_teachers table
            $assignedCourses = [];
            
            // Check if user has any assigned courses in course_teachers table
            $hasAssignedCourses = DB::table('course_teachers')
                ->where('user_id', $user->id)
                ->exists();
            
            if ($hasAssignedCourses) {
                $assignedCourses = DB::table('course_teachers')
                    ->join('courses', 'course_teachers.course_id', '=', 'courses.id')
                    ->leftJoin('users', 'course_teachers.user_id', '=', 'users.id')
                    ->leftJoin('departments', 'course_teachers.department_id', '=', 'departments.id')
                    ->where('course_teachers.user_id', $user->id)
                    ->select(
                        'courses.id',
                        'course_teachers.level',
                        'courses.course_code',
                        'courses.course_name',
                        'users.name as teacher_name',
                        'departments.name as department_name',
                        DB::raw('FLOOR(RAND() * 100) as progress')
                    )
                    ->get()
                    ->toArray();
            }
            
            // Get attendance statistics for the user's courses
            $attendanceStats = [];
            if (!empty($assignedCourses)) {
                $courseIds = array_column($assignedCourses, 'id');
                
                // Get attendance records for the user (as teacher)
                $attendanceRecords = DB::table('attendances')
                    ->whereIn('course_id', $courseIds)
                    ->where('marked_by', $user->id)
                    ->select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->get();
                
                $totalRecords = $attendanceRecords->sum('count');
                $presentCount = $attendanceRecords->where('status', 'present')->sum('count');
                $absentCount = $attendanceRecords->where('status', 'absent')->sum('count');
                $lateCount = $attendanceRecords->where('status', 'late')->sum('count');
                
                $attendanceStats = [
                    'total_records' => $totalRecords,
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount,
                    'present_percentage' => $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0,
                    'absent_percentage' => $totalRecords > 0 ? round(($absentCount / $totalRecords) * 100, 1) : 0,
                    'late_percentage' => $totalRecords > 0 ? round(($lateCount / $totalRecords) * 100, 1) : 0,
                ];
                
                // Get recent attendance records
                $recentAttendances = DB::table('attendances')
                    ->join('students', 'attendances.student_id', '=', 'students.id')
                    ->join('courses', 'attendances.course_id', '=', 'courses.id')
                    ->whereIn('attendances.course_id', $courseIds)
                    ->where('attendances.marked_by', $user->id)
                    ->select(
                        'attendances.*',
                        'students.first_name',
                        'students.last_name',
                        'students.student_id as student_roll',
                        'courses.course_name',
                        'courses.course_code'
                    )
                    ->orderBy('attendances.date', 'desc')
                    ->limit(10)
                    ->get()
                    ->toArray();
            }
            
            // Get overall system stats
            $systemStats = [
                'total_students' => DB::table('students')->count(),
                'total_courses' => DB::table('courses')->count(),
                'total_teachers' => DB::table('users')->where('role', 'user')->count(),
                'total_departments' => DB::table('departments')->count(),
                'total_attendance_records' => DB::table('attendances')->count(),
            ];
            
            // Get today's attendance summary
            $todayAttendance = DB::table('attendances')
                ->whereDate('date', now()->toDateString())
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get();
            
            $todayTotal = $todayAttendance->sum('count');
            $todayPresent = $todayAttendance->where('status', 'present')->sum('count');
            $todayAttendanceRate = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100, 1) : 0;
            
            // Get departments with student counts
            $departments = DB::table('departments')
                ->leftJoin('students', 'departments.id', '=', 'students.department_id')
                ->select('departments.id', 'departments.name', DB::raw('COUNT(students.id) as student_count'))
                ->groupBy('departments.id', 'departments.name')
                ->get()
                ->toArray();
            
            // Render welcome page with user data and assigned courses
            return Inertia::render('Welcome', [
                'canLogin' => Route::has('login'),
                'canRegister' => Route::has('register'),
                'laravelVersion' => Application::VERSION,
                'phpVersion' => PHP_VERSION,
                'auth' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'department' => $user->department,
                        'level' => $user->level
                    ]
                ],
                'assignedCourses' => $assignedCourses,
                'attendanceStats' => $attendanceStats,
                'recentAttendances' => $recentAttendances ?? [],
                'systemStats' => $systemStats,
                'todayAttendanceRate' => $todayAttendanceRate,
                'departments' => $departments
            ]);
        }
        
        // For guests (not logged in)
        $systemStats = [
            'total_students' => DB::table('students')->count(),
            'total_courses' => DB::table('courses')->count(),
            'total_teachers' => DB::table('users')->where('role', 'user')->count(),
            'total_departments' => DB::table('departments')->count(),
            'total_attendance_records' => DB::table('attendances')->count(),
        ];
        
        $departments = DB::table('departments')
            ->leftJoin('students', 'departments.id', '=', 'students.department_id')
            ->select('departments.id', 'departments.name', DB::raw('COUNT(students.id) as student_count'))
            ->groupBy('departments.id', 'departments.name')
            ->get()
            ->toArray();
        
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'auth' => null,
            'assignedCourses' => [],
            'attendanceStats' => [],
            'recentAttendances' => [],
            'systemStats' => $systemStats,
            'todayAttendanceRate' => 0,
            'departments' => $departments
        ]);
    }
}