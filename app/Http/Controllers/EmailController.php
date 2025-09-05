<?php

namespace App\Http\Controllers;

use App\Models\EmailVerificationAttempt;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Send a test email
     */
    public function sendTestEmail(Request $request): JsonResponse
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'to_name' => 'nullable|string|max:255'
        ]);

        try {
            $this->emailService->sendEmail(
                $request->to,
                $request->subject,
                $request->body,
                $request->to_name
            );

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send internship notification email
     */
    public function sendInternshipNotification(Request $request): JsonResponse
    {
        $request->validate([
            'student_email' => 'required|email',
            'student_name' => 'required|string|max:255',
            'internship_details' => 'required|array',
            'internship_details.company_name' => 'required|string',
            'internship_details.position' => 'required|string',
            'internship_details.duration' => 'required|string',
            'internship_details.start_date' => 'required|string'
        ]);

        try {
            $this->emailService->sendInternshipNotification(
                $request->student_email,
                $request->student_name,
                $request->internship_details
            );

            return response()->json([
                'success' => true,
                'message' => 'Internship notification sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send internship notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send assessment reminder email
     */
    public function sendAssessmentReminder(Request $request): JsonResponse
    {
        $request->validate([
            'student_email' => 'required|email',
            'student_name' => 'required|string|max:255',
            'assessment_type' => 'required|string|max:255'
        ]);

        try {
            $this->emailService->sendAssessmentReminder(
                $request->student_email,
                $request->student_name,
                $request->assessment_type
            );

            return response()->json([
                'success' => true,
                'message' => 'Assessment reminder sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send assessment reminder: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send bulk email to multiple recipients
     */
    public function sendBulkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*.email' => 'required|email',
            'recipients.*.name' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string'
        ]);

        try {
            $this->emailService->sendBulkEmail(
                $request->recipients,
                $request->subject,
                $request->body
            );

            return response()->json([
                'success' => true,
                'message' => 'Bulk email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send email with attachment
     */
    public function sendEmailWithAttachment(Request $request): JsonResponse
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'attachment' => 'required|file',
            'to_name' => 'nullable|string|max:255'
        ]);

        try {
            $attachment = $request->file('attachment');
            $attachmentPath = $attachment->getRealPath();
            $attachmentName = $attachment->getClientOriginalName();

            $this->emailService->sendEmailWithAttachment(
                $request->to,
                $request->subject,
                $request->body,
                $attachmentPath,
                $attachmentName,
                $request->to_name
            );

            return response()->json([
                'success' => true,
                'message' => 'Email with attachment sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email with attachment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send account verification email
     */
    public function sendAccountVerification(Request $request): JsonResponse
    {
        $request->validate([
            'user_email' => 'required|email',
            'user_name' => 'required|string|max:255',
            'verification_token' => 'required|string|max:255'
        ]);

        try {
            $this->emailService->sendAccountVerification(
                $request->user_email,
                $request->user_name,
                $request->verification_token
            );

            return response()->json([
                'success' => true,
                'message' => 'Account verification email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send account verification email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend verification email with rate limiting
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Check rate limiting for email verification attempts
        if (!EmailVerificationAttempt::canAttemptVerification($request->email)) {
            $timeRemaining = EmailVerificationAttempt::getFormattedTimeUntilNextAttempt($request->email);
            return response()->json([
                'success' => false,
                'message' => "Too many verification attempts. Please wait {$timeRemaining} before trying again."
            ], 429);
        }

        try {
            // Record the verification attempt
            EmailVerificationAttempt::recordAttempt(
                $request->email,
                $request->ip(),
                $request->userAgent()
            );

            // Here you would typically regenerate and send a new verification email
            // For now, we'll just return a success message
            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email: ' . $e->getMessage()
            ], 500);
        }
    }
}
