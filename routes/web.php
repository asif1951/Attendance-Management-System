<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassArmController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\CourseTeacherController;
use App\Http\Controllers\CourseDetailsController;
use App\Http\Controllers\DashboardController;

// Main route with role-based redirection - Use WelcomeController
Route::get('/', [WelcomeController::class, 'index']);

// Dashboard route - only for admin
Route::get('/dashboard', function () {
    $user = auth()->user();

    // Check if user is admin
    if ($user->role !== 'admin') {
        return redirect('/');
    }

    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Create Department
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
});

// Course Routes - Fixed
Route::middleware(['auth', 'verified'])->group(function () {
    // Main course page
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');

    // Create page (if needed separately)
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');

    // CRUD operations
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
});


Route::middleware(['auth'])->group(function () {
    // Courses
    Route::resource('courses', CourseController::class);

    // AJAX route for getting streams by department
    Route::get('/departments/{department}/streams', [CourseController::class, 'getStreamsByDepartment'])
        ->name('departments.streams');
});
// Add Student (Original route)
Route::get('/students/create', function () {
    return Inertia::render('AddStudent');
})->name('students.create');

// Student Management Routes
Route::middleware(['auth'])->group(function () {
    // Student listing page
    Route::get('/students', function () {
        return Inertia::render('students.index');
    })->name('students.index');

    Route::prefix('students')->group(function () {
        // Get all departments for dropdown
        Route::get('/departments', [StudentController::class, 'getDepartments'])->name('students.departments');

        // Get streams by department
        Route::get('/departments/{department}/streams', [StudentController::class, 'getStreamsByDepartment'])->name('students.streams.by.department');

        // Check email uniqueness
        Route::post('/check-email', [StudentController::class, 'checkEmail'])->name('students.check.email');

        // Store new student
        Route::post('/store', [StudentController::class, 'store'])->name('students.store');

        // Get student details
        Route::get('/{student}', [StudentController::class, 'show'])->name('students.show');

        // Update student
        Route::put('/{student}', [StudentController::class, 'update'])->name('students.update');

        // Delete student
        Route::delete('/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    });
});

// Class Arms
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/class-arms/create', [ClassArmController::class, 'create'])->name('class-arms.create');
    Route::post('/class-arms', [ClassArmController::class, 'store'])->name('class-arms.store');
    Route::put('/class-arms/{id}/status', [ClassArmController::class, 'updateStatus'])->name('class-arms.update-status');
});

// Student Management Routes - Additional routes as per your request
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

    // Additional routes for dropdowns
    Route::get('/students/departments', [StudentController::class, 'getDepartments'])->name('students.departments');
    Route::get('/students/department/{department}/streams', [StudentController::class, 'getStreamsByDepartment'])->name('students.streams.by.department');
    Route::post('/students/check-email', [StudentController::class, 'checkEmail'])->name('students.check.email');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Course Teacher Routes
    Route::get('/course-teachers/create', function () {
        return Inertia::render('CourseTeacher');
    })->name('course-teachers.create');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('course-teachers', CourseTeacherController::class);

    // Additional routes
    Route::get('/get-courses-by-filters', [CourseTeacherController::class, 'getCoursesByFilters']);
    Route::get('/get-email-by-user/{userId}', [CourseTeacherController::class, 'getEmailByUser']);
});

// Redirect after login based on role
Route::get('/redirect-after-login', function () {
    $user = auth()->user();

    if ($user->role === 'admin') {
        return redirect()->route('dashboard');
    }

    // For regular users, redirect to home page (root URL)
    return redirect('/');
})->middleware(['auth'])->name('redirect.after.login');

// Class Arms
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/class-arms/create', [ClassArmController::class, 'create'])->name('class-arms.create');
    Route::post('/class-arms', [ClassArmController::class, 'store'])->name('class-arms.store');
    Route::put('/class-arms/{id}/status', [ClassArmController::class, 'updateStatus'])->name('class-arms.update-status');
});


Route::get('/course-attendance/{id}', [CourseController::class, 'showAttendance'])
    ->middleware(['auth', 'verified'])
    ->name('course.attendance');


Route::get('/course/{courseId}/details', [CourseDetailsController::class, 'show'])->name('course.details');
Route::post('/course/{courseId}/attendance', [CourseDetailsController::class, 'storeAttendance'])->name('course.attendance.store');
Route::get('/course/{courseId}/attendance/report', [CourseDetailsController::class, 'getAttendanceReport'])->name('course.attendance.report');

