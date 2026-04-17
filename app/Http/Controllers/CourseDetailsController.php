<?php

namespace App\Http\Controllers;

use App\Models\CourseDetails;
use App\Models\Attendance;
use App\Models\CourseTeacher;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowAttendanceNotification;
use Carbon\Carbon;
use Inertia\Inertia;

class CourseDetailsController extends Controller
{
    /**
     * Display the course details page with students.
     */
    public function show($courseId)
    {
        try {
            // Get the current logged in user's ID
            $userId = Auth::id();
            
            // First, get the teacher's assigned department, academic stream, level and session for this course from course_teachers table
            $courseTeacher = CourseTeacher::where('course_id', $courseId)
                ->where('user_id', $userId)
                ->first();

            if (!$courseTeacher) {
                Log::warning('No course teacher assignment found for user: ' . $userId . ' and course: ' . $courseId);
                return Inertia::render('CourseDetails', [
                    'courseId' => $courseId,
                    'students' => [],
                    'attendanceRecords' => [],
                    'attendanceRecordsPaginated' => [],
                    'totalStudents' => 0,
                    'courseName' => $this->getCourseName($courseId),
                    'courseCode' => $this->getCourseCode($courseId),
                    'streamName' => $this->getStreamName($courseId),
                    'error' => 'You are not assigned to teach this course'
                ]);
            }

            // Get the department, academic stream, level and session from course_teachers table
            $teacherDepartmentId = $courseTeacher->department_id;
            $teacherAcademicStreamId = $courseTeacher->academic_stream_id;
            $teacherLevel = $courseTeacher->level;
            $teacherSession = $courseTeacher->session;
            
            Log::info('Teacher department: ' . $teacherDepartmentId . ', Academic Stream: ' . $teacherAcademicStreamId . ', Level: ' . $teacherLevel . ', Session: ' . $teacherSession);

            // Get all active students from database that match the teacher's department, academic stream, level AND session
            $students = CourseDetails::active()
                ->where('department_id', $teacherDepartmentId)
                ->where('academic_stream_id', $teacherAcademicStreamId)
                ->where('level', $teacherLevel)
                ->where('session', $teacherSession)
                ->orderBy('student_id', 'asc')
                ->get()
                ->map(function ($student) use ($courseId) {
                    // Calculate attendance statistics
                    $totalClasses = Attendance::forCourse($courseId)
                        ->forStudent($student->id)
                        ->count();
                    
                    $attendedClasses = Attendance::forCourse($courseId)
                        ->forStudent($student->id)
                        ->whereIn('status', ['present', 'late'])
                        ->count();
                    
                    $percentage = $totalClasses > 0 
                        ? round(($attendedClasses / $totalClasses) * 100, 2) 
                        : 0;

                    return [
                        'id' => $student->id,
                        'roll' => $student->student_id,
                        'name' => $student->full_name,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'email' => $student->email,
                        'phone' => $student->phone,
                        'level' => $student->level,
                        'session' => $student->session,
                        'status' => '',
                        'remarks' => '',
                        'percentage' => $percentage,
                        'total_classes' => $totalClasses,
                        'attended_classes' => $attendedClasses
                    ];
                })
                ->values()
                ->toArray();

            // Check if attendance already marked for today
            $today = Carbon::today()->toDateString();
            $existingAttendance = Attendance::forDate($today)
                ->forCourse($courseId)
                ->get()
                ->keyBy('student_id');

            // Populate status if attendance exists
            foreach ($students as &$student) {
                if (isset($existingAttendance[$student['id']])) {
                    $student['status'] = $existingAttendance[$student['id']]->status;
                    $student['remarks'] = $existingAttendance[$student['id']]->remarks;
                }
            }

            // Get the current user's name
            $userName = Auth::user()->name ?? 'Administrator';

            return Inertia::render('CourseDetails', [
                'courseId' => $courseId,
                'students' => $students,
                'attendanceRecords' => [],
                'attendanceRecordsPaginated' => [],
                'totalStudents' => count($students),
                'courseName' => $this->getCourseName($courseId),
                'courseCode' => $this->getCourseCode($courseId),
                'streamName' => $this->getStreamName($courseId),
                'userName' => $userName,
                'teacherDepartmentId' => $teacherDepartmentId,
                'teacherAcademicStreamId' => $teacherAcademicStreamId,
                'teacherLevel' => $teacherLevel,
                'teacherSession' => $teacherSession
            ]);

        } catch (\Exception $e) {
            Log::error('Error in CourseDetailsController@show: ' . $e->getMessage());
            
            return Inertia::render('CourseDetails', [
                'courseId' => $courseId,
                'students' => [],
                'attendanceRecords' => [],
                'attendanceRecordsPaginated' => [],
                'totalStudents' => 0,
                'courseName' => $this->getCourseName($courseId),
                'courseCode' => $this->getCourseCode($courseId),
                'streamName' => $this->getStreamName($courseId),
                'userName' => Auth::user()->name ?? 'Administrator',
                'error' => 'Failed to load students: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get attendance records with filtering and pagination.
     */
    public function getAttendanceRecords(Request $request, $courseId)
    {
        try {
            $perPage = $request->input('per_page', 6);
            $page = $request->input('page', 1);
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $status = $request->input('status');

            $query = Attendance::forCourse($courseId)
                ->with('student');

            // Apply date range filter
            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->whereDate('date', '>=', $startDate);
            } elseif ($endDate) {
                $query->whereDate('date', '<=', $endDate);
            }

            // Get all records and group by date
            $allRecords = $query->orderBy('date', 'desc')->get();
            
            $groupedRecords = $allRecords->groupBy('date')->map(function ($attendances, $date) {
                $presentCount = $attendances->where('status', 'present')->count();
                $absentCount = $attendances->where('status', 'absent')->count();
                $lateCount = $attendances->where('status', 'late')->count();
                
                return [
                    'id' => $attendances->first()->id,
                    'date' => Carbon::parse($date)->format('Y-m-d'),
                    'status' => 'completed',
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount,
                    'total' => $attendances->count()
                ];
            })->values();

            // Apply status filter on grouped records
            if ($status && $status !== 'all') {
                if ($status === 'high_attendance') {
                    $groupedRecords = $groupedRecords->filter(function ($record) {
                        $total = $record['present'] + $record['absent'] + $record['late'];
                        $presentPercentage = $total > 0 ? ($record['present'] / $total) * 100 : 0;
                        return $presentPercentage >= 75;
                    });
                } elseif ($status === 'low_attendance') {
                    $groupedRecords = $groupedRecords->filter(function ($record) {
                        $total = $record['present'] + $record['absent'] + $record['late'];
                        $presentPercentage = $total > 0 ? ($record['present'] / $total) * 100 : 0;
                        return $presentPercentage < 60;
                    });
                } elseif ($status === 'medium_attendance') {
                    $groupedRecords = $groupedRecords->filter(function ($record) {
                        $total = $record['present'] + $record['absent'] + $record['late'];
                        $presentPercentage = $total > 0 ? ($record['present'] / $total) * 100 : 0;
                        return $presentPercentage >= 60 && $presentPercentage < 75;
                    });
                }
            }

            // Apply pagination
            $total = $groupedRecords->count();
            $paginatedRecords = $groupedRecords->slice(($page - 1) * $perPage, $perPage)->values();

            return response()->json([
                'success' => true,
                'data' => $paginatedRecords,
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage)
                ],
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getAttendanceRecords: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get filtered students based on search criteria.
     */
    public function getFilteredStudents(Request $request, $courseId)
    {
        try {
            $search = $request->input('search', '');
            $percentageFilter = $request->input('percentage_filter', 'all');
            
            // Get the current logged in user's ID
            $userId = Auth::id();
            
            // Get the teacher's assigned department, academic stream, level and session for this course
            $courseTeacher = CourseTeacher::where('course_id', $courseId)
                ->where('user_id', $userId)
                ->first();

            if (!$courseTeacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to teach this course'
                ], 403);
            }

            $teacherDepartmentId = $courseTeacher->department_id;
            $teacherAcademicStreamId = $courseTeacher->academic_stream_id;
            $teacherLevel = $courseTeacher->level;
            $teacherSession = $courseTeacher->session;

            // Build query for students
            $studentsQuery = CourseDetails::active()
                ->where('department_id', $teacherDepartmentId)
                ->where('academic_stream_id', $teacherAcademicStreamId)
                ->where('level', $teacherLevel)
                ->where('session', $teacherSession);

            // Apply search filter (by name or student_id)
            if (!empty($search)) {
                $studentsQuery->where(function($q) use ($search) {
                    $q->where('full_name', 'like', '%' . $search . '%')
                      ->orWhere('student_id', 'like', '%' . $search . '%')
                      ->orWhere('first_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            }

            // Get students
            $students = $studentsQuery->orderBy('student_id', 'asc')->get();
            
            // Calculate percentages and apply percentage filter
            $filteredStudents = [];
            
            foreach ($students as $student) {
                // Calculate attendance statistics
                $totalClasses = Attendance::forCourse($courseId)
                    ->forStudent($student->id)
                    ->count();
                
                $attendedClasses = Attendance::forCourse($courseId)
                    ->forStudent($student->id)
                    ->whereIn('status', ['present', 'late'])
                    ->count();
                
                $percentage = $totalClasses > 0 
                    ? round(($attendedClasses / $totalClasses) * 100, 2) 
                    : 0;
                
                // Apply percentage filter
                $includeStudent = true;
                if ($percentageFilter === 'high') {
                    $includeStudent = $percentage >= 75;
                } elseif ($percentageFilter === 'medium') {
                    $includeStudent = $percentage >= 60 && $percentage < 75;
                } elseif ($percentageFilter === 'low') {
                    $includeStudent = $percentage < 60 && $percentage > 0;
                } elseif ($percentageFilter === 'zero') {
                    $includeStudent = $percentage == 0;
                }
                
                if ($includeStudent) {
                    // Check if attendance already marked for today
                    $today = Carbon::today()->toDateString();
                    $existingAttendance = Attendance::forDate($today)
                        ->forCourse($courseId)
                        ->where('student_id', $student->id)
                        ->first();
                    
                    $filteredStudents[] = [
                        'id' => $student->id,
                        'roll' => $student->student_id,
                        'name' => $student->full_name,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'email' => $student->email,
                        'phone' => $student->phone,
                        'level' => $student->level,
                        'session' => $student->session,
                        'status' => $existingAttendance ? $existingAttendance->status : '',
                        'remarks' => $existingAttendance ? $existingAttendance->remarks : '',
                        'percentage' => $percentage,
                        'total_classes' => $totalClasses,
                        'attended_classes' => $attendedClasses
                    ];
                }
            }
            
            // Calculate counts for filtered students
            $presentCount = collect($filteredStudents)->where('status', 'present')->count();
            $absentCount = collect($filteredStudents)->where('status', 'absent')->count();
            $lateCount = collect($filteredStudents)->where('status', 'late')->count();
            
            return response()->json([
                'success' => true,
                'students' => $filteredStudents,
                'counts' => [
                    'total' => count($filteredStudents),
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount
                ],
                'applied_filters' => [
                    'search' => $search,
                    'percentage_filter' => $percentageFilter
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getFilteredStudents: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error filtering students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store attendance for the course.
     */
    public function storeAttendance(Request $request, $courseId)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'date' => 'required|date',
                'attendance' => 'required|array',
                'attendance.*.student_id' => 'required|exists:students,id',
                'attendance.*.status' => 'nullable|in:present,absent,late',
                'attendance.*.remarks' => 'nullable|string|max:255'
            ]);

            $date = $request->date;
            $attendanceData = $request->attendance;
            $updatedPercentages = [];

            foreach ($attendanceData as $data) {
                $studentId = $data['student_id'];
                
                if (!empty($data['status'])) {
                    // Save or update attendance
                    Attendance::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'date' => $date
                        ],
                        [
                            'course_id' => $courseId,
                            'status' => $data['status'],
                            'remarks' => $data['remarks'] ?? null,
                            'marked_by' => Auth::id()
                        ]
                    );
                    
                    Log::info('Attendance saved for student: ' . $studentId . ' with status: ' . $data['status']);
                } else {
                    // If status is empty, delete existing attendance record
                    Attendance::where('student_id', $studentId)
                        ->where('date', $date)
                        ->delete();
                        
                    Log::info('Attendance deleted for student: ' . $studentId);
                }

                // Calculate updated percentage for this student
                $totalClasses = Attendance::forCourse($courseId)
                    ->forStudent($studentId)
                    ->count();
                
                $attendedClasses = Attendance::forCourse($courseId)
                    ->forStudent($studentId)
                    ->whereIn('status', ['present', 'late'])
                    ->count();
                
                $percentage = $totalClasses > 0 
                    ? round(($attendedClasses / $totalClasses) * 100, 2) 
                    : 0;

                // Update all attendance records for this student with new percentage
                Attendance::forCourse($courseId)
                    ->forStudent($studentId)
                    ->update(['percentage' => $percentage]);

                $updatedPercentages[$studentId] = [
                    'percentage' => $percentage,
                    'total_classes' => $totalClasses,
                    'attended_classes' => $attendedClasses
                ];
            }

            DB::commit();

            // Calculate counts for response
            $presentCount = collect($attendanceData)->where('status', 'present')->count();
            $absentCount = collect($attendanceData)->where('status', 'absent')->count();
            $lateCount = collect($attendanceData)->where('status', 'late')->count();

            return response()->json([
                'success' => true,
                'message' => 'Attendance saved successfully!',
                'data' => [
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount,
                    'total' => count($attendanceData)
                ],
                'percentages' => $updatedPercentages
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in storeAttendance: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error saving attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance report for a specific date.
     */
    public function getAttendanceReport(Request $request, $courseId)
    {
        try {
            $request->validate([
                'date' => 'required|date'
            ]);

            $date = $request->date;

            // Get the current logged in user's ID
            $userId = Auth::id();
            
            // Get the teacher's assigned department, academic stream, level and session for this course
            $courseTeacher = CourseTeacher::where('course_id', $courseId)
                ->where('user_id', $userId)
                ->first();

            if (!$courseTeacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to teach this course'
                ], 403);
            }

            $teacherDepartmentId = $courseTeacher->department_id;
            $teacherAcademicStreamId = $courseTeacher->academic_stream_id;
            $teacherLevel = $courseTeacher->level;
            $teacherSession = $courseTeacher->session;

            $attendance = Attendance::forDate($date)
                ->forCourse($courseId)
                ->with('student')
                ->get();

            // Get students count for this department, academic stream, level and session only
            $totalStudents = CourseDetails::active()
                ->where('department_id', $teacherDepartmentId)
                ->where('academic_stream_id', $teacherAcademicStreamId)
                ->where('level', $teacherLevel)
                ->where('session', $teacherSession)
                ->count();

            // Get percentages for students of this department, academic stream, level and session only
            $students = CourseDetails::active()
                ->where('department_id', $teacherDepartmentId)
                ->where('academic_stream_id', $teacherAcademicStreamId)
                ->where('level', $teacherLevel)
                ->where('session', $teacherSession)
                ->get();
            
            $studentPercentages = [];
            
            foreach ($students as $student) {
                $totalClasses = Attendance::forCourse($courseId)
                    ->forStudent($student->id)
                    ->count();
                
                $attendedClasses = Attendance::forCourse($courseId)
                    ->forStudent($student->id)
                    ->whereIn('status', ['present', 'late'])
                    ->count();
                
                $studentPercentages[$student->id] = [
                    'percentage' => $totalClasses > 0 ? round(($attendedClasses / $totalClasses) * 100, 2) : 0,
                    'total_classes' => $totalClasses,
                    'attended' => $attendedClasses
                ];
            }

            $report = [
                'date' => $date,
                'total_students' => $totalStudents,
                'present' => $attendance->where('status', 'present')->count(),
                'absent' => $attendance->where('status', 'absent')->count(),
                'late' => $attendance->where('status', 'late')->count(),
                'details' => $attendance->map(function ($a) {
                    return [
                        'student_id' => $a->student->student_id,
                        'name' => $a->student->full_name,
                        'status' => $a->status,
                        'remarks' => $a->remarks,
                        'percentage' => $a->percentage,
                        'total_classes' => $a->total_classes,
                        'attended_classes' => $a->attended_classes
                    ];
                }),
                'student_percentages' => $studentPercentages
            ];

            // Return as Inertia response with the report data
            return Inertia::render('CourseDetails', [
                'attendanceData' => $report,
                'courseId' => $courseId,
                'students' => [],
                'attendanceRecords' => [],
                'attendanceRecordsPaginated' => [],
                'totalStudents' => 0,
                'courseName' => $this->getCourseName($courseId),
                'courseCode' => $this->getCourseCode($courseId),
                'streamName' => $this->getStreamName($courseId),
                'userName' => Auth::user()->name ?? 'Administrator'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getAttendanceReport: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance report for a date range.
     */
    public function getRangeReport(Request $request, $courseId)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            $startDate = $request->start_date;
            $endDate = $request->end_date;

            // Get the current logged in user's ID
            $userId = Auth::id();
            
            // Get the teacher's assigned department, academic stream, level and session for this course
            $courseTeacher = CourseTeacher::where('course_id', $courseId)
                ->where('user_id', $userId)
                ->first();

            if (!$courseTeacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to teach this course'
                ], 403);
            }

            $teacherDepartmentId = $courseTeacher->department_id;
            $teacherAcademicStreamId = $courseTeacher->academic_stream_id;
            $teacherLevel = $courseTeacher->level;
            $teacherSession = $courseTeacher->session;

            // Get all attendance records in the date range
            $attendances = Attendance::where('course_id', $courseId)
                ->whereBetween('date', [$startDate, $endDate])
                ->with('student')
                ->get();

            // Get all students in this department, academic stream, level and session
            $students = CourseDetails::active()
                ->where('department_id', $teacherDepartmentId)
                ->where('academic_stream_id', $teacherAcademicStreamId)
                ->where('level', $teacherLevel)
                ->where('session', $teacherSession)
                ->get();

            // Calculate statistics
            $totalDays = $attendances->groupBy('date')->count();
            $presentCount = $attendances->where('status', 'present')->count();
            $absentCount = $attendances->where('status', 'absent')->count();
            $lateCount = $attendances->where('status', 'late')->count();
            $totalRecords = $attendances->count();

            $averagePresent = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0;
            $averageAbsent = $totalRecords > 0 ? round(($absentCount / $totalRecords) * 100, 1) : 0;
            $averageLate = $totalRecords > 0 ? round(($lateCount / $totalRecords) * 100, 1) : 0;

            // Prepare student data with their attendance for this range
            $studentData = $students->map(function($student) use ($attendances) {
                $studentAttendances = $attendances->where('student_id', $student->id);
                $present = $studentAttendances->where('status', 'present')->count();
                $absent = $studentAttendances->where('status', 'absent')->count();
                $late = $studentAttendances->where('status', 'late')->count();
                $total = $studentAttendances->count();

                // Calculate overall attendance percentage
                $totalClasses = Attendance::forCourse($student->course_id ?? 0)
                    ->forStudent($student->id)
                    ->count();

                $attendedClasses = Attendance::forCourse($student->course_id ?? 0)
                    ->forStudent($student->id)
                    ->whereIn('status', ['present', 'late'])
                    ->count();

                $percentage = $totalClasses > 0 ? round(($attendedClasses / $totalClasses) * 100, 1) : 0;

                return [
                    'id' => $student->id,
                    'roll' => $student->student_id,
                    'name' => $student->full_name,
                    'email' => $student->email,
                    'level' => $student->level,
                    'session' => $student->session,
                    'status' => '',
                    'remarks' => '',
                    'percentage' => $percentage,
                    'present_count' => $present,
                    'absent_count' => $absent,
                    'late_count' => $late,
                    'total_classes' => $total,
                    'attended_classes' => $attendedClasses
                ];
            })->values()->toArray();

            $summary = [
                'totalDays' => $totalDays,
                'totalRecords' => $totalRecords,
                'averagePresent' => $averagePresent,
                'averageAbsent' => $averageAbsent,
                'averageLate' => $averageLate,
                'startDate' => $startDate,
                'endDate' => $endDate
            ];

            return response()->json([
                'success' => true,
                'rangeData' => [
                    'students' => $studentData,
                    'summary' => $summary
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getRangeReport: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating range report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send email to a specific student about low attendance.
     */
    public function sendEmailToStudent(Request $request, $courseId)
    {
        try {
            $request->validate([
                'student_id' => 'required',
                'student_email' => 'required|email',
                'student_name' => 'required',
                'attendance_percentage' => 'required|numeric',
                'course_name' => 'nullable',
                'course_code' => 'nullable'
            ]);

            $course = Course::find($courseId);
            
            if (!$course) {
                $course = (object) [
                    'course_name' => $request->course_name ?? $this->getCourseName($courseId),
                    'course_code' => $request->course_code ?? $this->getCourseCode($courseId)
                ];
            }

            // Get student details
            $student = CourseDetails::find($request->student_id);
            
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            // Prepare email data
            $emailData = [
                'student_name' => $request->student_name,
                'student_email' => $request->student_email,
                'attendance_percentage' => $request->attendance_percentage,
                'course_name' => $request->course_name ?? ($course->course_name ?? 'Course'),
                'course_code' => $request->course_code ?? ($course->course_code ?? 'N/A'),
                'course_id' => $courseId,
                'threshold' => 60,
                'current_date' => Carbon::now()->format('F j, Y')
            ];

            // Send email
            Mail::to($request->student_email)->send(new LowAttendanceNotification($emailData));

            // Log the email sent
            Log::info('Low attendance email sent to: ' . $request->student_email . ' for student: ' . $request->student_name);

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully',
                'sent_to' => $request->student_email
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send bulk emails to multiple students with low attendance.
     */
    public function sendBulkEmail(Request $request, $courseId)
    {
        try {
            $request->validate([
                'students' => 'required|array',
                'students.*.id' => 'required',
                'students.*.email' => 'required|email',
                'students.*.name' => 'required',
                'students.*.percentage' => 'required|numeric',
                'course_name' => 'nullable',
                'course_code' => 'nullable'
            ]);

            $course = Course::find($courseId);
            
            if (!$course) {
                $course = (object) [
                    'course_name' => $request->course_name ?? $this->getCourseName($courseId),
                    'course_code' => $request->course_code ?? $this->getCourseCode($courseId)
                ];
            }

            $students = $request->students;
            
            $sentCount = 0;
            $failedCount = 0;
            $failedEmails = [];

            foreach ($students as $studentData) {
                try {
                    // Prepare email data
                    $emailData = [
                        'student_name' => $studentData['name'],
                        'student_email' => $studentData['email'],
                        'attendance_percentage' => $studentData['percentage'],
                        'course_name' => $request->course_name ?? ($course->course_name ?? 'Course'),
                        'course_code' => $request->course_code ?? ($course->course_code ?? 'N/A'),
                        'course_id' => $courseId,
                        'threshold' => 60,
                        'current_date' => Carbon::now()->format('F j, Y')
                    ];

                    // Send email
                    Mail::to($studentData['email'])->send(new LowAttendanceNotification($emailData));
                    
                    $sentCount++;
                    
                    // Log success
                    Log::info('Bulk email sent to: ' . $studentData['email']);
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    $failedEmails[] = $studentData['email'];
                    Log::error('Failed to send bulk email to ' . $studentData['email'] . ': ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk emails processed',
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'failed_emails' => $failedEmails
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process bulk emails: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk emails',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * View a specific attendance record.
     */
    public function viewRecord($courseId, $recordId)
    {
        try {
            // First, get the specific attendance record to find its date
            $attendanceRecord = Attendance::where('course_id', $courseId)
                ->where('id', $recordId)
                ->first();

            if (!$attendanceRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found'
                ], 404);
            }

            // Get the current logged in user's ID
            $userId = Auth::id();
            
            // Get the teacher's assigned department, academic stream, level and session for this course
            $courseTeacher = CourseTeacher::where('course_id', $courseId)
                ->where('user_id', $userId)
                ->first();

            if (!$courseTeacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to teach this course'
                ], 403);
            }

            $teacherDepartmentId = $courseTeacher->department_id;
            $teacherAcademicStreamId = $courseTeacher->academic_stream_id;
            $teacherLevel = $courseTeacher->level;
            $teacherSession = $courseTeacher->session;

            // Get all attendance records for the same date and course
            $attendanceRecords = Attendance::with('student')
                ->where('course_id', $courseId)
                ->where('date', $attendanceRecord->date)
                ->get();

            // Get all students that should be in this course (based on teacher's criteria)
            $allStudents = CourseDetails::active()
                ->where('department_id', $teacherDepartmentId)
                ->where('academic_stream_id', $teacherAcademicStreamId)
                ->where('level', $teacherLevel)
                ->where('session', $teacherSession)
                ->orderBy('student_id', 'asc')
                ->get();

            // Create a map of attendance statuses
            $attendanceMap = [];
            foreach ($attendanceRecords as $attendance) {
                $attendanceMap[$attendance->student_id] = $attendance;
            }

            // Prepare details for all students
            $details = [];
            $presentCount = 0;
            $absentCount = 0;
            $lateCount = 0;

            foreach ($allStudents as $student) {
                $attendance = $attendanceMap[$student->id] ?? null;
                $status = $attendance ? $attendance->status : '';
                $remarks = $attendance ? $attendance->remarks : '';
                
                // Count statuses
                if ($status === 'present') {
                    $presentCount++;
                } elseif ($status === 'absent') {
                    $absentCount++;
                } elseif ($status === 'late') {
                    $lateCount++;
                }
                
                $details[] = [
                    'student_id' => $student->student_id,
                    'name' => $student->full_name,
                    'level' => $student->level ?? 'N/A',
                    'session' => $student->session ?? 'N/A',
                    'status' => $status,
                    'remarks' => $remarks
                ];
            }

            return response()->json([
                'success' => true,
                'record' => [
                    'id' => $recordId,
                    'date' => $attendanceRecord->date,
                    'details' => $details,
                    'total_students' => count($details),
                    'present_count' => $presentCount,
                    'absent_count' => $absentCount,
                    'late_count' => $lateCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in viewRecord: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error viewing record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print a specific attendance record.
     */
    public function printRecord($courseId, $recordId)
    {
        try {
            $attendance = Attendance::with('student')
                ->where('course_id', $courseId)
                ->where('id', $recordId)
                ->get();

            $details = $attendance->map(function($a) {
                return [
                    'student_id' => $a->student->student_id ?? $a->student_id,
                    'student_name' => $a->student->full_name ?? 'N/A',
                    'status' => $a->status,
                    'remarks' => $a->remarks
                ];
            });

            return response()->json([
                'success' => true,
                'recordData' => [
                    'details' => $details
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in printRecord: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error printing record: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getCourseName($courseId)
    {
        $courses = [
            '1' => 'Introduction to Computer Science',
            '2' => 'Data Structures',
            '3' => 'Database Management Systems',
            '4' => 'Web Development',
            '5' => 'Software Engineering',
            '14' => 'Thesis',
            '15' => 'Project'
        ];

        return $courses[$courseId] ?? 'Course ' . $courseId;
    }

    private function getCourseCode($courseId)
    {
        $codes = [
            '1' => 'CSE-101',
            '2' => 'CSE-201',
            '3' => 'CSE-301',
            '4' => 'CSE-401',
            '5' => 'CSE-501',
            '14' => 'CSE-4202',
            '15' => 'CSE-4203'
        ];

        return $codes[$courseId] ?? 'CSE-' . $courseId;
    }

    private function getStreamName($courseId)
    {
        // Get stream name from courses table
        try {
            $course = Course::find($courseId);
            if ($course && $course->stream_name) {
                return $course->stream_name;
            }
        } catch (\Exception $e) {
            Log::warning('Could not get stream name for course: ' . $courseId);
        }

        // Default stream names based on course ID
        $streams = [
            '1' => 'Day',
            '2' => 'Day',
            '3' => 'Day',
            '4' => 'Evening',
            '5' => 'Evening',
            '14' => 'Day',
            '15' => 'Day'
        ];

        return $streams[$courseId] ?? 'Not specified';
    }
}