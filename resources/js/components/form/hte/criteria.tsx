import { FormControl, FormField, FormItem } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { PieChart } from '@/components/ui/pie-chart';
import { useFormContext } from 'react-hook-form';
import React, { useCallback, useMemo } from 'react';
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

interface PieChartData {
    name: string;
    value: number;
    color: string;
}

interface CriteriaProps {
    categories: Category[];
    loading: boolean;
    expandedCategories: Set<number>;
    expandedQuestions: Set<number>;
    setExpandedQuestions: React.Dispatch<React.SetStateAction<Set<number>>>;
}

export default function Criteria({
    categories,
    loading,
    expandedCategories,
    expandedQuestions,
    setExpandedQuestions,
}: CriteriaProps) {
    const { control, watch, setValue } = useFormContext();
    const { auth } = usePage<SharedData>().props;

    // Watch the weight values for validation
    const subcategoryWeights: Record<string, number> = useMemo(() => {
        return watch('subcategoryWeights') || {};
    }, [watch]);

    // Initialize default weights when categories are loaded
    React.useEffect(() => {
        if (categories.length > 0 && Object.keys(subcategoryWeights).length === 0) {
            const newWeights: Record<string, number> = {};

            categories.forEach((category) => {
                if (category.subCategories && category.subCategories.length > 0) {
                    const equalWeight = Math.floor(100 / category.subCategories.length);
                    const remainder = 100 % category.subCategories.length;

                    category.subCategories.forEach((subcat: SubCategory, index: number) => {
                        newWeights[subcat.id] = index < remainder ? equalWeight + 1 : equalWeight;
                    });
                }
            });

            Object.entries(newWeights).forEach(([subcatId, weight]) => {
                setValue(`subcategoryWeights.${subcatId}`, weight);
            });
        }
    }, [categories, setValue, subcategoryWeights]);

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

    const handleWeightChange = useCallback(
        (categoryId: number, subcategoryId: number, newWeight: number) => {
            const category = categories.find((cat) => cat.id === categoryId);
            if (!category || !category.subCategories) return;

            const clampedWeight = Math.max(0, Math.min(100, newWeight));
            const subcategories = category.subCategories;
            const currentTotal = subcategories.reduce((sum, subcat) => {
                if (subcat.id === subcategoryId) return sum + clampedWeight;
                return sum + (subcategoryWeights[subcat.id] || 0);
            }, 0);

            if (currentTotal > 100) {
                const excess = currentTotal - 100;
                const otherSubcategories = subcategories.filter((subcat) => subcat.id !== subcategoryId);
                const otherTotal = otherSubcategories.reduce((sum, subcat) => sum + (subcategoryWeights[subcat.id] || 0), 0);

                if (otherTotal > 0) {
                    otherSubcategories.forEach((subcat) => {
                        const currentWeight = subcategoryWeights[subcat.id] || 0;
                        const proportion = currentWeight / otherTotal;
                        const adjustedWeight = Math.max(0, currentWeight - excess * proportion);
                        setValue(`subcategoryWeights.${subcat.id}`, Math.round(adjustedWeight));
                    });
                }
            }

            setValue(`subcategoryWeights.${subcategoryId}`, clampedWeight);
        },
        [categories, subcategoryWeights, setValue],
    );

    const resetToEqualWeights = useCallback(
        (categoryId: number) => {
            const category = categories.find((cat) => cat.id === categoryId);
            if (!category || !category.subCategories) return;

            const equalWeight = Math.floor(100 / category.subCategories.length);
            const remainder = 100 % category.subCategories.length;

            category.subCategories.forEach((subcat: SubCategory, index: number) => {
                const weight = index < remainder ? equalWeight + 1 : equalWeight;
                setValue(`subcategoryWeights.${subcat.id}`, weight);
            });
        },
        [categories, setValue],
    );

    const ensureAllWeightsSet = useCallback(() => {
        categories.forEach((category) => {
            if (category.subCategories && category.subCategories.length > 0) {
                const equalWeight = Math.floor(100 / category.subCategories.length);
                const remainder = 100 % category.subCategories.length;

                category.subCategories.forEach((subcat: SubCategory, index: number) => {
                    const weight = index < remainder ? equalWeight + 1 : equalWeight;
                    setValue(`subcategoryWeights.${subcat.id}`, weight);
                });
            }
        });
    }, [categories, setValue]);

    // Memoize computed values to prevent unnecessary recalculations
    const getPieChartData = useCallback(
        (category: Category): PieChartData[] => {
            if (!category.subCategories) return [];

            const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8', '#82CA9D', '#FFC658', '#FF6B6B'];

            return category.subCategories.map((subcat, index) => ({
                name: subcat.subcategory_name,
                value: subcategoryWeights[subcat.id] || 0,
                color: COLORS[index % COLORS.length],
            }));
        },
        [subcategoryWeights],
    );

    const calculateCategoryTotal = useCallback(
        (categoryId: number) => {
            const category = categories.find((cat) => cat.id === categoryId);
            if (!category || !category.subCategories) return 0;

            return category.subCategories.reduce((sum, subcat) => {
                const weight = subcategoryWeights[subcat.id] || 0;
                return sum + (Number(weight) || 0);
            }, 0);
        },
        [categories, subcategoryWeights],
    );
    // Memoize filtered categories to prevent unnecessary re-filtering
    const categoriesWithSubcategories = useMemo(() => {
        return categories.filter((category) => {
            return category.subCategories && category.subCategories.length > 0;
        });
    }, [categories]);

    // Validate that all subcategories have weights and they add up to 100% for each category
    const validateWeights = useCallback(() => {
        const weights = watch('subcategoryWeights') || {};
        const validationErrors: string[] = [];

        categories.forEach((category) => {
            if (category.subCategories && category.subCategories.length > 0) {
                const categoryWeights = category.subCategories.map((subcat) => weights[subcat.id] || 0);
                const totalWeight = categoryWeights.reduce((sum, weight) => sum + weight, 0);

                if (totalWeight !== 100) {
                    validationErrors.push(`Category "${category.category_name}" weights must total 100% (currently ${totalWeight}%)`);
                }

                // Check if any subcategory is missing a weight
                category.subCategories.forEach((subcat) => {
                    if (weights[subcat.id] === undefined || weights[subcat.id] === null) {
                        validationErrors.push(`Subcategory "${subcat.subcategory_name}" is missing a weight`);
                    }
                });
            }
        });

        return validationErrors;
    }, [categories, watch]);

    React.useEffect(() => {
        const errors = validateWeights();
        if (errors.length > 0 && process.env.NODE_ENV === 'development') {
            console.warn('Weight validation errors:', errors);
        }
    }, [validateWeights]);

    // Calculate overall weight status
    const overallStatus = useMemo(() => {
        const weights = watch('subcategoryWeights') || {};
        let totalSubcategories = 0;
        let subcategoriesWithWeights = 0;
        let categoriesWithValidWeights = 0;
        let totalCategories = 0;

        categories.forEach((category) => {
            if (category.subCategories && category.subCategories.length > 0) {
                totalCategories++;
                totalSubcategories += category.subCategories.length;

                const categoryWeights = category.subCategories.map((subcat) => weights[subcat.id] || 0);
                const totalWeight = categoryWeights.reduce((sum, weight) => sum + weight, 0);

                if (totalWeight === 100) {
                    categoriesWithValidWeights++;
                }

                subcategoriesWithWeights += categoryWeights.filter((weight) => weight > 0).length;
            }
        });

        return {
            totalSubcategories,
            subcategoriesWithWeights,
            categoriesWithValidWeights,
            totalCategories,
            isComplete: subcategoriesWithWeights === totalSubcategories && categoriesWithValidWeights === totalCategories,
        };
    }, [categories, watch]);

    if (loading) {
        return (
            <div className="space-y-4">
                <div className="animate-pulse">
                    <div className="mb-4 h-4 w-1/4 rounded bg-gray-200"></div>
                    <div className="space-y-3">
                        <div className="h-4 rounded bg-gray-200"></div>
                        <div className="h-4 w-5/6 rounded bg-gray-200"></div>
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
                <h3 className="mb-2 text-lg font-medium text-gray-900">Authentication Error</h3>
                <p className="text-gray-600">You must be logged in as an HTE user to access this page.</p>
            </div>
        );
    }

    if (categoriesWithSubcategories.length === 0) {
        return (
            <div className="py-8 text-center">
                <div className="mb-4 text-gray-500">
                    <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                        />
                    </svg>
                </div>
                <h3 className="mb-2 text-lg font-medium text-gray-900">No Assessment Criteria Available</h3>
                <p className="text-gray-600">
                    There are currently no categories with subcategories set up in the system. Please contact an administrator to configure the
                    assessment criteria.
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Overall Weight Status */}
            <Card className="border-2 border-blue-100 bg-blue-50">
                <CardHeader>
                    <CardTitle className="text-blue-800">Weight Assignment Status</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-2 gap-4 text-center md:grid-cols-4">
                        <div>
                            <div className="text-2xl font-bold text-blue-600">{overallStatus.subcategoriesWithWeights}</div>
                            <div className="text-sm text-blue-700">Subcategories with Weights</div>
                            <div className="text-xs text-blue-600">of {overallStatus.totalSubcategories}</div>
                        </div>
                        <div>
                            <div className="text-2xl font-bold text-blue-600">{overallStatus.categoriesWithValidWeights}</div>
                            <div className="text-sm text-blue-700">Categories with 100%</div>
                            <div className="text-xs text-blue-600">of {overallStatus.totalCategories}</div>
                        </div>
                        <div>
                            <div className="text-2xl font-bold text-blue-600">
                                {Math.round((overallStatus.subcategoriesWithWeights / overallStatus.totalSubcategories) * 100)}%
                            </div>
                            <div className="text-sm text-blue-700">Completion</div>
                        </div>
                        <div>
                            <div className={`text-2xl font-bold ${overallStatus.isComplete ? 'text-green-600' : 'text-yellow-600'}`}>
                                {overallStatus.isComplete ? '✓' : '⚠'}
                            </div>
                            <div className="text-sm text-blue-700">Status</div>
                            <div className="text-xs text-blue-600">{overallStatus.isComplete ? 'Ready' : 'Incomplete'}</div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Categories */}
            <h2 className="text-xl font-semibold">Assessment Criteria</h2>
            <p className="text-gray-600 dark:text-gray-400">Assign weights to each subcategory. Weights within each category must total 100%.</p>

            {/* Register the subcategoryWeights field with react-hook-form */}
            <FormField
                control={control}
                name="subcategoryWeights"
                render={({ field }) => (
                    <FormItem>
                        <FormControl>
                            <input
                                type="hidden"
                                {...field}
                                value={JSON.stringify(subcategoryWeights)}
                                onChange={(e) => {
                                    try {
                                        const parsed = JSON.parse(e.target.value);
                                        field.onChange(parsed);
                                    } catch (error) {
                                        console.error('Error parsing subcategoryWeights:', error);
                                    }
                                }}
                            />
                        </FormControl>
                    </FormItem>
                )}
            />

            <div className="space-y-6">
                {categoriesWithSubcategories.map((category) => {
                    expandedCategories.has(category.id);
                    const categoryTotal = calculateCategoryTotal(category.id);
                    const pieChartData = getPieChartData(category);

                    return (
                        <Card key={category.id} className="border-2">
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle className="text-lg">{category.category_name}</CardTitle>
                                    <div className="flex items-center gap-4">
                                        <div className="flex items-center gap-2">
                                            <span className="text-sm font-medium">Total: {categoryTotal}%</span>
                                            <div
                                                className={`rounded-full px-2 py-1 text-xs font-medium ${
                                                    categoryTotal === 100
                                                        ? 'bg-green-100 text-green-800'
                                                        : categoryTotal > 100
                                                          ? 'bg-red-100 text-red-800'
                                                          : 'bg-yellow-100 text-yellow-800'
                                                }`}
                                            >
                                                {categoryTotal === 100 ? '✓ Valid' : categoryTotal > 100 ? '✗ Exceeds 100%' : '⚠ Incomplete'}
                                            </div>
                                        </div>
                                        <Button variant="outline" size="sm" onClick={() => resetToEqualWeights(category.id)}>
                                            Reset to Equal
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>

                            <CardContent className="space-y-6">
                                {/* Pie Chart */}
                                <div className="flex justify-center">
                                    <PieChart
                                        data={pieChartData}
                                        title={`${category.category_name} Weight Distribution`}
                                        totalWeight={100}
                                        showLabels={true}
                                        showTooltip={true}
                                        showLegend={true}
                                        onSliceClick={(data) => {
                                            // Find the subcategory by name and focus on its weight input
                                            const subcategory = category.subCategories.find((sub) => sub.subcategory_name === data.name);
                                            if (subcategory) {
                                                const inputElement = document.getElementById(`weight-${subcategory.id}`) as HTMLInputElement;
                                                if (inputElement) {
                                                    inputElement.focus();
                                                    inputElement.select();
                                                }
                                            }
                                        }}
                                    />
                                </div>

                                {/* Weight Distribution Summary */}
                                <div className="rounded-lg bg-gray-50 p-4">
                                    <h5 className="mb-3 font-medium text-gray-700">Weight Distribution Summary</h5>

                                    {/* Progress Bar */}
                                    <div className="mb-4">
                                        <div className="mb-2 flex items-center justify-between text-sm text-gray-600">
                                            <span>Total Weight: {categoryTotal}%</span>
                                            <span>Target: 100%</span>
                                        </div>
                                        <div className="h-2 w-full rounded-full bg-gray-200">
                                            <div
                                                className={`h-2 rounded-full transition-all duration-300 ${
                                                    categoryTotal === 100 ? 'bg-green-500' : categoryTotal > 100 ? 'bg-red-500' : 'bg-yellow-500'
                                                }`}
                                                style={{ width: `${Math.min(categoryTotal, 100)}%` }}
                                            ></div>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                                        {category.subCategories.map((subcat) => {
                                            const weight = subcategoryWeights[subcat.id] || 0;
                                            const percentage = ((weight / 100) * 100).toFixed(1);
                                            const questionCount = subcat.questions ? subcat.questions.length : 0;
                                            return (
                                                <div key={subcat.id} className="text-center">
                                                    <div className="text-lg font-semibold text-blue-600">{weight}%</div>
                                                    <div className="text-xs text-gray-600">{subcat.subcategory_name}</div>
                                                    <div className="text-xs text-gray-500">{percentage}% of total</div>
                                                    <div className="text-xs text-gray-400">
                                                        {questionCount} question{questionCount !== 1 ? 's' : ''}
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>

                                {/* Subcategories with Weight Inputs */}
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <h4 className="font-medium text-gray-700">Subcategory Weights</h4>
                                        <Button type="button" variant="outline" size="sm" onClick={ensureAllWeightsSet} className="text-xs">
                                            Redistribute Weights Evenly
                                        </Button>
                                    </div>
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                        {category.subCategories.map((subcategory) => {
                                            const isQuestionsExpanded = expandedQuestions.has(subcategory.id);
                                            const subcategoryWeight = subcategoryWeights[subcategory.id] || 0;

                                            return (
                                                <Card key={subcategory.id} className="border">
                                                    <CardHeader className="pb-3">
                                                        <Collapsible open={isQuestionsExpanded} onOpenChange={() => toggleQuestions(subcategory.id)}>
                                                            <CollapsibleTrigger asChild>
                                                                <Button
                                                                    variant="ghost"
                                                                    className="h-auto w-full justify-between p-0 font-normal hover:bg-transparent"
                                                                >
                                                                    <span className="text-sm font-medium">{subcategory.subcategory_name}</span>
                                                                    <ChevronDownIcon
                                                                        className={`h-4 w-4 transition-transform duration-200 ${
                                                                            isQuestionsExpanded ? 'rotate-180' : ''
                                                                        }`}
                                                                    />
                                                                </Button>
                                                            </CollapsibleTrigger>
                                                        </Collapsible>
                                                    </CardHeader>

                                                    <CardContent className="space-y-3">
                                                        {/* Weight Input */}
                                                        <div className="space-y-2">
                                                            <div className="flex items-center gap-2">
                                                                <span className="text-sm">Weight:</span>
                                                                <Input
                                                                    id={`weight-${subcategory.id}`}
                                                                    type="number"
                                                                    min="0"
                                                                    max="100"
                                                                    value={subcategoryWeight}
                                                                    onChange={(e) =>
                                                                        handleWeightChange(category.id, subcategory.id, Number(e.target.value))
                                                                    }
                                                                    className="w-20"
                                                                />
                                                                <span className="text-sm">%</span>
                                                            </div>

                                                            {/* Weight Bar */}
                                                            <div className="h-2 w-full rounded-full bg-gray-200">
                                                                <div
                                                                    className="h-2 rounded-full bg-blue-500 transition-all duration-300"
                                                                    style={{ width: `${subcategoryWeight}%` }}
                                                                ></div>
                                                            </div>
                                                            <div className="text-center text-xs text-gray-500">
                                                                {subcategoryWeight}% of category total
                                                            </div>
                                                        </div>

                                                        {/* Questions - Now revealed with dropdown animation */}
                                                        <Collapsible open={isQuestionsExpanded}>
                                                            <CollapsibleContent className="space-y-2 duration-200 animate-in fade-in-0 slide-in-from-top-2">
                                                                <h5 className="text-xs font-medium text-gray-600">
                                                                    Questions ({subcategory.questions ? subcategory.questions.length : 0}):
                                                                </h5>
                                                                <div className="max-h-32 space-y-1 overflow-y-auto">
                                                                    {subcategory.questions && subcategory.questions.length > 0 ? (
                                                                        subcategory.questions.map((question, qIndex) => (
                                                                            <div
                                                                                key={question.id}
                                                                                className="border-l-2 border-gray-200 pl-2 text-xs text-gray-600"
                                                                            >
                                                                                <span className="font-medium text-gray-700">Q{qIndex + 1}:</span>{' '}
                                                                                {question.question}
                                                                            </div>
                                                                        ))
                                                                    ) : (
                                                                        <div className="pl-2 text-xs text-gray-500">No questions available</div>
                                                                    )}
                                                                </div>
                                                            </CollapsibleContent>
                                                        </Collapsible>
                                                    </CardContent>
                                                </Card>
                                            );
                                        })}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>
        </div>
    );
}
