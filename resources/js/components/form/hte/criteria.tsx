import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { FormControl, FormField, FormItem } from '@/components/ui/form';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { ChevronDownIcon } from '@radix-ui/react-icons';
import React, { useCallback, useMemo } from 'react';
import { useFormContext } from 'react-hook-form';

interface Category {
    id: number;
    category_name: string;
    subCategories: SubCategory[];
}

interface Question {
    id: number;
    question: string;
    access: string;
    is_active: boolean;
    rating?: number | null;
}

interface SubCategory {
    id: number;
    subcategory_name: string;
    questions?: Question[];
}

interface CriteriaProps {
    categories: Category[];
    loading: boolean;
    expandedCategories: Set<number>;
    expandedSubcategories: Set<number>;
    expandedQuestions: Set<number>;
    setExpandedCategories: React.Dispatch<React.SetStateAction<Set<number>>>;
    setExpandedSubcategories: React.Dispatch<React.SetStateAction<Set<number>>>;
    setExpandedQuestions: React.Dispatch<React.SetStateAction<Set<number>>>;
    highlightInvalidCategories?: boolean;
}

const LIKERT_LABELS = [
    { value: 1, label: 'Not Important', description: 'Poor (<75%)' },
    { value: 2, label: 'Somewhat Important', description: 'Fair (75-79%)' },
    { value: 3, label: 'Important', description: 'Good (80-89%)' },
    { value: 4, label: 'Very Important', description: 'Very Good (90-95%)' },
    { value: 5, label: 'Most Important', description: 'Excellent (96-100%)' },
];

/**
 * Calculate subcategory percentage from question ratings
 * Formula: (sum of question ratings) / (number of questions × 5) × 100
 */
function calculateSubcategoryPercentage(questionRatings: number[], questionCount: number): number {
    if (questionCount === 0) return 0;
    const sumOfRatings = questionRatings.reduce((sum, rating) => sum + rating, 0);
    const maxPossibleScore = questionCount * 5;
    return Math.round((sumOfRatings / maxPossibleScore) * 100 * 100) / 100;
}

/**
 * Map percentage to Likert scale equivalent
 */
function mapPercentageToRating(percentage: number): number {
    if (percentage >= 96) return 5;
    if (percentage >= 90) return 4;
    if (percentage >= 80) return 3;
    if (percentage >= 75) return 2;
    return 1;
}

/**
 * Get descriptive rating from percentage
 */
function getDescriptiveRating(percentage: number): string {
    if (percentage >= 96) return 'Excellent';
    if (percentage >= 90) return 'Very Good';
    if (percentage >= 80) return 'Good';
    if (percentage >= 75) return 'Fair';
    return 'Poor';
}

