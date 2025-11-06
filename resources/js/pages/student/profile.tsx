import { useState, useMemo, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import {
    User,
    CheckCircle,
    Clock,
    Link,
    NotepadTextIcon,
    XCircle,
    Award,
    ChevronDownIcon
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

interface AdditionalInfo {
    info_name: string;
    info_value: string;
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
    is_submit: boolean;
}

interface Choice {
    id: number;
    choice_text: string;
    is_correct: boolean;
}

interface Question {
    id: number;
    question: string;
    subcategory_id: number;
    subcategory_name: string;
    category_id: number;
    category_name: string;
    choices: Choice[];
    student_choice_id?: number | null;
    student_choice_text?: string | null;
    is_correct?: boolean | null;
    correct_choice_id?: number | null;
    correct_choice_text?: string | null;
}

interface ProfileProps {
    student: Student | null;
    categories: Category[];
    additional_info?: AdditionalInfo[];
    hasSubmitted?: boolean;
    questions?: Question[];
}

export default function Profile({ student, categories, additional_info = [], hasSubmitted = true, questions = [] }: ProfileProps) {
    const [activeTab, setActiveTab] = useState('basic');
    // Track which categories are expanded (default all open)
    const [expandedCategories, setExpandedCategories] = useState<Record<number, boolean>>({});
    // Track which subcategories are expanded (default all open)
    const [expandedSubcategories, setExpandedSubcategories] = useState<Record<number, boolean>>({});

    // Initialize expanded state for categories when categories are available
    useEffect(() => {
        if (categories.length > 0) {
            setExpandedCategories(prev => {
                // Only initialize if not already set
                if (Object.keys(prev).length === 0) {
                    const initial: Record<number, boolean> = {};
                    categories.forEach(cat => {
                        initial[cat.id] = true; // All open by default
                    });
                    return initial;
                }
                // Add any new categories that weren't in the previous state
                const updated = { ...prev };
                let hasNew = false;
                categories.forEach(cat => {
                    if (!(cat.id in updated)) {
                        updated[cat.id] = true;
                        hasNew = true;
                    }
                });
                return hasNew ? updated : prev;
            });
        }
    }, [categories]);

    // Initialize expanded state for subcategories when categories are available
    useEffect(() => {
        if (categories.length > 0) {
            setExpandedSubcategories(prev => {
                // Only initialize if not already set
                if (Object.keys(prev).length === 0) {
                    const initial: Record<number, boolean> = {};
                    categories.forEach(cat => {
                        cat.subcategories.forEach(sub => {
                            initial[sub.id] = true; // All open by default
                        });
                    });
                    return initial;
                }
                // Add any new subcategories that weren't in the previous state
                const updated = { ...prev };
                let hasNew = false;
                categories.forEach(cat => {
                    cat.subcategories.forEach(sub => {
                        if (!(sub.id in updated)) {
                            updated[sub.id] = true;
                            hasNew = true;
                        }
                    });
                });
                return hasNew ? updated : prev;
            });
        }
    }, [categories]);

    // Navigation items - only Basic Information and My Assessment
    const navigationItems = useMemo(() => {
        return [
            { id: 'basic', label: 'Basic Information', icon: User },
            { id: 'assessment', label: 'My Assessment', icon: NotepadTextIcon }
        ];
    }, []);

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

    const renderBasicInformation = () => (
        <div className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        Assessment Status
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="space-y-3">
                        <div className="flex items-center gap-2">
                            {hasSubmitted ? (
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
                        {!hasSubmitted && (
                            <div className="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p className="text-sm text-yellow-800">
                                    Complete the assessment to view your detailed scores and performance analysis.
                                </p>
                                <a
                                    href="/assessment"
                                    className="inline-flex items-center mt-2 text-sm text-yellow-700 hover:text-yellow-800 font-medium"
                                >
                                    Take Assessment Now →
                                </a>
                            </div>
                        )}
                    </div>
                </CardContent>
            </Card>

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
                            <label className="text-sm font-medium text-muted-foreground">Phone</label>
                            <p className="text-lg">{student.phone || 'Not provided'}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Additional Information Card */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <Link className="h-5 w-5" />
                        Additional Information
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    {additional_info.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {additional_info.map((info, index) => (
                                <div key={index}>
                                    <label className="text-sm font-medium text-muted-foreground">
                                        {info.info_name}
                                    </label>
                                    <p className="text-lg break-all">
                                        {info.info_value || 'Not provided'}
                                    </p>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-6">
                            <div className="mx-auto w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                <Link className="h-6 w-6 text-gray-400" />
                            </div>
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                No Additional Information
                            </h3>
                            <p className="text-muted-foreground text-sm">
                                {hasSubmitted
                                    ? "You haven't provided any additional information yet."
                                    : "Complete the assessment to provide additional information."
                                }
                            </p>
                            {!hasSubmitted && (
                                <div className="mt-3">
                                    <a
                                        href="/assessment"
                                        className="inline-flex items-center px-3 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors text-sm"
                                    >
                                        Take Assessment
                                    </a>
                                </div>
                            )}
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );

    // Calculate assessment statistics
    const assessmentStats = useMemo(() => {
        if (!hasSubmitted || categories.length === 0) {
            return {
                rawScore: 0,
                totalPossibleScore: 0,
                percentageAverage: 0,
                categoryAverages: []
            };
        }

        // Calculate raw score by counting correct answers (each question is worth 1 point)
        let correctAnswers = 0;
        let totalQuestions = 0;
        const categoryAverages: Array<{ name: string; average: number }> = [];

        // Count correct answers from questions array
        if (questions.length > 0) {
            questions.forEach(question => {
                totalQuestions++;
                if (question.is_correct === true) {
                    correctAnswers++;
                }
            });
        }

        // Also calculate category averages for percentage display
        categories.forEach(category => {
            if (category.subcategories.length > 0) {
                const categoryTotal = category.subcategories.reduce((sum, sub) => sum + sub.score, 0);
                const categoryAverage = categoryTotal / category.subcategories.length;
                categoryAverages.push({
                    name: category.name,
                    average: categoryAverage
                });
            }
        });

        // Calculate percentage average across all categories
        const percentageAverage = categoryAverages.length > 0
            ? (categoryAverages.reduce((sum, cat) => sum + cat.average, 0) / categoryAverages.length) / 5 * 100
            : 0;

        return {
            rawScore: correctAnswers,
            totalPossibleScore: totalQuestions,
            percentageAverage: Math.round(percentageAverage * 100) / 100,
            categoryAverages
        };
    }, [categories, hasSubmitted, questions]);

    const renderAssessmentSection = () => {
        // If student hasn't submitted assessment, show message
        if (!hasSubmitted) {
            return (
                <Card>
                    <CardContent className="p-6">
                        <div className="text-center space-y-4">
                            <div className="mx-auto w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                                <NotepadTextIcon className="h-8 w-8 text-yellow-600" />
                            </div>
                            <div>
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Assessment Not Completed
                                </h3>
                                <p className="text-muted-foreground mt-2">
                                    You haven't completed the assessment yet. Please take the assessment first to view your scores.
                                </p>
                                <div className="mt-4">
                                    <a
                                        href="/assessment"
                                        className="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
                                    >
                                        Take Assessment
                                    </a>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            );
        }

            return (
            <div className="space-y-6">
                {/* Raw Score Card */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Award className="h-5 w-5" />
                            Raw Score
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-baseline gap-2">
                            <span className="text-4xl font-bold text-primary">
                                {assessmentStats.rawScore}
                            </span>
                            <span className="text-xl text-muted-foreground">
                                / {assessmentStats.totalPossibleScore}
                            </span>
                        </div>
                        <p className="text-sm text-muted-foreground mt-2">
                            Total score across all assessment questions
                        </p>
                    </CardContent>
                </Card>

                {/* Percentage Average Card */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <CheckCircle className="h-5 w-5" />
                            Percentage Average
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-baseline gap-2">
                            <span className="text-4xl font-bold text-primary">
                                {assessmentStats.percentageAverage}%
                            </span>
                        </div>
                        <p className="text-sm text-muted-foreground mt-2">
                            Average score percentage across all categories
                        </p>
                        {assessmentStats.categoryAverages.length > 0 && (
                            <div className="mt-4 space-y-2">
                                <p className="text-sm font-medium">Category Breakdown:</p>
                                {assessmentStats.categoryAverages.map((cat, index) => (
                                    <div key={index} className="flex justify-between items-center">
                                        <span className="text-sm text-muted-foreground">{cat.name}</span>
                                        <span className="text-sm font-medium">
                                            {Math.round((cat.average / 5) * 100)}%
                                        </span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Assessment Details Card */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <NotepadTextIcon className="h-5 w-5" />
                            Assessment Details
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {questions.length > 0 ? (
                            <div className="space-y-4">
                                {categories.map((category) => {
                                    // Filter questions for this category
                                    const categoryQuestions = questions.filter(
                                        q => q.category_id === category.id
                                    );

                                    if (categoryQuestions.length === 0) return null;

                                    const isCategoryOpen = expandedCategories[category.id] ?? true;

                                    return (
                                        <Collapsible
                                            key={category.id}
                                            open={isCategoryOpen}
                                            onOpenChange={(open) => setExpandedCategories(prev => ({ ...prev, [category.id]: open }))}
                                        >
                                            <div className="border rounded-lg overflow-hidden">
                                                <CollapsibleTrigger className="w-full">
                                                    <div className="flex items-center justify-between p-4 hover:bg-muted/50 transition-colors">
                                                        <h4 className="font-semibold text-lg">{category.name}</h4>
                                                        <ChevronDownIcon 
                                                            className={`h-5 w-5 text-muted-foreground transition-transform duration-200 ${
                                                                isCategoryOpen ? 'rotate-180' : ''
                                                            }`}
                                                        />
                                                    </div>
                                                </CollapsibleTrigger>
                                                
                                                <CollapsibleContent>
                                                    <div className="px-8 py-4 space-y-4">
                                                        {category.subcategories.map((subcategory) => {
                                                            // Filter questions for this subcategory
                                                            const subcategoryQuestions = categoryQuestions.filter(
                                                                q => q.subcategory_id === subcategory.id
                                                            );

                                                            if (subcategoryQuestions.length === 0) return null;

                                                            const isSubcategoryOpen = expandedSubcategories[subcategory.id] ?? true;

                                                            return (
                                                                <Collapsible
                                                                    key={subcategory.id}
                                                                    open={isSubcategoryOpen}
                                                                    onOpenChange={(open) => setExpandedSubcategories(prev => ({ ...prev, [subcategory.id]: open }))}
                                                                >
                                                                    <div className="border rounded-lg overflow-hidden bg-muted/30">
                                                                        <CollapsibleTrigger className="w-full">
                                                                            <div className="flex items-center justify-between p-3 hover:bg-muted/50 transition-colors">
                                                                                <h5 className="font-medium text-base">{subcategory.name}</h5>
                                                                                <ChevronDownIcon 
                                                                                    className={`h-4 w-4 text-muted-foreground transition-transform duration-200 ${
                                                                                        isSubcategoryOpen ? 'rotate-180' : ''
                                                                                    }`}
                                                                                />
                                                                            </div>
                                                                        </CollapsibleTrigger>
                                                                        
                                                                        <CollapsibleContent>
                                                                            <div className="px-8 py-4 pb-3 space-y-3">
                                                                                {subcategoryQuestions.map((question) => {
                                                                                    const hasAnswer = question.student_choice_id !== null && question.student_choice_id !== undefined;
                                                                                    const isCorrect = question.is_correct === true;
                                                                                    const isWrong = hasAnswer && question.is_correct === false;
                                                                                    
                                                                                    return (
                                                                                        <div key={question.id} className="rounded-lg border bg-background border-border p-4">
                                                                                            <div className="flex items-start gap-2 mb-3">
                                                                                                {isWrong && (
                                                                                                    <XCircle className="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" />
                                                                                                )}
                                                                                                {isCorrect && (
                                                                                                    <CheckCircle className="h-5 w-5 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" />
                                                                                                )}
                                                                                                {!hasAnswer && (
                                                                                                    <Clock className="h-5 w-5 text-muted-foreground mt-0.5 flex-shrink-0" />
                                                                                                )}
                                                                                                <p className="font-medium text-sm flex-1">{question.question}</p>
                                                                                            </div>
                                                                                            
                                                                                            <div className="space-y-2">
                                                                                                <p className="text-xs font-medium text-muted-foreground mb-2">Choices:</p>
                                                                                                {question.choices && question.choices.length > 0 ? (
                                                                                                    <div className="space-y-2">
                                                                                                        {question.choices.map((choice) => {
                                                                                                            // Use loose comparison to handle string/number mismatches
                                                                                                            const isStudentChoice = hasAnswer && (
                                                                                                                choice.id == question.student_choice_id || 
                                                                                                                String(choice.id) === String(question.student_choice_id)
                                                                                                            );
                                                                                                            const isCorrectChoice = choice.is_correct === true;
                                                                                                            
                                                                                                            return (
                                                                                                                <div
                                                                                                                    key={choice.id}
                                                                                                                    className={`p-3 rounded-md border ${
                                                                                                                        isCorrectChoice
                                                                                                                            ? 'bg-green-100 dark:bg-green-900/30 border-green-300 dark:border-green-700'
                                                                                                                            : isStudentChoice && isWrong
                                                                                                                            ? 'bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-700'
                                                                                                                            : isStudentChoice && isCorrect
                                                                                                                            ? 'bg-green-100 dark:bg-green-900/30 border-green-300 dark:border-green-700'
                                                                                                                            : 'bg-background border-border'
                                                                                                                    }`}
                                                                                                                >
                                                                                                                    <div className="flex items-start gap-2">
                                                                                                                        {isCorrectChoice && (
                                                                                                                            <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" />
                                                                                                                        )}
                                                                                                                        {isStudentChoice && isWrong && !isCorrectChoice && (
                                                                                                                            <XCircle className="h-4 w-4 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" />
                                                                                                                        )}
                                                                                                                        <span className={`text-sm ${
                                                                                                                            isCorrectChoice
                                                                                                                                ? 'text-green-800 dark:text-green-200 font-medium'
                                                                                                                                : isStudentChoice && isWrong
                                                                                                                                ? 'text-red-800 dark:text-red-200 font-medium'
                                                                                                                                : isStudentChoice && isCorrect
                                                                                                                                ? 'text-green-800 dark:text-green-200 font-medium'
                                                                                                                                : 'text-foreground'
                                                                                                                        }`}>
                                                                                                                            {choice.choice_text}
                                                                                                                        </span>
                                                                                                                        {isCorrectChoice && (
                                                                                                                            <span className="ml-auto text-xs text-green-700 dark:text-green-300 font-medium">
                                                                                                                                Correct Answer
                                                                                                                            </span>
                                                                                                                        )}
                                                                                                                        {isStudentChoice && isWrong && (
                                                                                                                            <span className="ml-auto text-xs font-medium text-red-700 dark:text-red-300">
                                                                                                                                Your Answer
                                                                                                                            </span>
                                                                                                                        )}
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            );
                                                                                                        })}
                                                                                                        
                                                                                                        {/* Show student's answer explicitly if it's not found in choices (fallback) */}
                                                                                                        {hasAnswer && isWrong && question.student_choice_text && 
                                                                                                            !question.choices.some(c => c.id == question.student_choice_id) && (
                                                                                                            <div className="p-3 rounded-md border bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-700">
                                                                                                                <div className="flex items-start gap-2">
                                                                                                                    <XCircle className="h-4 w-4 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" />
                                                                                                                    <span className="text-sm text-red-800 dark:text-red-200 font-medium">
                                                                                                                        {question.student_choice_text}
                                                                                                                    </span>
                                                                                                                    <span className="ml-auto text-xs text-red-700 dark:text-red-300 font-medium">
                                                                                                                        Your Answer (Not Found)
                                                                                                                    </span>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        )}
                                                                                                    </div>
                                                                                                ) : (
                                                                                                    <p className="text-sm text-muted-foreground italic">No choices available</p>
                                                                                                )}
                                                                                            </div>
                                                                                            
                                                                                            {!hasAnswer && (
                                                                                                <div className="pt-2 mt-3 border-t">
                                                                                                    <p className="text-sm text-muted-foreground italic">No answer provided</p>
                                                                                                </div>
                                                                                            )}
                                                                                        </div>
                                                                                    );
                                                                                })}
                                                                            </div>
                                                                        </CollapsibleContent>
                                                                    </div>
                                                                </Collapsible>
                                                            );
                                                        })}
                                                    </div>
                                                </CollapsibleContent>
                                            </div>
                                        </Collapsible>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="text-center py-6">
                                <p className="text-muted-foreground">No assessment questions available</p>
                        </div>
                        )}
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
                        <div className="flex flex-wrap justify-center gap-2">
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
                    {activeTab === 'assessment' && renderAssessmentSection()}
                </div>
            </div>
        </AppLayout>
    );
}
