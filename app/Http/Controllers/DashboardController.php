<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Department;
use App\Models\AcademicStream;
use App\Models\CourseTeacher;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Get all statistics from database
        $totalStudents = Student::count();
        $totalCourses = Course::count();
        $totalDepartments = Department::count();
        $totalAcademicStreams = AcademicStream::count();
        $totalCourseTeachers = CourseTeacher::count();
        $totalUsers = User::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalRegularUsers = User::where('role', 'user')->count();
        
        // Get attendance statistics
        $totalAttendances = Attendance::count();
        $presentCount = Attendance::where('status', 'present')->count();
        $absentCount = Attendance::where('status', 'absent')->count();
        $lateCount = Attendance::where('status', 'late')->count();
        
        // Calculate attendance percentage
        $attendancePercentage = $totalAttendances > 0 
            ? round(($presentCount / $totalAttendances) * 100, 2) 
            : 0;
        
        // Get recent students (last 5)
        $recentStudents = Student::with(['department', 'academicStream'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_id' => $student->student_id,
                    'email' => $student->email,
                    'phone' => $student->phone,
                    'level' => $student->level,
                    'session' => $student->session,
                    'department_name' => $student->department ? $student->department->name : 'N/A',
                    'stream_name' => $student->academicStream ? $student->academicStream->stream_name : 'N/A',
                    'created_at' => $student->created_at,
                ];
            });
        
        // Get recent courses (last 5)
        $recentCourses = Course::with(['department', 'academicStream'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'course_name' => $course->course_name,
                    'course_code' => $course->course_code,
                    'level' => $course->level,
                    'department_name' => $course->department ? $course->department->name : 'N/A',
                    'stream_name' => $course->academicStream ? $course->academicStream->stream_name : 'N/A',
                    'created_at' => $course->created_at,
                ];
            });
        
        // Get department-wise student count
        $departmentStats = Department::withCount('students')
            ->get()
            ->map(function ($dept) {
                return [
                    'name' => $dept->name,
                    'total_students' => $dept->students_count,
                ];
            });
        
        // Get level-wise student distribution
        $levelStats = Student::selectRaw('level, COUNT(*) as total')
            ->groupBy('level')
            ->get()
            ->map(function ($item) {
                return [
                    'level' => $item->level,
                    'total' => $item->total,
                ];
            });
        
        // Get session-wise student distribution
        $sessionStats = Student::selectRaw('session, COUNT(*) as total')
            ->groupBy('session')
            ->get()
            ->map(function ($item) {
                return [
                    'session' => $item->session ?: 'Not Set',
                    'total' => $item->total,
                ];
            });
        
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_students' => $totalStudents,
                'total_courses' => $totalCourses,
                'total_departments' => $totalDepartments,
                'total_academic_streams' => $totalAcademicStreams,
                'total_course_teachers' => $totalCourseTeachers,
                'total_users' => $totalUsers,
                'total_admins' => $totalAdmins,
                'total_regular_users' => $totalRegularUsers,
                'total_attendances' => $totalAttendances,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'late_count' => $lateCount,
                'attendance_percentage' => $attendancePercentage,
            ],
            'recent_students' => $recentStudents,
            'recent_courses' => $recentCourses,
            'department_stats' => $departmentStats,
            'level_stats' => $levelStats,
            'session_stats' => $sessionStats,
        ]);
    }
}