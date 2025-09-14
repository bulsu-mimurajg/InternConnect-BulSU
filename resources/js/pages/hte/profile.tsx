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
    ArrowLeftIcon,
    EditIcon
} from 'lucide-react';
import { useState, useMemo, useEffect } from 'react';
import { SubmissionPrompt } from '@/components/hte/submission-prompt';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/hte/dashboard',
    },
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
    [key: string]: unknown;
}

export default function HTEProfilePage() {
    const { hte, showSubmissionPrompt } = usePage<HTEProfileProps>().props;
    
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

    // Add defensive programming to handle missing data
    if (!hte) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="HTE Profile" />
                <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 overflow-x-auto">
                    <div className="text-center py-8 text-muted-foreground">
                        <p>HTE profile not found.</p>
                    </div>
                </div>
            </AppLayout>
        );
    }

    // Update selected internship when status changes
    const handleStatusChange = (value: string) => {
        setSelectedStatus(value);
        setSelectedInternshipId(''); // Reset internship selection
    };

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
            
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 overflow-x-auto">
                {/* Success Message */}
                {showSuccessMessage && (
                    <div className="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg">
                        <div className="flex items-center gap-2">
                            <CheckCircle className="h-5 w-5" />
                            <span className="font-medium">Internship status updated successfully!</span>
                        </div>
                    </div>
                )}

                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="space-y-2">
                        <div className="flex items-center gap-3">
                            <Link href="/hte/dashboard">
                                <Button variant="ghost" size="sm" className="gap-2">
                                    <ArrowLeftIcon className="h-4 w-4" />
                                    Back to Dashboard
                                </Button>
                            </Link>
                        </div>
                        <h1 className="text-3xl font-bold tracking-tight">{hte.company_name}</h1>
                        <p className="text-muted-foreground">
                            Company Profile & Internship Management
                        </p>
                    </div>
                    
                    <div className="flex items-center gap-3">
                        <Badge variant={hte.is_active ? "default" : "secondary"}>
                            {hte.is_active ? (
                                <>
                                    <CheckCircle className="h-4 w-4 mr-1" />
                                    Active
                                </>
                            ) : (
                                <>
                                    <XCircle className="h-4 w-4 mr-1" />
                                    Inactive
                                </>
                            )}
                        </Badge>
                        <Link href="/hte/add-internship">
                            <Button className="gap-2">
                                <PlusIcon className="h-4 w-4" />
                                Add Internship
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Submission Prompt */}
                <SubmissionPrompt 
                    showPrompt={showSubmissionPrompt}
                    title="Complete Your Assessment Form"
                    description="Please complete the assessment form first to access all features and manage your internships effectively."
                    buttonText="Complete Form"
                    buttonHref="/form"
                />

                <div className="grid gap-6 md:grid-cols-3">
                    {/* Main Content */}
                    <div className="md:col-span-2 space-y-6">
                        {/* Company Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Building2 className="h-5 w-5" />
                                    Company Information
                                </CardTitle>
                                <CardDescription>Basic company details and contact information</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Company Name</label>
                                        <p className="text-lg font-semibold">{hte.company_name}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Status</label>
                                        <Badge variant={hte.is_active ? "default" : "secondary"} className="text-sm">
                                            {hte.is_active ? "Active" : "Inactive"}
                                        </Badge>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Address</label>
                                        <p className="text-sm text-gray-700">{hte.company_address}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Email</label>
                                        <p className="text-sm text-blue-600">{hte.company_email}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Contact Person</label>
                                        <p className="font-semibold">{hte.cperson_fname} {hte.cperson_lname}</p>
                                        <p className="text-sm text-gray-600">{hte.cperson_position}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Contact Number</label>
                                        <p className="text-sm text-gray-600">{hte.cperson_contactnum}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Internships Section with Dropdowns */}
                        {hte.internships && hte.internships.length > 0 ? (
                            <div className="space-y-6">
                                <div className="flex items-center justify-between">
                                    <h2 className="text-2xl font-bold">Internship Opportunities</h2>
                                    <p className="text-muted-foreground">
                                        {filteredInternships.length} position{filteredInternships.length !== 1 ? 's' : ''} available
                                    </p>
                                </div>

                                {/* Dropdowns */}
                                <div className="flex gap-4 items-center">
                                    <div className="space-y-2">
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

                                    <div className="space-y-2">
                                        <label className="text-sm font-medium text-muted-foreground">Select Internship</label>
                                        <Select value={selectedInternshipId} onValueChange={setSelectedInternshipId}>
                                            <SelectTrigger className="w-64">
                                                <SelectValue placeholder="Select an internship" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {filteredInternships.map((internship) => (
                                                    <SelectItem key={internship.id} value={internship.id.toString()}>
                                                        <div className="flex items-center justify-between w-full">
                                                            <span>{internship.position_title} - {internship.department}</span>
                                                            <Badge 
                                                                variant={internship.is_active ? "default" : "secondary"} 
                                                                className="ml-2 text-xs"
                                                            >
                                                                {internship.is_active ? "Active" : "Inactive"}
                                                            </Badge>
                                                        </div>
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
                                                <Badge variant={selectedInternship.is_active ? "default" : "secondary"}>
                                                    {selectedInternship.is_active ? (
                                                        <>
                                                            <CheckCircle className="h-4 w-4 mr-1" />
                                                            Active
                                                        </>
                                                    ) : (
                                                        <>
                                                            <XCircle className="h-4 w-4 mr-1" />
                                                            Inactive
                                                        </>
                                                    )}
                                                </Badge>
                                            </div>
                                        </CardHeader>
                                        <CardContent className="space-y-6">
                                            {/* Position Details Section */}
                                            <div className="border-2 border-gray-300 rounded-lg">
                                                <div className="bg-gray-100 px-4 py-2 border-b-2 border-gray-300">
                                                    <h3 className="font-semibold text-lg">Position Details</h3>
                                                </div>
                                                <div className="p-4">
                                                    <div className="grid grid-cols-2 gap-6">
                                                        <div className="space-y-3">
                                                            <div>
                                                                <label className="text-sm font-medium text-muted-foreground">Position Title</label>
                                                                <p className="text-lg font-semibold">{selectedInternship.position_title}</p>
                                                            </div>
                                                            <div>
                                                                <label className="text-sm font-medium text-muted-foreground">Department</label>
                                                                <p className="text-lg font-semibold">{selectedInternship.department}</p>
                                                            </div>
                                                            <div>
                                                                <label className="text-sm font-medium text-muted-foreground">Available Slots</label>
                                                                <p className="text-lg font-semibold flex items-center gap-2">
                                                                    <Users className="h-4 w-4" />
                                                                    {selectedInternship.slot_count} slot{selectedInternship.slot_count !== 1 ? 's' : ''}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div className="space-y-3">
                                                            <div>
                                                                <label className="text-sm font-medium text-muted-foreground">Duration</label>
                                                                <p className="text-lg font-semibold">{extractDuration(selectedInternship.placement_description)}</p>
                                                            </div>
                                                            <div>
                                                                <label className="text-sm font-medium text-muted-foreground">Start Date</label>
                                                                <p className="text-lg font-semibold flex items-center gap-2">
                                                                    <Calendar className="h-4 w-4" />
                                                                    {extractStartDate(selectedInternship.placement_description)}
                                                                </p>
                                                            </div>
                                                            <div>
                                                                <label className="text-sm font-medium text-muted-foreground">End Date</label>
                                                                <p className="text-lg font-semibold flex items-center gap-2">
                                                                    <Calendar className="h-4 w-4" />
                                                                    {extractEndDate(selectedInternship.placement_description)}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    {selectedInternship.placement_description && (
                                                        <div className="mt-4 pt-4 border-t border-gray-200">
                                                            <label className="text-sm font-medium text-muted-foreground">Description</label>
                                                            <p className="text-base text-gray-700 mt-1">{selectedInternship.placement_description}</p>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>

                                            {/* Assessment Criteria and Weights Section */}
                                            <div className="border-2 border-gray-300 rounded-lg">
                                                <div className="bg-gray-100 px-4 py-2 border-b-2 border-gray-300">
                                                    <h3 className="font-semibold text-lg">Assessment Criteria and Weights</h3>
                                                </div>
                                                <div className="p-4">
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
                                                                    
                                                                    // Prepare data for pie chart
                                                                    const pieData = weights.map((weight) => ({
                                                                        name: weight.subcategory.subcategory_name,
                                                                        value: weight.weight,
                                                                        color: `hsl(${Math.random() * 360}, 70%, 50%)`
                                                                    }));
                                                                    
                                                                    return (
                                                                        <div key={categoryName} className="border-2 border-gray-300 rounded-lg">
                                                                            <div className="bg-blue-50 px-4 py-2 border-b-2 border-gray-300">
                                                                                <h4 className="font-semibold text-lg text-blue-800">{categoryName}</h4>
                                                                            </div>
                                                                            <div className="p-4">
                                                                                <div className="grid grid-cols-2 gap-6">
                                                                                    {/* Weight Distribution */}
                                                                                    <div>
                                                                                        <h5 className="font-medium text-gray-700 mb-3">Weight Distribution</h5>
                                                                                        <div className="space-y-2">
                                                                                            {weights.map((weight) => (
                                                                                                <div key={weight.id} className="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                                                                                    <span className="font-medium text-sm">{weight.subcategory.subcategory_name}</span>
                                                                                                    <Badge variant="outline" className="font-mono text-xs">
                                                                                                        {weight.weight}%
                                                                                                    </Badge>
                                                                                                </div>
                                                                                            ))}
                                                                                            <div className="flex items-center justify-between p-2 bg-blue-100 rounded-lg border border-blue-200">
                                                                                                <span className="font-semibold text-blue-800">Total</span>
                                                                                                <Badge variant="default" className="font-mono text-xs bg-blue-600">
                                                                                                    {categoryTotal}%
                                                                                                </Badge>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    
                                                                                    {/* Pie Chart */}
                                                                                    <div className="flex items-center justify-center">
                                                                                        <PieChart 
                                                                                            data={pieData}
                                                                                            title={`${categoryName} Weights`}
                                                                                            totalWeight={categoryTotal}
                                                                                        />
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    );
                                                                })}
                                                            </div>
                                                        ) : (
                                                            <div className="text-center py-8 text-muted-foreground">
                                                                <p className="text-sm">No assessment criteria configured for this internship.</p>
                                                            </div>
                                                        );
                                                    })()}
                                                </div>
                                            </div>

                                            {/* Action Buttons */}
                                            <div className="flex justify-end gap-3 pt-4 border-t border-gray-200">
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
                                                        disabled={isToggling}
                                                    >
                                                        {isToggling ? 'Updating...' : 'Deactivate'}
                                                    </Button>
                                                ) : (
                                                    <Button 
                                                        variant="default" 
                                                        size="sm" 
                                                        className="gap-2"
                                                        onClick={() => handleToggleStatus(selectedInternship.id)}
                                                        disabled={isToggling}
                                                    >
                                                        {isToggling ? 'Updating...' : 'Activate'}
                                                    </Button>
                                                )}
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

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Company Status */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Company Status</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium">Status</span>
                                    <Badge variant={hte.is_active ? "default" : "secondary"}>
                                        {hte.is_active ? "Active" : "Inactive"}
                                    </Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm font-medium">Member Since</span>
                                    <span className="text-sm text-gray-600">
                                        {new Date(hte.created_at).toLocaleDateString('en-US', { 
                                            year: 'numeric', 
                                            month: 'long' 
                                        })}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Quick Stats */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Target className="h-5 w-5" />
                                    Quick Stats
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-primary">
                                        {hte.internships?.length || 0}
                                    </div>
                                    <p className="text-sm text-muted-foreground">Total Internships</p>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-green-600">
                                        {hte.internships?.filter(i => i.is_active).length || 0}
                                    </div>
                                    <p className="text-sm text-muted-foreground">Active Positions</p>
                                </div>
                                <div className="text-center">
                                    <div className="text-2xl font-bold text-blue-600">
                                        {hte.internships?.reduce((sum, i) => sum + i.slot_count, 0) || 0}
                                    </div>
                                    <p className="text-sm text-muted-foreground">Total Slots</p>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Quick Actions */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <Link href="/hte/add-internship" className="w-full">
                                    <Button className="w-full gap-2">
                                        <PlusIcon className="h-4 w-4" />
                                        Add Internship
                                    </Button>
                                </Link>
                                <Link href="/hte/dashboard" className="w-full">
                                    <Button variant="ghost" className="w-full gap-2">
                                        <ArrowLeftIcon className="h-4 w-4" />
                                        Back to Dashboard
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
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
