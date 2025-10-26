import { FormControl, FormField, FormItem } from '@/components/ui/form';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { useFormContext } from 'react-hook-form';
import React, { useCallback, useMemo, useState, useEffect } from 'react';
import { ChevronDownIcon } from '@radix-ui/react-icons';
import { usePage } from '@inertiajs/react';
import { type SharedData } from '@/types';

interface Category {
    id: number;
    category_name: string;
    subCategories: SubCategory[];
}

interface SubCategory {
    id: number;
    subcategory_name: string;
    questions: Question[];
}

interface Question {
    id: number;
    question: string;
    access: string;
    is_active: boolean;
}

interface CriteriaProps {
    categories: Category[];
    loading: boolean;
    expandedCategories: Set<number>;
    expandedSubcategories: Set<number>;
    setExpandedCategories: React.Dispatch<React.SetStateAction<Set<number>>>;
    setExpandedSubcategories: React.Dispatch<React.SetStateAction<Set<number>>>;
}

// Likert Scale Options
const LIKERT_SCALE = [
    { value: '1', label: 'Strongly Disagree', shortLabel: '1' },
    { value: '2', label: 'Disagree', shortLabel: '2' },
    { value: '3', label: 'Neutral', shortLabel: '3' },
    { value: '4', label: 'Agree', shortLabel: '4' },
    { value: '5', label: 'Strongly Agree', shortLabel: '5' },
];

