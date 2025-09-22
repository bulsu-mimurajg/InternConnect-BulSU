import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { 
    Building2, 
    MapPin, 
    Phone, 
    Mail, 
    Users, 
    Calendar,
    Search,
    Plus,
    Filter,
    MoreHorizontal,
    Edit,
    Eye
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { type BreadcrumbItem } from '@/types';

interface HTE {
    id: number;
    company_name: string;
    contact_person: string;
    email: string;
    phone: string;
    address: string;
    status: 'active' | 'inactive' | 'pending';
    total_students: number;
    max_capacity: number;
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Host Training Establishment',
        href: '/hte',
    },
];

// Mock data - replace with actual data from props
const mockHTEs: HTE[] = [
    {
        id: 1,
        company_name: 'Tech Solutions Inc.',
        contact_person: 'John Smith',
        email: 'john@techsolutions.com',
        phone: '+1 (555) 123-4567',
        address: '123 Business Ave, Tech City, TC 12345',
        status: 'active',
        total_students: 15,
        max_capacity: 20,
        created_at: '2024-01-15',
    },
    {
        id: 2,
        company_name: 'Digital Innovations Ltd.',
        contact_person: 'Sarah Johnson',
        email: 'sarah@digitalinnovations.com',
        phone: '+1 (555) 987-6543',
        address: '456 Innovation St, Digital City, DC 67890',
        status: 'active',
        total_students: 8,
        max_capacity: 15,
        created_at: '2024-02-20',
    },
    {
        id: 3,
        company_name: 'Future Systems Corp.',
        contact_person: 'Michael Brown',
        email: 'michael@futuresystems.com',
        phone: '+1 (555) 456-7890',
        address: '789 Future Blvd, System City, SC 13579',
        status: 'pending',
        total_students: 0,
        max_capacity: 25,
        created_at: '2024-03-10',
    },
];

export default function Hte() {
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState<string>('all');

    const filteredHTEs = mockHTEs.filter(hte => {
        const matchesSearch = hte.company_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            hte.contact_person.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            hte.email.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = statusFilter === 'all' || hte.status === statusFilter;
        return matchesSearch && matchesStatus;
    });

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'active':
                return <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Active</Badge>;
            case 'inactive':
                return <Badge variant="secondary">Inactive</Badge>;
            case 'pending':
                return <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pending</Badge>;
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    const getCapacityPercentage = (current: number, max: number) => {
        return Math.round((current / max) * 100);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Host Training Establishment" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Header Section */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div className="space-y-1">
                        <h1 className="text-2xl font-bold tracking-tight">Host Training Establishments</h1>
                        <p className="text-muted-foreground">
                            Manage and monitor partner companies for student internships
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" size="sm">
                            <Filter className="mr-2 h-4 w-4" />
                            Filter
                        </Button>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Add HTE
                        </Button>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total HTEs</CardTitle>
                            <Building2 className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{mockHTEs.length}</div>
                            <p className="text-xs text-muted-foreground">
                                {mockHTEs.filter(h => h.status === 'active').length} active
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Students</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {mockHTEs.reduce((sum, h) => sum + h.total_students, 0)}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Across all HTEs
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Available Slots</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {mockHTEs.reduce((sum, h) => sum + (h.max_capacity - h.total_students), 0)}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Remaining capacity
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending Approval</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {mockHTEs.filter(h => h.status === 'pending').length}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Awaiting review
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Search and Filter */}
                <Card>
                    <CardHeader>
                        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div className="flex flex-col gap-2 md:flex-row md:items-center">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        placeholder="Search HTEs..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-9 w-full md:w-64"
                                    />
                                </div>
                                <select
                                    value={statusFilter}
                                    onChange={(e) => setStatusFilter(e.target.value)}
                                    className="h-9 rounded-md border border-input bg-background px-3 py-1 text-sm"
                                >
                                    <option value="all">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                            <div className="text-sm text-muted-foreground">
                                {filteredHTEs.length} of {mockHTEs.length} HTEs
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {filteredHTEs.map((hte) => (
                                <Card key={hte.id} className="transition-all hover:shadow-md">
                                    <CardContent className="p-4 md:p-6">
                                        <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                            <div className="flex-1 space-y-3">
                                                <div className="flex flex-col gap-2 md:flex-row md:items-center md:gap-4">
                                                    <h3 className="text-lg font-semibold">{hte.company_name}</h3>
                                                    {getStatusBadge(hte.status)}
                                                </div>
                                                
                                                <div className="grid gap-2 text-sm text-muted-foreground md:grid-cols-2">
                                                    <div className="flex items-center gap-2">
                                                        <Users className="h-4 w-4" />
                                                        <span>{hte.contact_person}</span>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <Mail className="h-4 w-4" />
                                                        <span>{hte.email}</span>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <Phone className="h-4 w-4" />
                                                        <span>{hte.phone}</span>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <MapPin className="h-4 w-4" />
                                                        <span className="truncate">{hte.address}</span>
                                                    </div>
                                                </div>

                                                <div className="flex flex-col gap-2 md:flex-row md:items-center md:gap-4">
                                                    <div className="flex items-center gap-2 text-sm">
                                                        <span className="font-medium">Capacity:</span>
                                                        <span>{hte.total_students}/{hte.max_capacity} students</span>
                                                        <div className="flex-1 max-w-24">
                                                            <div className="h-2 bg-muted rounded-full overflow-hidden">
                                                                <div 
                                                                    className="h-full bg-primary transition-all"
                                                                    style={{ width: `${getCapacityPercentage(hte.total_students, hte.max_capacity)}%` }}
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="text-sm text-muted-foreground">
                                                        Added: {new Date(hte.created_at).toLocaleDateString()}
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="flex gap-2">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="sm">
                                                            <MoreHorizontal className="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem>
                                                            <Eye className="mr-2 h-4 w-4" />
                                                            View Details
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem>
                                                            <Edit className="mr-2 h-4 w-4" />
                                                            Edit HTE
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}

                            {filteredHTEs.length === 0 && (
                                <div className="text-center py-8">
                                    <Building2 className="mx-auto h-12 w-12 text-muted-foreground" />
                                    <h3 className="mt-2 text-sm font-semibold">No HTEs found</h3>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {searchTerm || statusFilter !== 'all' 
                                            ? 'Try adjusting your search or filter criteria.'
                                            : 'Get started by adding your first Host Training Establishment.'
                                        }
                                    </p>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
