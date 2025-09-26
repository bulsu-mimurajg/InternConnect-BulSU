import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { PieChart } from '@/components/ui/pie-chart';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
    Building2, 
    Calendar, 
    Users, 
    Target, 
    CheckCircle, 
    XCircle, 
    PlusIcon,
    EditIcon,
    AlertCircle
} from 'lucide-react';
import { useState, useMemo, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { SubmissionPrompt } from '@/components/hte/submission-prompt';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { SaveIcon, XIcon } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HTE Profile',
        href: '/hte/profile',
    },
];

interface HTEProfileProps {
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
            is_active: boolean;
            created_at: string;
            updated_at: string;
            subcategory_weights: Array<{
                id: number;
                weight: number;
                subcategory: {
                    id: number;
                    subcategory_name: string;
                    category: {
                        id: number;
                        category_name: string;
                    };
                };
            }>;
        }>;
    };
    showSubmissionPrompt: boolean;
    studentAssessmentDeadlineActive: boolean;
    studentAssessmentDeadline?: {
        title: string;
        end_date: string;
    };
    [key: string]: unknown;
}

// Form validation schema for company information
const CompanyInfoSchema = z.object({
    company_name: z.string().min(1, 'Company name is required'),
    company_address: z.string().min(1, 'Company address is required'),
    company_email: z.string().email('Invalid email address'),
    cperson_fname: z.string().min(1, 'Contact person first name is required'),
    cperson_lname: z.string().min(1, 'Contact person last name is required'),
    cperson_position: z.string().min(1, 'Contact person position is required'),
    cperson_contactnum: z.string().min(1, 'Contact number is required'),
});

type CompanyInfoFormData = z.infer<typeof CompanyInfoSchema>;