export default function Criteria({
    categories,
    loading,
    expandedCategories,
    expandedSubcategories,
    setExpandedCategories,
    setExpandedSubcategories,
}: CriteriaProps) {
    console.log('🔴 [Criteria Component] RENDERED - Categories:', categories.length);

    const { control, watch, setValue, getValues } = useFormContext();
    const { auth } = usePage<SharedData>().props;

    console.log('🔴 [Criteria Component] Form context available:', {
        hasWatch: !!watch,
        hasSetValue: !!setValue,
        hasGetValues: !!getValues
    });

    // Track assessment responses with state that updates on form changes
    const [assessmentResponses, setAssessmentResponses] = useState<Record<string, number>>(() => {
        // Initialize state with current form values
        const initial = getValues('assessmentResponses') || {};
        console.log('🔴 [Criteria Component] Initial state from form:', initial);
        return initial;
    });

    // Subscribe to form changes using watch - only subscribe once
    useEffect(() => {
        console.log('🟢 [Criteria] SUBSCRIBING to form changes');
        const subscription = watch((formValues) => {
            const responses = formValues.assessmentResponses || {};
            console.log('🟡 [Criteria] Form changed! New responses:', responses);
            console.log('🟡 [Criteria] Total keys in responses:', Object.keys(responses).length);
            setAssessmentResponses(responses);
        });

        console.log('🟢 [Criteria] Current form value on mount:', getValues('assessmentResponses'));

        return () => {
            console.log('🔴 [Criteria] UNSUBSCRIBING from form changes');
            subscription.unsubscribe();
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const toggleCategory = useCallback(
        (categoryId: number) => {
            setExpandedCategories((prev) => {
                const newExpanded = new Set(prev);
                if (newExpanded.has(categoryId)) {
                    newExpanded.delete(categoryId);
                } else {
                    newExpanded.add(categoryId);
                }
                return newExpanded;
            });
        },
        [setExpandedCategories],
    );

    const toggleSubcategory = useCallback(
        (subcategoryId: number) => {
            setExpandedSubcategories((prev) => {
                const newExpanded = new Set(prev);
                if (newExpanded.has(subcategoryId)) {
                    newExpanded.delete(subcategoryId);
                } else {
                    newExpanded.add(subcategoryId);
                }
                return newExpanded;
            });
        },
        [setExpandedSubcategories],
    );

    // Calculate progress for each category
    const calculateCategoryProgress = useCallback(
        (category: Category) => {
            let totalQuestions = 0;
            let answeredQuestions = 0;

            category.subCategories.forEach((subcat) => {
                subcat.questions.forEach((question) => {
                    totalQuestions++;
                    if (assessmentResponses[`question_${question.id}`]) {
                        answeredQuestions++;
                    }
                });
            });

            return {
                total: totalQuestions,
                answered: answeredQuestions,
                percentage: totalQuestions > 0 ? Math.round((answeredQuestions / totalQuestions) * 100) : 0,
            };
        },
        [assessmentResponses],
    );

    // Calculate overall progress
    const overallProgress = useMemo(() => {
        console.log('📊 [Progress] Calculating overall progress...');
        console.log('📊 [Progress] Current assessmentResponses:', assessmentResponses);
        console.log('📊 [Progress] Keys in assessmentResponses:', Object.keys(assessmentResponses).length);

        let totalQuestions = 0;
        let answeredQuestions = 0;

        categories.forEach((category) => {
            category.subCategories.forEach((subcat) => {
                subcat.questions.forEach((question) => {
                    totalQuestions++;
                    if (assessmentResponses[`question_${question.id}`]) {
                        answeredQuestions++;
                    }
                });
            });
        });

        const progress = {
            total: totalQuestions,
            answered: answeredQuestions,
            percentage: totalQuestions > 0 ? Math.round((answeredQuestions / totalQuestions) * 100) : 0,
            isComplete: totalQuestions > 0 && answeredQuestions === totalQuestions,
        };

        console.log('📊 [Progress] RESULT:', progress);

        return progress;
    }, [categories, assessmentResponses]);

    // Expand all categories
    const expandAll = useCallback(() => {
        const allCategoryIds = categories.map((cat) => cat.id);
        const allSubcategoryIds = categories.flatMap((cat) => cat.subCategories.map((sub) => sub.id));
        setExpandedCategories(new Set(allCategoryIds));
        setExpandedSubcategories(new Set(allSubcategoryIds));
    }, [categories, setExpandedCategories, setExpandedSubcategories]);

    // Collapse all categories
    const collapseAll = useCallback(() => {
        setExpandedCategories(new Set());
        setExpandedSubcategories(new Set());
    }, [setExpandedCategories, setExpandedSubcategories]);

    // Find and scroll to first unanswered question
    const scrollToFirstUnanswered = useCallback(() => {
        console.log('🔍 [Scroll] Looking for first unanswered question...');

        for (const category of categories) {
            for (const subcategory of category.subCategories) {
                for (const question of subcategory.questions) {
                    if (!assessmentResponses[`question_${question.id}`]) {
                        console.log('🔍 [Scroll] Found unanswered question:', question.id);

                        // Expand the category and subcategory
                        setExpandedCategories(prev => new Set([...prev, category.id]));
                        setExpandedSubcategories(prev => new Set([...prev, subcategory.id]));

                        // Wait for DOM to update, then scroll
                        setTimeout(() => {
                            const questionElement = document.getElementById(`question_${question.id}`);
                            if (questionElement) {
                                console.log('🔍 [Scroll] Scrolling to question element');
                                questionElement.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });

                                // Add shake and highlight animation
                                questionElement.classList.add('animate-shake', 'highlight-question');

                                // Remove animation classes after animation completes
                                setTimeout(() => {
                                    questionElement.classList.remove('animate-shake');
                                    setTimeout(() => {
                                        questionElement.classList.remove('highlight-question');
                                    }, 2000);
                                }, 600);
                            }
                        }, 300);

                        return true; // Found unanswered question
                    }
                }
            }
        }

        console.log('🔍 [Scroll] All questions answered!');
        return false; // All questions answered
    }, [categories, assessmentResponses, setExpandedCategories, setExpandedSubcategories]);

    // Expose scrollToFirstUnanswered to parent via ref or callback
    useEffect(() => {
        // Store function in window for parent to call
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        (window as any).scrollToFirstUnansweredQuestion = scrollToFirstUnanswered;

        return () => {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            delete (window as any).scrollToFirstUnansweredQuestion;
        };
    }, [scrollToFirstUnanswered]);

    if (loading) {
        return (
            <div className="space-y-4">
                <div className="animate-pulse">
                    <div className="mb-4 h-4 w-1/4 rounded bg-muted"></div>
                    <div className="space-y-3">
                        <div className="h-4 rounded bg-muted"></div>
                        <div className="h-4 w-5/6 rounded bg-muted"></div>
                    </div>
                </div>
            </div>
        );
    }

    // Check if user is authenticated and has HTE role
    if (!auth.user || auth.role !== 'hte') {
        return (
            <div className="py-8 text-center">
                <div className="mb-4 text-red-500">
                    <svg className="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                        />
                    </svg>
                </div>
                <h3 className="mb-2 text-lg font-medium text-foreground">Authentication Error</h3>
                <p className="text-muted-foreground">You must be logged in as an HTE user to access this page.</p>
            </div>
        );
    }

    if (categories.length === 0) {
        return (
            <div className="py-8 text-center">
                <div className="mb-4 text-muted-foreground">
                    <svg className="mx-auto h-12 w-12 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                        />
                    </svg>
                </div>
                <h3 className="mb-2 text-lg font-medium text-foreground">No Assessment Criteria Available</h3>
                <p className="text-muted-foreground">
                    There are currently no categories set up in the system. Please contact an administrator to configure the assessment criteria.
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header with instructions */}
            <div className="space-y-2">
                <h2 className="text-xl font-semibold">HTE Assessment Criteria</h2>
                <p className="text-muted-foreground">
                    Rate each criterion on a scale of 1-5, where 1 = Strongly Disagree and 5 = Strongly Agree.
                </p>
                <div className="flex items-center gap-2">
                    <Button type="button" variant="outline" size="sm" onClick={expandAll}>
                        Expand All
                    </Button>
                    <Button type="button" variant="outline" size="sm" onClick={collapseAll}>
                        Collapse All
                    </Button>
                    </div>
            </div>

            {/* Register the assessmentResponses field with react-hook-form */}
            <FormField
                control={control}
                name="assessmentResponses"
                render={({ field }) => (
                    <FormItem>
                        <FormControl>
                            <input
                                type="hidden"
                                {...field}
                                value={JSON.stringify(assessmentResponses)}
                                onChange={(e) => {
                                    try {
                                        const parsed = JSON.parse(e.target.value);
                                        field.onChange(parsed);
                                    } catch {
                                        // Handle parsing error silently
                                    }
                                }}
                            />
                        </FormControl>
                    </FormItem>
                )}
            />

            {/* Overall Progress Indicator */}
            <Card className="bg-muted/30">
                <CardContent className="p-4">
                    <div className="flex items-center justify-between mb-2">
                        <h3 className="text-sm font-medium">Overall Progress</h3>
                        <span className="text-sm font-medium">
                            {overallProgress.answered} / {overallProgress.total} questions answered ({overallProgress.percentage}%)
                        </span>
                    </div>
                    <div className="h-2 w-full rounded-full bg-muted relative overflow-hidden">
                        <div
                            className={`h-2 rounded-full transition-all duration-300 ${
                                overallProgress.isComplete ? 'bg-green-500' : 'bg-blue-500'
                            }`}
                            style={{ width: `${overallProgress.percentage}%` }}
                        ></div>
                </div>
                </CardContent>
            </Card>

            {/* Likert Scale Legend */}
            <Card className="bg-primary/5 border-primary/20">
                <CardContent className="p-4">
                    <div className="flex flex-wrap items-center justify-center gap-4 text-sm">
                        {LIKERT_SCALE.map((option) => (
                            <div key={option.value} className="flex items-center gap-2">
                                <span className="font-bold text-primary">{option.value}</span>
                                <span className="text-muted-foreground">=</span>
                                <span>{option.label}</span>
                            </div>
                        ))}
                </div>
                </CardContent>
            </Card>

            {/* Categories */}
            <div className="space-y-4">
                {categories.map((category) => {
                    const progress = calculateCategoryProgress(category);
                    const isCategoryExpanded = expandedCategories.has(category.id);

                    return (
                        <Card key={category.id} className="border-2">
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle className="text-lg">{category.category_name}</CardTitle>
                                    <div className="flex items-center gap-4">
                                        <span className="text-sm text-muted-foreground">
                                            {progress.answered}/{progress.total} answered
                                        </span>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => toggleCategory(category.id)}
                                        >
                                            <ChevronDownIcon
                                                className={`h-5 w-5 transition-transform duration-200 ${
                                                    isCategoryExpanded ? 'rotate-180' : ''
                                                }`}
                                            />
                                        </Button>
                                    </div>
                                </div>
                                <div className="h-1.5 w-full rounded-full bg-muted mt-2">
                                    <div
                                        className={`h-1.5 rounded-full transition-all duration-300 ${
                                            progress.percentage === 100 ? 'bg-green-500' : 'bg-blue-500'
                                        }`}
                                        style={{ width: `${progress.percentage}%` }}
                                    ></div>
                                </div>
                            </CardHeader>

                            {isCategoryExpanded && (
                                <CardContent className="space-y-4">
                                        {category.subCategories.map((subcategory) => {
                                        const isSubcategoryExpanded = expandedSubcategories.has(subcategory.id);

                                            return (
                                            <Collapsible
                                                key={subcategory.id}
                                                open={isSubcategoryExpanded}
                                                onOpenChange={() => toggleSubcategory(subcategory.id)}
                                            >
                                                <Card className="bg-muted/20">
                                                    <CollapsibleTrigger className="w-full">
                                                        <CardHeader className="cursor-pointer hover:bg-muted/30 transition-colors">
                                                            <div className="flex items-center justify-between">
                                                                <h4 className="text-md font-medium text-left">{subcategory.subcategory_name}</h4>
                                                                <div className="flex items-center gap-2">
                                                                    <span className="text-sm text-muted-foreground">
                                                                        {subcategory.questions.filter((q) => assessmentResponses[`question_${q.id}`]).length}/
                                                                        {subcategory.questions.length}
                                                                    </span>
                                                                    <ChevronDownIcon
                                                                        className={`h-4 w-4 transition-transform duration-200 ${
                                                                            isSubcategoryExpanded ? 'rotate-180' : ''
                                                                    }`}
                                                                />
                                                                </div>
                                                            </div>
                                                        </CardHeader>
                                                    </CollapsibleTrigger>

                                                    <CollapsibleContent>
                                                        <CardContent className="space-y-6 pt-0">
                                                            {subcategory.questions.map((question, qIndex) => {
                                                                const currentValue = assessmentResponses[`question_${question.id}`];

                                                                return (
                                                                    <div
                                                                        key={question.id}
                                                                        id={`question_${question.id}`}
                                                                        className="space-y-3 border-b pb-4 last:border-b-0 transition-all duration-300"
                                                                    >
                                                                        <div className="text-sm font-medium text-foreground">
                                                                            <span className="question-number inline-block transition-all duration-300">
                                                                                {qIndex + 1}.
                                                                            </span> {question.question}
                                                                        </div>
                                                                        <RadioGroup
                                                                            value={currentValue?.toString()}
                                                                            onValueChange={(value) => {
                                                                                console.log('🔵 [RadioGroup] CLICKED! Question ID:', question.id, 'Value:', value);

                                                                                const numValue = parseInt(value);
                                                                                const currentResponses = getValues('assessmentResponses') || {};

                                                                                console.log('🔵 [RadioGroup] Current responses before update:', currentResponses);
                                                                                console.log('🔵 [RadioGroup] Keys count:', Object.keys(currentResponses).length);

                                                                                const newResponses = {
                                                                                    ...currentResponses,
                                                                                    [`question_${question.id}`]: numValue
                                                                                };

                                                                                console.log('🔵 [RadioGroup] New responses to set:', newResponses);
                                                                                console.log('🔵 [RadioGroup] New keys count:', Object.keys(newResponses).length);

                                                                                setValue('assessmentResponses', newResponses, {
                                                                                    shouldValidate: true,
                                                                                    shouldDirty: true,
                                                                                    shouldTouch: true
                                                                                });

                                                                                console.log('🔵 [RadioGroup] After setValue, checking form:', getValues('assessmentResponses'));
                                                                            }}
                                                                            className="flex flex-col sm:flex-row sm:flex-wrap gap-3"
                                                                        >
                                                                            {LIKERT_SCALE.map((option) => (
                                                                                <label
                                                                                    key={option.value}
                                                                                    htmlFor={`question_${question.id}_option_${option.value}`}
                                                                                    className="flex items-center gap-2 cursor-pointer group"
                                                                                >
                                                                                    <RadioGroupItem
                                                                                        value={option.value}
                                                                                        id={`question_${question.id}_option_${option.value}`}
                                                                                        className="cursor-pointer"
                                                                                    />
                                                                                    <span className="text-sm text-foreground group-hover:text-primary transition-colors">
                                                                                        {option.value} - {option.label}
                                                                                    </span>
                                                                                </label>
                                                                            ))}
                                                                        </RadioGroup>
                                                                    </div>
                                                                );
                                                            })}
                                                        </CardContent>
                                                        </CollapsibleContent>
                                                </Card>
                                                    </Collapsible>
                                            );
                                        })}
                            </CardContent>
                            )}
                        </Card>
                    );
                })}
            </div>
        </div>
    );
}
