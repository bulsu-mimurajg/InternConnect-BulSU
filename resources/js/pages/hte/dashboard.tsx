import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { 
    UsersIcon, 
    BriefcaseIcon, 
    Building2Icon,
    TrendingUpIcon,
    AlertCircle
} from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/hte/dashboard',
    },
];

interface HTEDashboardProps {
    hte: {
        id: number;
        company_name: string;
        company_address: string;
        company_email: string;
        cperson_fname: string;
        cperson_lname: string;
        cperson_position: string;
        cperson_contactnum: string;
        is_active: boolean;
        is_submit: boolean;
        created_at: string;
        internships: Array<{
            id: number;
            position_title: string;
            department: string;
            placement_description: string;
            slot_count: number;
            available_slots_count: number;
            filled_slots_count: number;
            is_active: boolean;
            created_at: string;
            subcategory_weights: Array<{
                id: number;
                weight: number;
                subcategory: {
                    id: number;
                    subcategory_name: string;
                };
            }>;
        }>;
    };
    stats: {
        totalInternships: number;
        activeInternships: number;
        totalSlots: number;
        internshipSlots: Array<{
            id: number;
            position_title: string;
            slot_count: number;
            available_slots: number;
            filled_slots: number;
            is_active: boolean;
            created_at: string;
        }>;
        companyName: string;
        contactPerson: string;
        email: string;
        phone: string;
        address: string;
    };
    showSubmissionPrompt: boolean;
    [key: string]: unknown;
}

export default function HTEDashboardPage() {
    const { hte, stats, showSubmissionPrompt } = usePage<HTEDashboardProps>().props;

    // Add defensive programming to handle missing data
    if (!hte) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="HTE Dashboard" />
                <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                    <div className="text-center py-8 text-muted-foreground">
                        <p>HTE dashboard not found.</p>
                    </div>
                </div>
            </AppLayout>
        );
    }

    // Show assessment prompt if not submitted
    if (showSubmissionPrompt) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="HTE Dashboard" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <AlertCircle className="mx-auto h-12 w-12 text-yellow-500 mb-4" />
                                <h2 className="text-xl font-semibold mb-2">Assessment Not Submitted</h2>
                                <p className="text-muted-foreground mb-4">
                                    You need to complete your assessment form to access the dashboard.
                                </p>
                                <Button asChild>
                                    <Link href="/form">
                                        Take Assessment
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HTE Dashboard" />
            <div className="relative">
                {/* Full Background Image - covers entire main content area */}
                <div
                    className="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-70 dark:opacity-50 bg-fixed"
                    style={{backgroundImage: 'url(/images/pimentel.jpg)'}}
                />
                {/* Background Overlay */}
                <div className="absolute inset-0 bg-background/80 dark:bg-background/90" />
                
                {/* Content Container with padding */}
                <div className="relative z-10 flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                {/* Header */}
                <div className="space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">Company Dashboard</h1>
                    <p className="text-muted-foreground">
                        Welcome back, {hte.company_name}! Here's an overview of your internship program.
                    </p>
                </div>

                {/* Company Status Overview */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Building2Icon className="h-5 w-5" />
                            Company Status
                        </CardTitle>
                        <CardDescription>
                            Your company's profile and assessment status
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-6 md:grid-cols-2">
                            {/* Status Information */}
                            <div className="space-y-4">
                                <div className="flex items-center justify-between py-2">
                                    <span className="text-sm font-medium text-muted-foreground">Assessment Status</span>
                                    <Badge variant={hte.is_submit ? "default" : "secondary"}>
                                        {hte.is_submit ? "Completed" : "Pending"}
                                    </Badge>
                                </div>
                                <div className="flex items-center justify-between py-2">
                                    <span className="text-sm font-medium text-muted-foreground">Internship offers</span>
                                    <span className="text-sm font-medium text-primary">
                                        {hte.internships.length}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between py-2">
                                    <span className="text-sm font-medium text-muted-foreground">Registration Date</span>
                                    <span className="text-sm font-medium">
                                        {new Date(hte.created_at).toLocaleDateString()}
                                    </span>
                                </div>
                            </div>
                            
                            {/* Contact Information */}
                            <div className="space-y-4">
                                <div className="flex items-center justify-between py-2">
                                    <span className="text-sm font-medium text-muted-foreground">Contact Person</span>
                                    <span className="text-sm font-medium">{hte.cperson_fname} {hte.cperson_lname}</span>
                                </div>
                                <div className="flex items-center justify-between py-2">
                                    <span className="text-sm font-medium text-muted-foreground">Position</span>
                                    <span className="text-sm font-medium">{hte.cperson_position}</span>
                                </div>
                                <div className="flex items-center justify-between py-2">
                                    <span className="text-sm font-medium text-muted-foreground">Email</span>
                                    <span className="text-sm font-medium">{hte.company_email}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Key Metrics */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Internships</CardTitle>
                            <BriefcaseIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalInternships}</div>
                            <p className="text-xs text-muted-foreground">
                                Created positions
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active Internships</CardTitle>
                            <TrendingUpIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.activeInternships}</div>
                            <p className="text-xs text-muted-foreground">
                                Currently available
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Slots</CardTitle>
                            <UsersIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalSlots}</div>
                            <p className="text-xs text-muted-foreground">
                                Available positions
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Internship Positions Overview */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <BriefcaseIcon className="h-5 w-5" />
                            Internship Positions
                        </CardTitle>
                        <CardDescription>
                            Overview of your available internship opportunities
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {hte.internships.length > 0 ? (
                            <div className="space-y-4">
                                {hte.internships.map((internship) => (
                                    <div key={internship.id} className="flex flex-col md:flex-row md:items-center md:justify-between p-4 border rounded-lg gap-3 md:gap-4">
                                        <div className="flex-1">
                                            <div className="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                                                <h4 className="font-medium text-base">{internship.position_title}</h4>
                                                <Badge variant={internship.is_active ? "default" : "secondary"} className="w-fit">
                                                    {internship.is_active ? "Active" : "Inactive"}
                                                </Badge>
                                            </div>
                                            
                                            <p className="text-xs text-muted-foreground">
                                                Created: {new Date(internship.created_at).toLocaleDateString()}
                                            </p>
                                        </div>
                                        
                                        {/* Slot Information */}
                                        <div className="flex items-center justify-between md:justify-end gap-4 bg-muted/50 p-3 rounded-lg md:w-auto">
                                            <div className="text-center">
                                                <div className="text-lg font-bold text-primary">{internship.slot_count}</div>
                                                <div className="text-xs text-muted-foreground">total</div>
                                            </div>
                                            <div className="text-center">
                                                <div className={`text-lg font-bold ${internship.available_slots_count > 0 ? 'text-green-600' : 'text-orange-600'}`}>
                                                    {internship.available_slots_count}
                                                </div>
                                                <div className="text-xs text-muted-foreground">available</div>
                                            </div>
                                            <div className="text-center">
                                                <div className="text-lg font-bold text-slate-600">{internship.filled_slots_count}</div>
                                                <div className="text-xs text-muted-foreground">filled</div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-8 text-muted-foreground">
                                <BriefcaseIcon className="h-12 w-12 mx-auto mb-3 opacity-50" />
                                <p className="text-sm">No internship positions created yet</p>
                                <p className="text-xs mt-1">Create your first internship position to get started</p>
                            </div>
                        )}
                    </CardContent>
                </Card>


                </div>
            </div>
        </AppLayout>
    );
}
