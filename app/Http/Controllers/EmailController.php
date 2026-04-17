<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\LowAttendanceNotification;
use Illuminate\Support\Facades\Queue;

class EmailController extends Controller
{
    /**
     * Send email to a specific student about low attendance
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

            $course = Course::findOrFail($courseId);
            
            // Get student details
            $student = Student::find($request->student_id);
            
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
                'student_id' => $request->student_id,
                'attendance_percentage' => round($request->attendance_percentage, 2),
                'course_name' => $request->course_name ?? $course->course_name,
                'course_code' => $request->course_code ?? $course->course_code,
                'course_id' => $courseId,
                'threshold' => 60,
                'current_date' => now()->format('F j, Y')
            ];

            // Send email using queue for faster response
            Mail::to($request->student_email)->queue(new LowAttendanceNotification($emailData));

            // Log the email sent - FIXED: Using proper Log facade
            Log::info('Low attendance email queued successfully', [
                'student_email' => $request->student_email,
                'student_name' => $request->student_name,
                'course_id' => $courseId,
                'percentage' => $request->attendance_percentage,
                'timestamp' => now()->toDateTimeString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email queued successfully',
                'sent_to' => $request->student_email
            ]);

        } catch (\Exception $e) {
            // FIXED: Using proper Log facade with context
            Log::error('Failed to queue email', [
                'error' => $e->getMessage(),
                'student_email' => $request->student_email ?? 'unknown',
                'course_id' => $courseId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send bulk emails to multiple students - FAST with queuing
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

            $course = Course::findOrFail($courseId);
            $students = $request->students;
            
            $queuedCount = 0;
            $failedCount = 0;
            $failedEmails = [];

            // Process all emails - using queue for speed
            foreach ($students as $studentData) {
                try {
                    // Prepare email data
                    $emailData = [
                        'student_name' => $studentData['name'],
                        'student_email' => $studentData['email'],
                        'student_id' => $studentData['id'],
                        'attendance_percentage' => round($studentData['percentage'], 2),
                        'course_name' => $request->course_name ?? $course->course_name,
                        'course_code' => $request->course_code ?? $course->course_code,
                        'course_id' => $courseId,
                        'threshold' => 60,
                        'current_date' => now()->format('F j, Y')
                    ];

                    // Queue email instead of sending immediately
                    Mail::to($studentData['email'])->queue(new LowAttendanceNotification($emailData));
                    
                    $queuedCount++;
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    $failedEmails[] = $studentData['email'];
                    
                    // Log individual failure
                    Log::error('Failed to queue email for student', [
                        'student_email' => $studentData['email'],
                        'student_name' => $studentData['name'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Log summary - FIXED: Using proper Log facade
            Log::info('Bulk email queue completed', [
                'course_id' => $courseId,
                'total_students' => count($students),
                'queued_count' => $queuedCount,
                'failed_count' => $failedCount,
                'timestamp' => now()->toDateTimeString()
            ]);

            $message = $queuedCount . ' emails queued successfully';
            if ($failedCount > 0) {
                $message .= ', ' . $failedCount . ' failed';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'queued_count' => $queuedCount,
                'failed_count' => $failedCount,
                'failed_emails' => $failedEmails
            ]);

        } catch (\Exception $e) {
            // FIXED: Using proper Log facade
            Log::error('Failed to process bulk emails', [
                'error' => $e->getMessage(),
                'course_id' => $courseId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk emails: ' . $e->getMessage()
            ], 500);
        }
    }
}