//Email
// Add these routes inside your existing routes
Route::middleware(['auth'])->group(function () {
    // Existing routes...

    // Course details route
    Route::get('/course/{course}/details', [CourseDetailsController::class, 'show'])->name('course.details');

    // Attendance routes
    Route::post('/course/{course}/attendance', [CourseDetailsController::class, 'storeAttendance'])->name('course.attendance.store');
    Route::get('/course/{course}/attendance/report', [CourseDetailsController::class, 'getAttendanceReport'])->name('course.attendance.report');

    // Email routes for low attendance notifications - NOW POINTING TO CourseDetailsController
    Route::post('/course/{course}/send-email', [CourseDetailsController::class, 'sendEmailToStudent'])->name('course.send-email');
    Route::post('/course/{course}/send-bulk-email', [CourseDetailsController::class, 'sendBulkEmail'])->name('course.send-bulk-email');

    // Date range report route
    Route::get('/course/{course}/attendance/range-report', [CourseDetailsController::class, 'getRangeReport'])->name('course.attendance.range-report');

    // Attendance record routes
    Route::get('/course/{course}/attendance/record/{record}', [CourseDetailsController::class, 'viewRecord'])->name('course.attendance.record');
    Route::get('/course/{course}/attendance/record/{record}/print', [CourseDetailsController::class, 'printRecord'])->name('course.attendance.record.print');
});


Route::get('/course/{courseId}/attendance-records', [CourseDetailsController::class, 'getAttendanceRecords']);
Route::get('/course/{courseId}/filtered-students', [CourseDetailsController::class, 'getFilteredStudents']);


//User Page
Route::get('/user', function () {
    return Inertia::render('User');
})->middleware(['auth'])->name('user');

use App\Http\Controllers\UserController;

// User Management Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/user', [UserController::class, 'index'])->name('user.index');
    Route::post('/user', [UserController::class, 'store'])->name('user.store');
    Route::put('/user/{user}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');
});

// Student Management Routes
Route::middleware(['auth'])->group(function () {
    // Student listing page with data
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');

    // CRUD operations
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

    // Additional routes for dropdowns and checks
    Route::get('/students/departments', [StudentController::class, 'getDepartments'])->name('students.departments');
    Route::get('/students/department/{department}/streams', [StudentController::class, 'getStreamsByDepartment'])->name('students.streams.by.department');
    Route::post('/students/check-email', [StudentController::class, 'checkEmail'])->name('students.check.email');
    Route::post('/students/check-phone', [StudentController::class, 'checkPhone'])->name('students.check.phone');
    Route::post('/students/check-student-id', [StudentController::class, 'checkStudentId'])->name('students.check.student_id');

    // Get paginated list of students
    Route::get('/students/list/data', [StudentController::class, 'getStudentsList'])->name('students.list');
});
// Add this route inside your student routes group
Route::get('/students/all-streams', [StudentController::class, 'getAllStreams'])->name('students.all.streams');


Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');


// API Routes for Welcome Page - Real-time data fetching
Route::middleware(['auth'])->group(function () {
    // Get assigned courses with details
    Route::get('/api/user/courses', [WelcomeController::class, 'getUserCourses'])->name('api.user.courses');

    // Get attendance statistics
    Route::get('/api/user/attendance-stats', [WelcomeController::class, 'getAttendanceStatistics'])->name('api.user.attendance.stats');

    // Get recent attendance records
    Route::get('/api/user/recent-attendances', [WelcomeController::class, 'getRecentAttendances'])->name('api.user.recent.attendances');

    // Get system statistics
    Route::get('/api/system/stats', [WelcomeController::class, 'getSystemStats'])->name('api.system.stats');

    // Get departments with student counts
    Route::get('/api/departments/stats', [WelcomeController::class, 'getDepartmentsWithStats'])->name('api.departments.stats');

    // Get today's attendance rate
    Route::get('/api/today/attendance-rate', [WelcomeController::class, 'getTodayAttendanceRate'])->name('api.today.attendance.rate');
});

// Optional: Public stats for guests (non-authenticated users)
Route::get('/api/public/stats', [WelcomeController::class, 'getPublicStats'])->name('api.public.stats');


// Dashboard route - only for admin
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::get('/course-details/{courseId}', function ($courseId) {
    return Inertia::render('CourseDetails', [
        'courseId' => $courseId
    ]);
})->name('course.details');
require __DIR__ . '/auth.php';