export default function HTEProfilePage() {
    const { hte, showSubmissionPrompt, studentAssessmentDeadlineActive, studentAssessmentDeadline } = usePage<HTEProfileProps>().props;
    
    // Get internship ID from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const internshipIdFromUrl = urlParams.get('internship');
    
    // State for dropdowns
    const [selectedStatus, setSelectedStatus] = useState<string>('all');
    const [selectedInternshipId, setSelectedInternshipId] = useState<string>(internshipIdFromUrl || '');
    const [showConfirmDialog, setShowConfirmDialog] = useState<boolean>(false);
    const [internshipToToggle, setInternshipToToggle] = useState<number | null>(null);
    const [showSuccessMessage, setShowSuccessMessage] = useState<boolean>(false);
    const [isToggling, setIsToggling] = useState<boolean>(false);
    const [isEditingCompany, setIsEditingCompany] = useState<boolean>(false);
    const [isSavingCompany, setIsSavingCompany] = useState<boolean>(false);

    // Form for company information editing
    const companyForm = useForm<CompanyInfoFormData>({
        resolver: zodResolver(CompanyInfoSchema),
        defaultValues: {
            company_name: hte?.company_name || '',
            company_address: hte?.company_address || '',
            company_email: hte?.company_email || '',
            cperson_fname: hte?.cperson_fname || '',
            cperson_lname: hte?.cperson_lname || '',
            cperson_position: hte?.cperson_position || '',
            cperson_contactnum: hte?.cperson_contactnum || '',
        },
    });

    // Auto-hide success message after 5 seconds
    useEffect(() => {
        if (showSuccessMessage) {
            const timer = setTimeout(() => {
                setShowSuccessMessage(false);
            }, 5000);
            return () => clearTimeout(timer);
        }
    }, [showSuccessMessage]);

    // Filter internships based on selected status
    const filteredInternships = useMemo(() => {
        if (!hte?.internships) return [];
        if (selectedStatus === 'all') {
            return hte.internships;
        }
        return hte.internships.filter(internship => {
            if (selectedStatus === 'active') return internship.is_active;
            if (selectedStatus === 'inactive') return !internship.is_active;
            return true;
        });
    }, [hte?.internships, selectedStatus]);

    // Get the selected internship
    const selectedInternship = useMemo(() => {
        if (!selectedInternshipId) {
            return filteredInternships.length > 0 ? filteredInternships[0] : null;
        }
        return filteredInternships.find(internship => internship.id.toString() === selectedInternshipId) || null;
    }, [filteredInternships, selectedInternshipId]);

    // Auto-select first internship on initial load
    useEffect(() => {
        if (hte?.internships && hte.internships.length > 0 && !selectedInternshipId && !internshipIdFromUrl) {
            setSelectedInternshipId(hte.internships[0].id.toString());
        }
    }, [hte?.internships, selectedInternshipId, internshipIdFromUrl]);

    // Add defensive programming to handle missing data
    if (!hte) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="HTE Profile" />
                <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                    <div className="text-center py-8 text-muted-foreground">
                        <p>HTE profile not found.</p>
                    </div>
                </div>
            </AppLayout>
        );
    }

    // Show assessment prompt if not submitted
    if (showSubmissionPrompt) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="HTE Profile" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <AlertCircle className="mx-auto h-12 w-12 text-yellow-500 mb-4" />
                                <h2 className="text-xl font-semibold mb-2">Assessment Not Submitted</h2>
                                <p className="text-muted-foreground mb-4">
                                    You need to complete your assessment form to access your company profile.
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

    // Update selected internship when status changes
    const handleStatusChange = (value: string) => {
        setSelectedStatus(value);
        setSelectedInternshipId(''); // Reset internship selection
    };

    // Auto-select first internship when filtered internships change
    useEffect(() => {
        if (filteredInternships.length > 0 && !selectedInternshipId) {
            setSelectedInternshipId(filteredInternships[0].id.toString());
        }
    }, [filteredInternships, selectedInternshipId]);

    // Handle internship status toggle
    const handleToggleStatus = (internshipId: number) => {
        setInternshipToToggle(internshipId);
        setShowConfirmDialog(true);
    };

    // Confirm and execute status toggle
    const confirmToggleStatus = () => {
        if (internshipToToggle) {
            setIsToggling(true);
            router.patch(`/hte/internship/${internshipToToggle}/toggle-status`, {}, {
                onSuccess: () => {
                    setShowSuccessMessage(true);
                    setIsToggling(false);
                },
                onError: () => {
                    setIsToggling(false);
                }
            });
            setShowConfirmDialog(false);
            setInternshipToToggle(null);
        }
    };

    // Cancel status toggle
    const cancelToggleStatus = () => {
        setShowConfirmDialog(false);
        setInternshipToToggle(null);
    };

    // Handle company information editing
    const handleEditCompany = () => {
        setIsEditingCompany(true);
        // Reset form to current values
        companyForm.reset({
            company_name: hte?.company_name || '',
            company_address: hte?.company_address || '',
            company_email: hte?.company_email || '',
            cperson_fname: hte?.cperson_fname || '',
            cperson_lname: hte?.cperson_lname || '',
            cperson_position: hte?.cperson_position || '',
            cperson_contactnum: hte?.cperson_contactnum || '',
        });
    };

    const handleCancelEditCompany = () => {
        setIsEditingCompany(false);
        companyForm.reset();
    };

    const handleSaveCompanyInfo = (data: CompanyInfoFormData) => {
        setIsSavingCompany(true);
        router.patch('/hte/update-company-info', data, {
            onSuccess: () => {
                setIsEditingCompany(false);
                setIsSavingCompany(false);
                setShowSuccessMessage(true);
            },
            onError: (errors) => {
                setIsSavingCompany(false);
                // Handle validation errors
                Object.entries(errors).forEach(([key, value]) => {
                    companyForm.setError(key as keyof CompanyInfoFormData, {
                        type: 'manual',
                        message: value as string,
                    });
                });
            },
        });
    };

    // Extract duration, start date, and end date from placement description
    const extractDuration = (description: string) => {
        const match = description.match(/Duration: ([^-]+)/);
        return match ? match[1].trim() : 'Not specified';
    };

    const extractStartDate = (description: string) => {
        const match = description.match(/from ([^to]+) to/);
        return match ? match[1].trim() : 'Not specified';
    };

    const extractEndDate = (description: string) => {
        const match = description.match(/to (.+)$/);
        return match ? match[1].trim() : 'Not specified';
    };

    // Group subcategory weights by category for each internship
    const getWeightsByCategory = (internship: {
        subcategory_weights: Array<{
            id: number;
            weight: number;
            subcategory: {
                id: number;
                subcategory_name: string;
                category: {
                    id: number;
                    category_name: string;
                };
            };
        }>;
    }) => {
        return internship.subcategory_weights.reduce((acc: Record<string, typeof internship.subcategory_weights>, weight) => {
            const categoryName = weight.subcategory.category.category_name;
            if (!acc[categoryName]) {
                acc[categoryName] = [];
            }
            acc[categoryName].push(weight);
            return acc;
        }, {});
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HTE Profile" />
            
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                {/* Success Message */}
                {showSuccessMessage && (
                    <div className="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg">
                        <div className="flex items-center gap-2">
                            <CheckCircle className="h-5 w-5" />
                            <span className="font-medium">Information updated successfully!</span>
                        </div>
                    </div>
                )}

                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-bold tracking-tight">{hte.company_name || 'Company Profile'}</h1>
                        <p className="text-muted-foreground">
                            Company Profile & Internship Management
                        </p>
                    </div>
                    
                    <div className="flex items-center gap-3">
                        <Link href="/hte/add-internship">
                            <Button className="gap-2">
                                <PlusIcon className="h-4 w-4" />
                                Add Internship
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="space-y-6">
                    {/* Company Information & Overview Section */}
                    <div className="grid gap-6 lg:grid-cols-3">
                        {/* Company Information - Takes 2 columns */}
                        <Card className="lg:col-span-2 h-full flex flex-col">
                            <CardHeader className="pb-4">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="flex items-center gap-2">
                                            <Building2 className="h-5 w-5" />
                                            Company Information
                                        </CardTitle>
                                        <CardDescription>Basic company details and contact information</CardDescription>
                                    </div>
                                    {!isEditingCompany && (
                                        <Button 
                                            variant="outline" 
                                            size="sm" 
                                            onClick={handleEditCompany}
                                            className="gap-2"
                                        >
                                            <EditIcon className="h-4 w-4" />
                                            Edit
                                        </Button>
                                    )}
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-6 flex-1">
                                {isEditingCompany ? (
                                    <Form {...companyForm}>
                                        <form onSubmit={companyForm.handleSubmit(handleSaveCompanyInfo)} className="space-y-6">
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <FormField
                                                    control={companyForm.control}
                                                    name="company_name"
                                                    render={({ field }) => (
                                                        <FormItem>
                                                            <FormLabel>Company Name</FormLabel>
                                                            <FormControl>
                                                                <Input {...field} />
                                                            </FormControl>
                                                            <FormMessage />
                                                        </FormItem>
                                                    )}
                                                />
                                                <FormField
                                                    control={companyForm.control}
                                                    name="company_email"
                                                    render={({ field }) => (
                                                        <FormItem>
                                                            <FormLabel>Email</FormLabel>
                                                            <FormControl>
                                                                <Input {...field} type="email" />
                                                            </FormControl>
                                                            <FormMessage />
                                                        </FormItem>
                                                    )}
                                                />
                                                <FormField
                                                    control={companyForm.control}
                                                    name="company_address"
                                                    render={({ field }) => (
                                                        <FormItem className="md:col-span-2">
                                                            <FormLabel>Address</FormLabel>
                                                            <FormControl>
                                                                <Textarea {...field} rows={3} />
                                                            </FormControl>
                                                            <FormMessage />
                                                        </FormItem>
                                                    )}
                                                />
                                                <FormField
                                                    control={companyForm.control}
                                                    name="cperson_fname"
                                                    render={({ field }) => (
                                                        <FormItem>
                                                            <FormLabel>Contact Person First Name</FormLabel>
                                                            <FormControl>
                                                                <Input {...field} />
                                                            </FormControl>
                                                            <FormMessage />
                                                        </FormItem>
                                                    )}
                                                />
                                                <FormField
                                                    control={companyForm.control}
                                                    name="cperson_lname"
                                                    render={({ field }) => (
                                                        <FormItem>
                                                            <FormLabel>Contact Person Last Name</FormLabel>
                                                            <FormControl>
                                                                <Input {...field} />
                                                            </FormControl>
                                                            <FormMessage />
                                                        </FormItem>
                                                    )}
                                                />
                                                <FormField
                                                    control={companyForm.control}
                                                    name="cperson_position"
                                                    render={({ field }) => (
                                                        <FormItem>
                                                            <FormLabel>Position</FormLabel>
                                                            <FormControl>
                                                                <Input {...field} />
                                                            </FormControl>
                                                            <FormMessage />
                                                        </FormItem>
                                                    )}
                                                />
                                                <FormField
                                                    control={companyForm.control}
                                                    name="cperson_contactnum"
                                                    render={({ field }) => (
                                                        <FormItem>
                                                            <FormLabel>Contact Number</FormLabel>
                                                            <FormControl>
                                                                <Input {...field} />
                                                            </FormControl>
                                                            <FormMessage />
                                                        </FormItem>
                                                    )}
                                                />
                                            </div>
                                            <div className="flex justify-end gap-3 pt-4 border-t border-border">
                                                <Button 
                                                    type="button" 
                                                    variant="outline" 
                                                    onClick={handleCancelEditCompany}
                                                    disabled={isSavingCompany}
                                                >
                                                    <XIcon className="h-4 w-4 mr-2" />
                                                    Cancel
                                                </Button>
                                                <Button 
                                                    type="submit" 
                                                    disabled={isSavingCompany}
                                                >
                                                    <SaveIcon className="h-4 w-4 mr-2" />
                                                    {isSavingCompany ? 'Saving...' : 'Save Changes'}
                                                </Button>
                                            </div>
                                        </form>
                                    </Form>
                                ) : (
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div className="space-y-3">
                                            <label className="text-base font-medium text-muted-foreground">Company Name</label>
                                            <p className="text-lg font-semibold text-foreground">{hte.company_name || 'Not provided'}</p>
                                        </div>
                                        <div className="space-y-3">
                                            <label className="text-base font-medium text-muted-foreground">Email</label>
                                            <p className="text-lg text-primary font-medium">{hte.company_email || 'Not provided'}</p>
                                        </div>
                                        <div className="space-y-3 md:col-span-2">
                                            <label className="text-base font-medium text-muted-foreground">Address</label>
                                            <div className="text-lg text-foreground leading-relaxed space-y-1">
                                                {hte.company_address ? (
                                                    hte.company_address.split(',').map((part, index) => (
                                                        <p key={index} className="text-lg">{part.trim()}</p>
                                                    ))
                                                ) : (
                                                    <p className="text-lg text-muted-foreground">Not provided</p>
                                                )}
                                            </div>
                                        </div>
                                        <div className="space-y-3">
                                            <label className="text-base font-medium text-muted-foreground">Contact Person</label>
                                            <div className="space-y-2">
                                                <p className="text-lg font-semibold text-foreground">
                                                    {hte.cperson_fname || 'Not provided'} {hte.cperson_lname || ''}
                                                </p>
                                                <p className="text-base text-muted-foreground">{hte.cperson_position || 'Not provided'}</p>
                                            </div>
                                        </div>
                                        <div className="space-y-3">
                                            <label className="text-base font-medium text-muted-foreground">Contact Number</label>
                                            <p className="text-lg text-foreground font-medium">{hte.cperson_contactnum || 'Not provided'}</p>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Overview & Statistics - Takes 1 column with equal height cards */}
                        <div className="space-y-6 h-full flex flex-col">
                            {/* Company Overview */}
                            <Card className="flex-1 flex flex-col">
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <Building2 className="h-4 w-4" />
                                        Company Overview
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4 flex-1 flex flex-col justify-center">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Status</span>
                                        <Badge variant={hte.is_active ? "default" : "secondary"}>
                                            {hte.is_active ? "Active" : "Inactive"}
                                        </Badge>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Partner Since</span>
                                        <span className="text-sm text-muted-foreground">
                                            {new Date(hte.created_at).toLocaleDateString('en-US', { 
                                                year: 'numeric', 
                                                month: 'long' 
                                            })}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Internship Statistics */}
                            <Card className="flex-1 flex flex-col">
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <Target className="h-4 w-4" />
                                        Internship Statistics
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4 flex-1 flex flex-col justify-center">
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                                            <span className="text-sm font-medium">Total Internships</span>
                                            <span className="text-lg font-bold text-primary">
                                                {hte.internships?.length || 0}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                                            <span className="text-sm font-medium">Active Positions</span>
                                            <span className="text-lg font-bold text-green-600">
                                                {hte.internships?.filter(i => i.is_active).length || 0}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                                            <span className="text-sm font-medium">Total Slots</span>
                                            <span className="text-lg font-bold text-blue-600">
                                                {hte.internships?.reduce((sum, i) => sum + i.slot_count, 0) || 0}
                                            </span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    {/* Internships Section with Dropdowns - Full Width */}
                    <div className="w-full">
                        {hte.internships && hte.internships.length > 0 ? (
                            <div className="space-y-6">
                                <div className="flex items-center gap-3">
                                    <h2 className="text-2xl font-bold">Internship Opportunities</h2>
                                    <p className="text-muted-foreground">
                                        {filteredInternships.length} position{filteredInternships.length !== 1 ? 's' : ''} available
                                    </p>
                                </div>

                                {/* Dropdowns */}
                                <div className="flex gap-4 items-end">
                                    <div className="space-y-2 flex-shrink-0">
                                        <label className="text-sm font-medium text-muted-foreground">Status Filter</label>
                                        <Select value={selectedStatus} onValueChange={handleStatusChange}>
                                            <SelectTrigger className="w-48">
                                                <SelectValue placeholder="Select Status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">All Statuses ({hte.internships?.length || 0})</SelectItem>
                                                <SelectItem value="active">Active Only ({hte.internships?.filter(i => i.is_active).length || 0})</SelectItem>
                                                <SelectItem value="inactive">Inactive Only ({hte.internships?.filter(i => !i.is_active).length || 0})</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2 flex-1">
                                        <label className="text-sm font-medium text-muted-foreground">Select Internship</label>
                                        <Select value={selectedInternshipId} onValueChange={setSelectedInternshipId}>
                                            <SelectTrigger className="w-full">
                                                <SelectValue placeholder="Select an internship" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {filteredInternships.map((internship) => (
                                                    <SelectItem key={internship.id} value={internship.id.toString()}>
                                                        <span>{internship.position_title} - {internship.department}</span>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                {/* Selected Internship Display */}
                                {selectedInternship ? (
                                    <Card className="border-2">
                                        <CardHeader>
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <CardTitle className="text-xl">{selectedInternship.position_title}</CardTitle>
                                                    <CardDescription>
                                                        {selectedInternship.department} • {selectedInternship.slot_count} slot{selectedInternship.slot_count !== 1 ? 's' : ''}
                                                    </CardDescription>
                                                </div>
                                                <Badge variant={selectedInternship.is_active ? "default" : "secondary"} className="px-4 py-2 text-sm font-semibold">
                                                    {selectedInternship.is_active ? (
                                                        <>
                                                            <CheckCircle className="h-6 w-6 mr-2" />
                                                            Active
                                                        </>
                                                    ) : (
                                                        <>
                                                            <XCircle className="h-6 w-6 mr-2" />
                                                            Inactive
                                                        </>
                                                    )}
                                                </Badge>
                                            </div>
                                        </CardHeader>
                                        <CardContent className="space-y-6">
                                            {/* Position Details Section */}
                                            <div className="border border-border rounded-lg bg-card">
                                                <div className="bg-muted/50 px-4 py-3 border-b border-border">
                                                    <h3 className="font-semibold text-base text-foreground">Position Details</h3>
                                                </div>
                                                <div className="p-6">
                                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                        <div className="space-y-4">
                                                            <div>
                                                                <label className="text-sm font-medium text-muted-foreground">Position Title</label>
                                                                <p className="text-sm font-semibold text-foreground">{selectedInternship.position_title}</p>
                                                            </div>
                                                            <div>
                                                                <label className="text-sm font-medium text-muted-foreground">Department</label>
                                                                <p className="text-sm font-semibold text-foreground">{selectedInternship.department}</p>
                                                            </div>
                                                            <div>
                                                                <label className="text-sm font-medium text-muted-foreground">Available Slots</label>
                                                                <p className="text-sm font-semibold flex items-center gap-2 text-foreground">
                                                                    <Users className="h-4 w-4" />
                                                                    {selectedInternship.slot_count} slot{selectedInternship.slot_count !== 1 ? 's' : ''}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div className="space-y-4">
                                                            <div>
                                                                <label className="text-sm font-medium text-muted-foreground">Duration</label>
                                                                <p className="text-sm font-semibold text-foreground">{extractDuration(selectedInternship.placement_description)}</p>
                                                            </div>
                                                            <div>
                                                                <label className="text-sm font-medium text-muted-foreground">Start Date</label>
                                                                <p className="text-sm font-semibold flex items-center gap-2 text-foreground">
                                                                    <Calendar className="h-4 w-4" />
                                                                    {extractStartDate(selectedInternship.placement_description)}
                                                                </p>
                                                            </div>
                                                            <div>
                                                                <label className="text-sm font-medium text-muted-foreground">End Date</label>
                                                                <p className="text-sm font-semibold flex items-center gap-2 text-foreground">
                                                                    <Calendar className="h-4 w-4" />
                                                                    {extractEndDate(selectedInternship.placement_description)}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    {selectedInternship.placement_description && (
                                                        <div className="mt-6 pt-4 border-t border-border">
                                                            <label className="text-sm font-medium text-muted-foreground">Description</label>
                                                            <p className="text-sm text-foreground mt-2 leading-relaxed">{selectedInternship.placement_description}</p>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>

                                            {/* Assessment Criteria and Weights Section */}
                                            <div className="border border-border rounded-lg bg-card">
                                                <div className="bg-muted/50 px-4 py-3 border-b border-border">
                                                    <h3 className="font-semibold text-base text-foreground">Assessment Criteria</h3>
                                                </div>
                                                <div className="p-6">
                                                    {(() => {
                                                        const weightsByCategory = getWeightsByCategory(selectedInternship);
                                                        return Object.entries(weightsByCategory).length > 0 ? (
                                                            <div className="space-y-6">
                                                                {Object.entries(weightsByCategory).map(([categoryName, weights]: [string, Array<{
                                                                    id: number;
                                                                    weight: number;
                                                                    subcategory: {
                                                                        id: number;
                                                                        subcategory_name: string;
                                                                        category: {
                                                                            id: number;
                                                                            category_name: string;
                                                                        };
                                                                    };
                                                                }>]) => {
                                                                    // Calculate category total weight
                                                                    const categoryTotal = weights.reduce((sum: number, w) => sum + w.weight, 0);
                                                                    
                                                                    // Prepare data for pie chart with consistent colors
                                                                    const colors = [
                                                                        'hsl(220, 70%, 50%)', // Blue
                                                                        'hsl(120, 70%, 50%)', // Green
                                                                        'hsl(30, 70%, 50%)',  // Orange
                                                                        'hsl(280, 70%, 50%)', // Purple
                                                                        'hsl(0, 70%, 50%)',   // Red
                                                                        'hsl(60, 70%, 50%)'   // Yellow
                                                                    ];
                                                                    
                                                                    const pieData = weights.map((weight, index) => ({
                                                                        name: weight.subcategory.subcategory_name,
                                                                        value: weight.weight,
                                                                        color: colors[index % colors.length]
                                                                    }));
                                                                    
                                                                    return (
                                                                        <div key={categoryName} className="border border-border rounded-lg bg-card">
                                                                            <div className="bg-primary/5 px-4 py-3 border-b border-border">
                                                                                <h4 className="font-semibold text-base text-foreground">{categoryName}</h4>
                                                                                <p className="text-sm text-muted-foreground">Total Weight:  {categoryTotal}%</p>
                                                                            </div>
                                                                            <div className="p-8">
                                                                                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                                                                    {/* Weight Distribution */}
                                                                                    <div className="lg:col-span-1 flex flex-col h-full">
                                                                                        <div className="text-center lg:text-left mb-6">
                                                                                            <h5 className="font-semibold text-base text-foreground mb-2">Weight Distribution</h5>
                                                                                            <p className="text-sm text-muted-foreground">Breakdown of assessment criteria weights</p>
                                                                                        </div>
                                                                                        <div className="flex flex-col h-full">
                                                                                            <div className="space-y-4 flex-1">
                                                                                                {weights.map((weight, index) => (
                                                                                                    <div key={weight.id} className="flex items-center justify-between p-4 bg-muted/20 rounded-xl border border-border/50 hover:bg-muted/30 transition-colors">
                                                                                                        <div className="flex items-center gap-4">
                                                                                                            <div 
                                                                                                                className="w-4 h-4 rounded-full shadow-sm" 
                                                                                                                style={{ backgroundColor: colors[index % colors.length] }}
                                                                                                            />
                                                                                                            <span className="font-medium text-sm text-foreground">
                                                                                                                {weight.subcategory.subcategory_name}
                                                                                                            </span>
                                                                                                        </div>
                                                                                                        <Badge variant="outline" className="font-mono text-sm px-3 py-1">
                                                                                                            {weight.weight}%
                                                                                                        </Badge>
                                                                                                    </div>
                                                                                                ))}
                                                                                            </div>
                                                                                            <div className="flex items-center justify-between p-4 bg-primary/10 rounded-xl border border-primary/30 mt-4">
                                                                                                <span className="font-semibold text-primary text-base">Total Weight</span>
                                                                                                <Badge variant="default" className="font-mono text-sm px-3 py-1">
                                                                                                    {categoryTotal}%
                                                                                                </Badge>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    
                                                                                    {/* Pie Chart - Takes up 2/3 of the space */}
                                                                                    <div className="lg:col-span-2 flex flex-col items-center justify-center">
                                                                                        <div className="w-full h-full min-h-[400px]">
                                                                                            <PieChart 
                                                                                                data={pieData}
                                                                                                title={`${categoryName} Weights`}
                                                                                                totalWeight={categoryTotal}
                                                                                            />
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    );
                                                                })}
                                                            </div>
                                                        ) : (
                                                            <div className="text-center py-12 text-muted-foreground">
                                                                <Target className="h-12 w-12 mx-auto mb-4 opacity-50" />
                                                                <p className="text-sm">No assessment criteria configured for this internship.</p>
                                                            </div>
                                                        );
                                                    })()}
                                                </div>
                                            </div>

                                            {/* Action Buttons */}
                                            <div className="pt-4 border-t border-border">
                                                {/* Student Assessment Deadline Warning */}
                                                {studentAssessmentDeadlineActive && (
                                                    <div className="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                                                        <div className="flex items-start gap-3">
                                                            <div className="flex-shrink-0">
                                                                <Calendar className="h-4 w-4 text-amber-600 mt-0.5" />
                                                            </div>
                                                            <div className="flex-1">
                                                                <h4 className="text-sm font-medium text-amber-800">
                                                                    Student Assessment Period Active
                                                                </h4>
                                                                <p className="text-xs text-amber-700 mt-1">
                                                                    {studentAssessmentDeadline?.title} is currently active (ends {studentAssessmentDeadline?.end_date}). 
                                                                    <br />You cannot activate or deactivate internships during this period.
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}
                                                
                                                <div className="flex justify-end gap-3">
                                                    <Link href={`/hte/edit-internship/${selectedInternship.id}`}>
                                                        <Button variant="outline" size="sm" className="gap-2">
                                                            <EditIcon className="h-4 w-4" />
                                                            Edit Internship
                                                        </Button>
                                                    </Link>
                                                    {selectedInternship.is_active ? (
                                                        <Button 
                                                            variant="destructive" 
                                                            size="sm" 
                                                            className="gap-2"
                                                            onClick={() => handleToggleStatus(selectedInternship.id)}
                                                            disabled={isToggling || studentAssessmentDeadlineActive}
                                                            title={studentAssessmentDeadlineActive ? 'Cannot deactivate during student assessment period' : ''}
                                                        >
                                                            {isToggling ? 'Updating...' : 'Deactivate'}
                                                        </Button>
                                                    ) : (
                                                        <Button 
                                                            variant="default" 
                                                            size="sm" 
                                                            className="gap-2"
                                                            onClick={() => handleToggleStatus(selectedInternship.id)}
                                                            disabled={isToggling || studentAssessmentDeadlineActive}
                                                            title={studentAssessmentDeadlineActive ? 'Cannot activate during student assessment period' : ''}
                                                        >
                                                            {isToggling ? 'Updating...' : 'Activate'}
                                                        </Button>
                                                    )}
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ) : (
                                    <Card>
                                        <CardContent className="text-center py-12 text-muted-foreground">
                                            <Target className="h-16 w-16 mx-auto mb-4 opacity-50" />
                                            <h3 className="text-lg font-medium mb-2">No internships found</h3>
                                            <p className="text-sm">No internships match the selected criteria.</p>
                                        </CardContent>
                                    </Card>
                                )}
                            </div>
                        ) : (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Target className="h-5 w-5" />
                                        Internship Opportunities
                                    </CardTitle>
                                    <CardDescription>Manage your company's internship positions and student applications</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-center py-12 text-muted-foreground">
                                        <Target className="h-16 w-16 mx-auto mb-4 opacity-50" />
                                        <h3 className="text-lg font-medium mb-2">No internship opportunities yet</h3>
                                        <p className="text-sm mb-4">Create your first internship position to start attracting students.</p>
                                        <Link href="/hte/add-internship">
                                            <Button>
                                                <PlusIcon className="h-4 w-4 mr-2" />
                                                Create Internship
                                            </Button>
                                        </Link>
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>

            {/* Confirmation Dialog */}
            {showConfirmDialog && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                        <h3 className="text-lg font-semibold mb-4">Confirm Status Change</h3>
                        <p className="text-gray-600 mb-6">
                            Are you sure you want to change the status of this internship? 
                            This action will affect student applications and visibility.
                        </p>
                        <div className="flex justify-end gap-3">
                            <Button 
                                variant="outline" 
                                onClick={cancelToggleStatus}
                                disabled={isToggling}
                            >
                                Cancel
                            </Button>
                            <Button 
                                variant="destructive" 
                                onClick={confirmToggleStatus}
                                disabled={isToggling}
                            >
                                {isToggling ? 'Updating...' : 'Confirm'}
                            </Button>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