export default function Criteria({
    categories,
    loading,
    expandedCategories,
    expandedSubcategories,
    expandedQuestions,
    setExpandedCategories,
    setExpandedSubcategories,
    setExpandedQuestions,
    highlightInvalidCategories = false,
}: CriteriaProps) {
    const { control, watch, setValue } = useFormContext();
    const { auth } = usePage<SharedData>().props;

    // Watch question ratings
    const questionRatings: Record<string, number> = useMemo(() => {
        return watch('questionRatings') || {};
    }, [watch]);

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

    const toggleQuestions = useCallback(
        (subcategoryId: number) => {
            setExpandedQuestions((prev) => {
                const newExpanded = new Set(prev);
                if (newExpanded.has(subcategoryId)) {
                    newExpanded.delete(subcategoryId);
                } else {
                    newExpanded.add(subcategoryId);
                }
                return newExpanded;
            });
        },
        [setExpandedQuestions],
    );

    const handleRatingChange = useCallback(
        (questionId: number, rating: number) => {
            setValue(`questionRatings.${questionId}`, rating, { shouldValidate: true });
        },
        [setValue],
    );

    // Calculate subcategory percentage and rating
    const getSubcategoryStats = useCallback(
        (subcategory: SubCategory) => {
            if (!subcategory.questions || subcategory.questions.length === 0) {
                return { percentage: 0, rating: 0, descriptiveRating: 'N/A' };
            }

            const ratings = subcategory.questions
                .map((q) => questionRatings[q.id])
                .filter((r) => r !== undefined && r !== null && r >= 1 && r <= 5) as number[];

            if (ratings.length === 0) {
                return { percentage: 0, rating: 0, descriptiveRating: 'Not Rated' };
            }

            const percentage = calculateSubcategoryPercentage(ratings, subcategory.questions.length);
            const rating = mapPercentageToRating(percentage);
            const descriptiveRating = getDescriptiveRating(percentage);

            return { percentage, rating, descriptiveRating };
        },
        [questionRatings],
    );

    // Calculate category percentage
    const getCategoryStats = useCallback(
        (category: Category) => {
            let allRatings: number[] = [];
            let totalQuestions = 0;

            category.subCategories.forEach((subcat) => {
                if (subcat.questions && subcat.questions.length > 0) {
                    const ratings = subcat.questions
                        .map((q) => questionRatings[q.id])
                        .filter((r) => r !== undefined && r !== null && r >= 1 && r <= 5) as number[];
                    allRatings.push(...ratings);
                    totalQuestions += subcat.questions.length;
                }
            });

            if (allRatings.length === 0 || totalQuestions === 0) {
                return { percentage: 0, rating: 0, descriptiveRating: 'Not Rated' };
            }

            const percentage = calculateSubcategoryPercentage(allRatings, totalQuestions);
            const rating = mapPercentageToRating(percentage);
            const descriptiveRating = getDescriptiveRating(percentage);

            return { percentage, rating, descriptiveRating };
        },
        [questionRatings],
    );

    // Memoize filtered categories
    const categoriesWithSubcategories = useMemo(() => {
        return categories.filter((category) => {
            return category.subCategories && category.subCategories.length > 0;
        });
    }, [categories]);

    // Check for unrated questions
    const hasUnratedQuestions = useMemo(() => {
        return categories.some((category) =>
            category.subCategories?.some((subcat) =>
                subcat.questions?.some((question) => {
                    const rating = questionRatings[question.id];
                    return rating === undefined || rating === null || rating < 1 || rating > 5;
                }),
            ),
        );
    }, [categories, questionRatings]);

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

    if (categoriesWithSubcategories.length === 0) {
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
                    There are currently no categories with subcategories set up in the system. Please contact an administrator to configure the
                    assessment criteria.
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="space-y-2">
                <h2 className="text-xl font-semibold">Assessment Criteria</h2>
                <p className="text-muted-foreground">
                    Rate the importance of each question on a scale of 1-5. Higher ratings indicate greater importance. These ratings determine the
                    passing thresholds for students.
                </p>
                {highlightInvalidCategories && hasUnratedQuestions && (
                    <div className="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-950/30">
                        <div className="flex-shrink-0">
                            <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fillRule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clipRule="evenodd"
                                />
                            </svg>
                        </div>
                        <div className="text-sm text-red-800 dark:text-red-200">
                            <strong>Cannot proceed:</strong> All questions must be rated before proceeding. Please rate all questions below.
                        </div>
                    </div>
                )}
            </div>

            {/* Register questionRatings field */}
            <FormField
                control={control}
                name="questionRatings"
                render={({ field }) => (
                    <FormItem>
                        <FormControl>
                            <input type="hidden" {...field} value={JSON.stringify(questionRatings)} />
                        </FormControl>
                    </FormItem>
                )}
            />

            {/* Categories */}
            <div className="space-y-4">
                {categoriesWithSubcategories.map((category) => {
                    const categoryStats = getCategoryStats(category);
                    const isCategoryExpanded = expandedCategories.has(category.id);

                    return (
                        <Card
                            key={category.id}
                            id={`category-card-${category.category_name.toLowerCase().replace(/\s+/g, '-')}`}
                            className={`border-2 transition-all duration-300 ${
                                highlightInvalidCategories && hasUnratedQuestions
                                    ? 'ring-opacity-50 border-red-200 ring-2 ring-red-500 dark:border-red-800'
                                    : 'border-border'
                            }`}
                        >
                            <Collapsible open={isCategoryExpanded} onOpenChange={() => toggleCategory(category.id)}>
                                <CollapsibleTrigger asChild>
                                    <CardHeader className="cursor-pointer transition-colors hover:bg-muted/50">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-3">
                                                <ChevronDownIcon
                                                    className={`h-4 w-4 transition-transform duration-200 ${isCategoryExpanded ? 'rotate-180' : ''}`}
                                                />
                                                <CardTitle className="text-lg">{category.category_name}</CardTitle>
                                            </div>
                                            <div className="flex items-center gap-4">
                                                <div className="text-right">
                                                    <div className="text-sm text-muted-foreground">Category Passing Threshold</div>
                                                    <div
                                                        className={`text-lg font-bold ${
                                                            categoryStats.percentage >= 96
                                                                ? 'text-green-600 dark:text-green-400'
                                                                : categoryStats.percentage >= 90
                                                                  ? 'text-blue-600 dark:text-blue-400'
                                                                  : categoryStats.percentage >= 80
                                                                    ? 'text-yellow-600 dark:text-yellow-400'
                                                                    : categoryStats.percentage >= 75
                                                                      ? 'text-orange-600 dark:text-orange-400'
                                                                      : 'text-red-600 dark:text-red-400'
                                                        }`}
                                                    >
                                                        {categoryStats.percentage > 0 ? `${categoryStats.percentage}%` : 'Not Rated'}
                                                    </div>
                                                    <div className="text-xs text-muted-foreground">
                                                        {categoryStats.descriptiveRating} (Rating: {categoryStats.rating})
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </CardHeader>
                                </CollapsibleTrigger>
                            </Collapsible>

                            <Collapsible open={isCategoryExpanded} onOpenChange={() => toggleCategory(category.id)}>
                                <CollapsibleContent>
                                    <CardContent className="space-y-4">
                                        {category.subCategories.map((subcategory) => {
                                            const subcategoryStats = getSubcategoryStats(subcategory);
                                            const isSubcategoryExpanded = expandedSubcategories.has(subcategory.id);
                                            const isQuestionsExpanded = expandedQuestions.has(subcategory.id);

                                            return (
                                                <div key={subcategory.id} className="space-y-3 rounded-lg border p-4">
                                                    <Collapsible open={isSubcategoryExpanded} onOpenChange={() => toggleSubcategory(subcategory.id)}>
                                                        <CollapsibleTrigger asChild>
                                                            <div className="-m-4 flex cursor-pointer items-center justify-between rounded-t-lg p-4 transition-colors hover:bg-muted/30">
                                                                <div className="flex items-center gap-2">
                                                                    <ChevronDownIcon
                                                                        className={`h-3 w-3 transition-transform duration-200 ${
                                                                            isSubcategoryExpanded ? 'rotate-180' : ''
                                                                        }`}
                                                                    />
                                                                    <h4 className="font-medium">{subcategory.subcategory_name}</h4>
                                                                </div>
                                                                <div className="text-right">
                                                                    <div className="text-xs text-muted-foreground">Subcategory Passing Threshold</div>
                                                                    <div
                                                                        className={`text-sm font-semibold ${
                                                                            subcategoryStats.percentage >= 96
                                                                                ? 'text-green-600 dark:text-green-400'
                                                                                : subcategoryStats.percentage >= 90
                                                                                  ? 'text-blue-600 dark:text-blue-400'
                                                                                  : subcategoryStats.percentage >= 80
                                                                                    ? 'text-yellow-600 dark:text-yellow-400'
                                                                                    : subcategoryStats.percentage >= 75
                                                                                      ? 'text-orange-600 dark:text-orange-400'
                                                                                      : 'text-red-600 dark:text-red-400'
                                                                        }`}
                                                                    >
                                                                        {subcategoryStats.percentage > 0
                                                                            ? `${subcategoryStats.percentage}%`
                                                                            : 'Not Rated'}
                                                                    </div>
                                                                    <div className="text-xs text-muted-foreground">
                                                                        {subcategoryStats.descriptiveRating}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </CollapsibleTrigger>

                                                        <CollapsibleContent>
                                                            <div className="space-y-4 pt-4 pl-6">
                                                                {subcategory.questions && subcategory.questions.length > 0 ? (
                                                                    <div className="space-y-4">
                                                                        {subcategory.questions.map((question, qIndex) => {
                                                                            const currentRating = questionRatings[question.id];
                                                                            const isUnrated =
                                                                                currentRating === undefined ||
                                                                                currentRating === null ||
                                                                                currentRating < 1 ||
                                                                                currentRating > 5;

                                                                            return (
                                                                                <div
                                                                                    key={question.id}
                                                                                    id={`question-rating-${question.id}`}
                                                                                    className={`rounded-lg border p-4 ${
                                                                                        isUnrated && highlightInvalidCategories
                                                                                            ? 'border-red-300 bg-red-50/50 dark:border-red-700 dark:bg-red-950/20'
                                                                                            : 'border-border'
                                                                                    }`}
                                                                                >
                                                                                    <div className="mb-3">
                                                                                        <div className="flex items-start gap-2">
                                                                                            <span className="text-xs font-medium text-muted-foreground">
                                                                                                Question {qIndex + 1}:
                                                                                            </span>
                                                                                            <p className="flex-1 text-sm font-medium">
                                                                                                {question.question}
                                                                                            </p>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div className="space-y-2">
                                                                                        <div className="flex flex-wrap gap-2">
                                                                                            {LIKERT_LABELS.map((likert) => (
                                                                                                <Button
                                                                                                    key={likert.value}
                                                                                                    type="button"
                                                                                                    variant={
                                                                                                        currentRating === likert.value
                                                                                                            ? 'default'
                                                                                                            : 'outline'
                                                                                                    }
                                                                                                    size="sm"
                                                                                                    onClick={() =>
                                                                                                        handleRatingChange(question.id, likert.value)
                                                                                                    }
                                                                                                    className={`h-9 px-3 text-xs ${
                                                                                                        currentRating === likert.value
                                                                                                            ? 'bg-primary text-primary-foreground'
                                                                                                            : ''
                                                                                                    }`}
                                                                                                >
                                                                                                    <div className="text-center">
                                                                                                        <div className="font-semibold">
                                                                                                            {likert.value}
                                                                                                        </div>
                                                                                                        <div className="text-[10px] opacity-80">
                                                                                                            {likert.label}
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </Button>
                                                                                            ))}
                                                                                        </div>
                                                                                        {currentRating && (
                                                                                            <div className="mt-1 text-xs text-muted-foreground">
                                                                                                Selected: {currentRating} -{' '}
                                                                                                {
                                                                                                    LIKERT_LABELS.find(
                                                                                                        (l) => l.value === currentRating,
                                                                                                    )?.label
                                                                                                }{' '}
                                                                                                (
                                                                                                {
                                                                                                    LIKERT_LABELS.find(
                                                                                                        (l) => l.value === currentRating,
                                                                                                    )?.description
                                                                                                }
                                                                                                )
                                                                                            </div>
                                                                                        )}
                                                                                    </div>
                                                                                </div>
                                                                            );
                                                                        })}
                                                                    </div>
                                                                ) : (
                                                                    <div className="text-sm text-muted-foreground">No questions available</div>
                                                                )}
                                                            </div>
                                                        </CollapsibleContent>
                                                    </Collapsible>
                                                </div>
                                            );
                                        })}
                                    </CardContent>
                                </CollapsibleContent>
                            </Collapsible>
                        </Card>
                    );
                })}
            </div>
        </div>
    );
}
