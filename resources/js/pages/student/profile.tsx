import { useState } from 'react';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import { RadarChart } from '@/components/ui/radar-chart';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { 
    User, 
    Phone, 
    MapPin, 
    Calendar, 
    GraduationCap, 
    BookOpen,
    CheckCircle,
    Clock
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile',
        href: '/profile',
    },
];

interface Subcategory {
    id: number;
    name: string;
    score: number;
}

interface Category {
    id: number;
    name: string;
    subcategories: Subcategory[];
}

interface Student {
    id: number;
    student_number: string;
    first_name: string;
    last_name: string;
    middle_name: string | null;
    phone: string | null;
    section: string | null;
    specialization: string | null;
    address: string | null;
    birth_date: string | null;
    is_submit: boolean;
}

interface ProfileProps {
    student: Student | null;
    categories: Category[];
}

const navigationItems = [
    { id: 'basic', label: 'Basic Information', icon: User },
    { id: 'language', label: 'Language Proficiency', icon: BookOpen },
    { id: 'technical', label: 'Technical Skills', icon: GraduationCap },
    { id: 'soft', label: 'Soft Skills', icon: CheckCircle },
];

export default function Profile({ student, categories }: ProfileProps) {
    const [activeTab, setActiveTab] = useState('basic');

    if (!student) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Profile" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <p className="text-muted-foreground">No student profile found.</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    const languageCategory = categories.find(cat => cat.name === 'Language Proficiency');
    const technicalCategory = categories.find(cat => cat.name === 'Technical Skill');
    const softCategory = categories.find(cat => cat.name === 'Soft Skill');

    const formatDate = (dateString: string | null) => {
        if (!dateString) return 'Not provided';
        return new Date(dateString).toLocaleDateString();
    };

    const getScoreColor = (score: number) => {
        if (score >= 4) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        if (score >= 3) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    };

    const renderBasicInformation = () => (
        <div className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <User className="h-5 w-5" />
                        Personal Information
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="text-sm font-medium text-muted-foreground">Full Name</label>
                            <p className="text-lg font-semibold">
                                {student.first_name} {student.middle_name} {student.last_name}
                            </p>
                        </div>
                        <div>
                            <label className="text-sm font-medium text-muted-foreground">Student Number</label>
                            <p className="text-lg font-semibold">{student.student_number}</p>
                        </div>
                        <div>
                            <label className="text-sm font-medium text-muted-foreground">Section</label>
                            <p className="text-lg">{student.section || 'Not provided'}</p>
                        </div>
                        <div>
                            <label className="text-sm font-medium text-muted-foreground">Specialization</label>
                            <p className="text-lg">{student.specialization || 'Not provided'}</p>
                        </div>
                        <div>
                            <label className="text-sm font-medium text-muted-foreground">Birth Date</label>
                            <p className="text-lg">{formatDate(student.birth_date)}</p>
                        </div>
                        <div>
                            <label className="text-sm font-medium text-muted-foreground">Phone</label>
                            <p className="text-lg">{student.phone || 'Not provided'}</p>
                        </div>
                    </div>
                    <div>
                        <label className="text-sm font-medium text-muted-foreground">Address</label>
                        <p className="text-lg">{student.address || 'Not provided'}</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <CheckCircle className="h-5 w-5" />
                        Assessment Status
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="flex items-center gap-2">
                        {student.is_submit ? (
                            <>
                                <CheckCircle className="h-5 w-5 text-green-600" />
                                <span className="text-green-600 font-medium">Assessment Completed</span>
                            </>
                        ) : (
                            <>
                                <Clock className="h-5 w-5 text-yellow-600" />
                                <span className="text-yellow-600 font-medium">Assessment Pending</span>
                            </>
                        )}
                    </div>
                </CardContent>
            </Card>
        </div>
    );

    const renderCategorySection = (category: Category | undefined, title: string) => {
        if (!category || category.subcategories.length === 0) {
            return (
                <Card>
                    <CardContent className="p-6">
                        <div className="text-center">
                            <p className="text-muted-foreground">No {title.toLowerCase()} data available.</p>
                        </div>
                    </CardContent>
                </Card>
            );
        }

        const chartData = category.subcategories.map(sub => ({
            subject: sub.name,
            score: sub.score,
            fullMark: 5,
        }));

        return (
            <div className="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>{title} Overview</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <RadarChart data={chartData} title={`${title} Radar Chart`} />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{title} Scores</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="text-left p-2 font-medium">Subcategory</th>
                                        <th className="text-right p-2 font-medium">Score</th>
                                        <th className="text-center p-2 font-medium">Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {category.subcategories.map((subcategory) => (
                                        <tr key={subcategory.id} className="border-b">
                                            <td className="p-2">{subcategory.name}</td>
                                            <td className="p-2 text-right">{subcategory.score}/5</td>
                                            <td className="p-2 text-center">
                                                <Badge className={getScoreColor(subcategory.score)}>
                                                    {subcategory.score >= 4 ? 'Excellent' : 
                                                     subcategory.score >= 3 ? 'Good' : 'Needs Improvement'}
                                                </Badge>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Sub Navigation */}
                <Card>
                    <CardContent className="p-0">
                        <div className="flex flex-wrap gap-1 p-4">
                            {navigationItems.map((item) => {
                                const Icon = item.icon;
                                return (
                                    <button
                                        key={item.id}
                                        onClick={() => setActiveTab(item.id)}
                                        className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                                            activeTab === item.id
                                                ? 'bg-primary text-primary-foreground'
                                                : 'text-muted-foreground hover:text-foreground hover:bg-muted'
                                        }`}
                                    >
                                        <Icon className="h-4 w-4" />
                                        {item.label}
                                    </button>
                                );
                            })}
                        </div>
                    </CardContent>
                </Card>

                {/* Content */}
                <div className="flex-1">
                    {activeTab === 'basic' && renderBasicInformation()}
                    {activeTab === 'language' && renderCategorySection(languageCategory, 'Language Proficiency')}
                    {activeTab === 'technical' && renderCategorySection(technicalCategory, 'Technical Skills')}
                    {activeTab === 'soft' && renderCategorySection(softCategory, 'Soft Skills')}
                </div>
            </div>
        </AppLayout>
    );
}
