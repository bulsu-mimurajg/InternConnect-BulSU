import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

interface EmailTestProps {
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
}

export default function EmailTest({ auth }: EmailTestProps) {
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);
    const [activeTab, setActiveTab] = useState('test');

    // Test email form state
    const [testEmail, setTestEmail] = useState({
        to: '',
        subject: 'Test Email from BULSU InternConnect',
        body: '<h1>Test Email</h1><p>This is a test email from the BULSU InternConnect system.</p>',
        to_name: ''
    });

    // Internship notification form state
    const [internshipNotification, setInternshipNotification] = useState({
        student_email: '',
        student_name: '',
        company_name: '',
        position: '',
        duration: '',
        start_date: ''
    });

    // Assessment reminder form state
    const [assessmentReminder, setAssessmentReminder] = useState({
        student_email: '',
        student_name: '',
        assessment_type: 'Self-Assessment'
    });

    const showMessage = (type: 'success' | 'error', text: string) => {
        setMessage({ type, text });
        setTimeout(() => setMessage(null), 5000);
    };

    const sendTestEmail = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setMessage(null);

        try {
            const response = await fetch('/email/send-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(testEmail)
            });

            const data = await response.json();

            if (data.success) {
                showMessage('success', data.message);
            } else {
                showMessage('error', data.message);
            }
        } catch (error) {
            showMessage('error', 'Failed to send test email');
        } finally {
            setLoading(false);
        }
    };

    const sendInternshipNotification = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setMessage(null);

        try {
            const response = await fetch('/email/internship-notification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    student_email: internshipNotification.student_email,
                    student_name: internshipNotification.student_name,
                    internship_details: {
                        company_name: internshipNotification.company_name,
                        position: internshipNotification.position,
                        duration: internshipNotification.duration,
                        start_date: internshipNotification.start_date
                    }
                })
            });

            const data = await response.json();

            if (data.success) {
                showMessage('success', data.message);
            } else {
                showMessage('error', data.message);
            }
        } catch (error) {
            showMessage('error', 'Failed to send internship notification');
        } finally {
            setLoading(false);
        }
    };

    const sendAssessmentReminder = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setMessage(null);

        try {
            const response = await fetch('/email/assessment-reminder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(assessmentReminder)
            });

            const data = await response.json();

            if (data.success) {
                showMessage('success', data.message);
            } else {
                showMessage('error', data.message);
            }
        } catch (error) {
            showMessage('error', 'Failed to send assessment reminder');
        } finally {
            setLoading(false);
        }
    };

    return (
        <AdminLayout>
            <Head title="Email Test" />
            
            <div className="space-y-6">
                <div>
                    <h1 className="text-3xl font-bold">Email Test Center</h1>
                    <p className="text-muted-foreground">
                        Test the PHPMailer integration with your Gmail account
                    </p>
                </div>

                {message && (
                    <Alert className={message.type === 'success' ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'}>
                        <AlertDescription className={message.type === 'success' ? 'text-green-700' : 'text-red-700'}>
                            {message.text}
                        </AlertDescription>
                    </Alert>
                )}

                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="test">Test Email</TabsTrigger>
                        <TabsTrigger value="internship">Internship Notification</TabsTrigger>
                        <TabsTrigger value="assessment">Assessment Reminder</TabsTrigger>
                    </TabsList>

                    <TabsContent value="test">
                        <Card>
                            <CardHeader>
                                <CardTitle>Send Test Email</CardTitle>
                                <CardDescription>
                                    Send a simple test email to verify PHPMailer is working correctly
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={sendTestEmail} className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="to">To Email</Label>
                                            <Input
                                                id="to"
                                                type="email"
                                                value={testEmail.to}
                                                onChange={(e) => setTestEmail({ ...testEmail, to: e.target.value })}
                                                placeholder="recipient@example.com"
                                                required
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="to_name">Recipient Name (Optional)</Label>
                                            <Input
                                                id="to_name"
                                                value={testEmail.to_name}
                                                onChange={(e) => setTestEmail({ ...testEmail, to_name: e.target.value })}
                                                placeholder="John Doe"
                                            />
                                        </div>
                                    </div>
                                    
                                    <div className="space-y-2">
                                        <Label htmlFor="subject">Subject</Label>
                                        <Input
                                            id="subject"
                                            value={testEmail.subject}
                                            onChange={(e) => setTestEmail({ ...testEmail, subject: e.target.value })}
                                            required
                                        />
                                    </div>
                                    
                                    <div className="space-y-2">
                                        <Label htmlFor="body">Message Body (HTML)</Label>
                                        <Textarea
                                            id="body"
                                            value={testEmail.body}
                                            onChange={(e) => setTestEmail({ ...testEmail, body: e.target.value })}
                                            rows={6}
                                            required
                                        />
                                    </div>
                                    
                                    <Button type="submit" disabled={loading}>
                                        {loading ? 'Sending...' : 'Send Test Email'}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="internship">
                        <Card>
                            <CardHeader>
                                <CardTitle>Internship Notification</CardTitle>
                                <CardDescription>
                                    Send a formatted internship placement notification to a student
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={sendInternshipNotification} className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="student_email">Student Email</Label>
                                            <Input
                                                id="student_email"
                                                type="email"
                                                value={internshipNotification.student_email}
                                                onChange={(e) => setInternshipNotification({ ...internshipNotification, student_email: e.target.value })}
                                                placeholder="student@bulsu.edu.ph"
                                                required
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="student_name">Student Name</Label>
                                            <Input
                                                id="student_name"
                                                value={internshipNotification.student_name}
                                                onChange={(e) => setInternshipNotification({ ...internshipNotification, student_name: e.target.value })}
                                                placeholder="Juan Dela Cruz"
                                                required
                                            />
                                        </div>
                                    </div>
                                    
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="company_name">Company Name</Label>
                                            <Input
                                                id="company_name"
                                                value={internshipNotification.company_name}
                                                onChange={(e) => setInternshipNotification({ ...internshipNotification, company_name: e.target.value })}
                                                placeholder="ABC Corporation"
                                                required
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="position">Position</Label>
                                            <Input
                                                id="position"
                                                value={internshipNotification.position}
                                                onChange={(e) => setInternshipNotification({ ...internshipNotification, position: e.target.value })}
                                                placeholder="Software Developer Intern"
                                                required
                                            />
                                        </div>
                                    </div>
                                    
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="duration">Duration</Label>
                                            <Input
                                                id="duration"
                                                value={internshipNotification.duration}
                                                onChange={(e) => setInternshipNotification({ ...internshipNotification, duration: e.target.value })}
                                                placeholder="3 months"
                                                required
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="start_date">Start Date</Label>
                                            <Input
                                                id="start_date"
                                                value={internshipNotification.start_date}
                                                onChange={(e) => setInternshipNotification({ ...internshipNotification, start_date: e.target.value })}
                                                placeholder="January 15, 2024"
                                                required
                                            />
                                        </div>
                                    </div>
                                    
                                    <Button type="submit" disabled={loading}>
                                        {loading ? 'Sending...' : 'Send Internship Notification'}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="assessment">
                        <Card>
                            <CardHeader>
                                <CardTitle>Assessment Reminder</CardTitle>
                                <CardDescription>
                                    Send a reminder email to students about pending assessments
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={sendAssessmentReminder} className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="reminder_student_email">Student Email</Label>
                                            <Input
                                                id="reminder_student_email"
                                                type="email"
                                                value={assessmentReminder.student_email}
                                                onChange={(e) => setAssessmentReminder({ ...assessmentReminder, student_email: e.target.value })}
                                                placeholder="student@bulsu.edu.ph"
                                                required
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="reminder_student_name">Student Name</Label>
                                            <Input
                                                id="reminder_student_name"
                                                value={assessmentReminder.student_name}
                                                onChange={(e) => setAssessmentReminder({ ...assessmentReminder, student_name: e.target.value })}
                                                placeholder="Juan Dela Cruz"
                                                required
                                            />
                                        </div>
                                    </div>
                                    
                                    <div className="space-y-2">
                                        <Label htmlFor="assessment_type">Assessment Type</Label>
                                        <Input
                                            id="assessment_type"
                                            value={assessmentReminder.assessment_type}
                                            onChange={(e) => setAssessmentReminder({ ...assessmentReminder, assessment_type: e.target.value })}
                                            placeholder="Self-Assessment"
                                            required
                                        />
                                    </div>
                                    
                                    <Button type="submit" disabled={loading}>
                                        {loading ? 'Sending...' : 'Send Assessment Reminder'}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>

                <Card>
                    <CardHeader>
                        <CardTitle>Email Configuration</CardTitle>
                        <CardDescription>
                            Current email settings and status
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2">
                            <p><strong>From Email:</strong> internconnectbulsu@gmail.com</p>
                            <p><strong>From Name:</strong> InternConnect BULSU</p>
                            <p><strong>SMTP Host:</strong> smtp.gmail.com</p>
                            <p><strong>SMTP Port:</strong> 587</p>
                            <p><strong>Encryption:</strong> STARTTLS</p>
                            <p><strong>Status:</strong> <span className="text-green-600">Configured</span></p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
