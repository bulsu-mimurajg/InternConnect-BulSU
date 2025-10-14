import { FormControl, FormField, FormItem } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Badge } from '@/components/ui/badge';
import { PieChart, SimplePieChart } from '@/components/charts';
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
    expandedSubcategories: Set<number>;
    expandedQuestions: Set<number>;
    setExpandedCategories: React.Dispatch<React.SetStateAction<Set<number>>>;
    setExpandedSubcategories: React.Dispatch<React.SetStateAction<Set<number>>>;
    setExpandedQuestions: React.Dispatch<React.SetStateAction<Set<number>>>;
    highlightInvalidCategories?: boolean;
    lockedSubcategories: Set<number>;
    onToggleSubcategoryLock: (subcategoryId: number) => void;
    onWeightChange?: () => void;
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
    lockedSubcategories,
    onToggleSubcategoryLock,
    onWeightChange,
}: CriteriaProps) {
    const { control, watch, setValue } = useFormContext();
    const { auth } = usePage<SharedData>().props;

    // Watch the weight values for validation
    const subcategoryWeights: Record<string, number> = useMemo(() => {
        return watch('subcategoryWeights') || {};
    }, [watch]);

    // Note: Weight initialization is handled in the parent form component
    // to avoid conflicts and ensure proper weight distribution

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
            // Prevent weight changes if subcategory is locked
            if (lockedSubcategories.has(subcategoryId)) return;
            
            const category = categories.find((cat) => cat.id === categoryId);
            if (!category || !category.subCategories) return;

            const clampedWeight = Math.max(0, Math.min(100, newWeight));
            const subcategories = category.subCategories;
            
            // Minimum weight threshold to prevent subcategories from going to 0%
            const MIN_WEIGHT_THRESHOLD = 1;
            
            // If only one subcategory, set it to the weight
            if (subcategories.length === 1) {
                setValue(`subcategoryWeights.${subcategoryId}`, clampedWeight);
                return;
            }

            // Get other unlocked subcategories (excluding the one being changed and locked ones)
            const otherUnlockedSubcategories = subcategories.filter((subcat) => 
                subcat.id !== subcategoryId && !lockedSubcategories.has(subcat.id)
            );
            
            // Calculate total weight of ALL locked subcategories (excluding the one being changed)
            const lockedSubcategoriesTotal = subcategories
                .filter((subcat) => subcat.id !== subcategoryId && lockedSubcategories.has(subcat.id))
                .reduce((sum, subcat) => sum + (subcategoryWeights[subcat.id] || 0), 0);
            
            // Calculate total weight of other unlocked subcategories
            const otherUnlockedSubcategoriesTotal = otherUnlockedSubcategories.reduce((sum, subcat) => {
                return sum + (subcategoryWeights[subcat.id] || 0);
            }, 0);

            // Calculate remaining weight to distribute among unlocked subcategories
            // This is: 100% - (new weight + all locked weights)
            const remainingWeight = 100 - clampedWeight - lockedSubcategoriesTotal;
            
            // CRITICAL FIX: Prevent total from exceeding 100%
            // If the new weight plus locked weights would exceed 100%, cap the new weight
            const maxAllowedWeight = 100 - lockedSubcategoriesTotal;
            if (clampedWeight > maxAllowedWeight) {
                // Cap the weight to prevent exceeding 100% total
                const cappedWeight = Math.max(0, maxAllowedWeight);
                setValue(`subcategoryWeights.${subcategoryId}`, cappedWeight);
                return;
            }
            
            // If there are no unlocked subcategories to redistribute to, just set the weight
            if (otherUnlockedSubcategories.length === 0) {
                setValue(`subcategoryWeights.${subcategoryId}`, clampedWeight);
                return;
            }
            
            // Check if the change would cause any unlocked subcategory to go below minimum threshold
            const minRequiredWeight = otherUnlockedSubcategories.length * MIN_WEIGHT_THRESHOLD;
            if (remainingWeight < minRequiredWeight) {
                // Calculate the maximum allowed weight for the current subcategory
                const maxAllowedWeight = 100 - lockedSubcategoriesTotal - minRequiredWeight;
                if (maxAllowedWeight < 0) {
                    // If even the minimum threshold can't be met, don't allow the change
                    return;
                }
                // Clamp the weight to the maximum allowed
                const adjustedWeight = Math.min(clampedWeight, maxAllowedWeight);
                setValue(`subcategoryWeights.${subcategoryId}`, adjustedWeight);
                return;
            }
            
            // If remaining weight is negative or zero, set all unlocked others to 0
            if (remainingWeight <= 0) {
                otherUnlockedSubcategories.forEach((subcat) => {
                    setValue(`subcategoryWeights.${subcat.id}`, 0);
                });
                setValue(`subcategoryWeights.${subcategoryId}`, clampedWeight);
                return;
            }

            if (otherUnlockedSubcategoriesTotal === 0) {
                // If other unlocked subcategories have no weight, distribute remaining weight equally
                // Ensure each gets at least the minimum threshold
                const equalWeight = Math.floor(remainingWeight / otherUnlockedSubcategories.length);
                const remainder = remainingWeight % otherUnlockedSubcategories.length;
                
                otherUnlockedSubcategories.forEach((subcat, index) => {
                    const baseWeight = equalWeight + (index < remainder ? 1 : 0);
                    const weight = Math.max(MIN_WEIGHT_THRESHOLD, baseWeight);
                    setValue(`subcategoryWeights.${subcat.id}`, weight);
                });
            } else {
                // Distribute remaining weight proportionally based on current weights of unlocked subcategories
                // Ensure each gets at least the minimum threshold
                otherUnlockedSubcategories.forEach((subcat) => {
                    const currentWeight = subcategoryWeights[subcat.id] || 0;
                    const proportionalWeight = Math.round((currentWeight / otherUnlockedSubcategoriesTotal) * remainingWeight);
                    const weight = Math.max(MIN_WEIGHT_THRESHOLD, proportionalWeight);
                    setValue(`subcategoryWeights.${subcat.id}`, weight);
                });
                
                // Adjust for rounding errors to ensure total is exactly 100
                // Calculate the new total: new weight + locked weights + redistributed unlocked weights
                const newUnlockedTotal = otherUnlockedSubcategories.reduce((sum, subcat) => {
                    const currentWeight = subcategoryWeights[subcat.id] || 0;
                    const proportionalWeight = Math.round((currentWeight / otherUnlockedSubcategoriesTotal) * remainingWeight);
                    const weight = Math.max(MIN_WEIGHT_THRESHOLD, proportionalWeight);
                    return sum + weight;
                }, 0);
                
                const newTotal = clampedWeight + lockedSubcategoriesTotal + newUnlockedTotal;
                
                if (newTotal !== 100) {
                    const difference = 100 - newTotal;
                    // Apply difference to the largest unlocked subcategory
                    const largestOther = otherUnlockedSubcategories.reduce((largest, current) => {
                        const currentWeight = subcategoryWeights[current.id] || 0;
                        const largestWeight = subcategoryWeights[largest.id] || 0;
                        return currentWeight > largestWeight ? current : largest;
                    });
                    
                    const currentWeight = subcategoryWeights[largestOther.id] || 0;
                    const proportionalWeight = Math.round((currentWeight / otherUnlockedSubcategoriesTotal) * remainingWeight);
                    const adjustedWeight = Math.max(MIN_WEIGHT_THRESHOLD, proportionalWeight + difference);
                    setValue(`subcategoryWeights.${largestOther.id}`, adjustedWeight);
                }
            }

            setValue(`subcategoryWeights.${subcategoryId}`, clampedWeight);
            
            // Notify parent component of weight change
            if (onWeightChange) {
                onWeightChange();
            }
            
            // Force form validation update
            setTimeout(() => {
                console.log('Weight changed, triggering form validation update');
            }, 0);
        },
        [categories, subcategoryWeights, setValue, lockedSubcategories, onWeightChange],
    );

    const resetToEqualWeights = useCallback(
        (categoryId: number) => {
            const category = categories.find((cat) => cat.id === categoryId);
            if (!category || !category.subCategories) return;

            // First, unlock all subcategories in this category
            const categorySubcategoryIds = category.subCategories.map(subcat => subcat.id);
            categorySubcategoryIds.forEach(subcatId => {
                if (lockedSubcategories.has(subcatId)) {
                    onToggleSubcategoryLock(subcatId);
                }
            });

            // Then distribute weights evenly among all subcategories
            const subcategoryCount = category.subCategories.length;
            const baseWeight = Math.floor(100 / subcategoryCount);
            const remainder = 100 % subcategoryCount;

            category.subCategories.forEach((subcat: SubCategory, index: number) => {
                const weight = index < remainder ? baseWeight + 1 : baseWeight;
                setValue(`subcategoryWeights.${subcat.id}`, weight);
            });
            
            // Notify parent component of weight change
            if (onWeightChange) {
                onWeightChange();
            }
        },
        [categories, setValue, lockedSubcategories, onToggleSubcategoryLock, onWeightChange],
    );


    // Memoize computed values to prevent unnecessary recalculations
    const getPieChartData = useCallback(
        (category: Category): PieChartData[] => {
            if (!category.subCategories) return [];

            const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8', '#82CA9D', '#FFC658', '#FF6B6B'];

            const chartData = category.subCategories.map((subcat, index) => ({
                name: subcat.subcategory_name,
                value: subcategoryWeights[subcat.id] || 0,
                color: COLORS[index % COLORS.length],
            }));
            return chartData;
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


    // Calculate overall weight status and find unset subcategories
    const overallStatus = useMemo(() => {
        const weights = watch('subcategoryWeights') || {};
        let totalSubcategories = 0;
        let subcategoriesWithWeights = 0;
        let categoriesWithValidWeights = 0;
        let totalCategories = 0;
        const unsetSubcategories: Array<{categoryId: number, subcategoryId: number}> = [];

        categories.forEach((category) => {
            if (category.subCategories && category.subCategories.length > 0) {
                totalCategories++;
                totalSubcategories += category.subCategories.length;

                const categoryWeights = category.subCategories.map((subcat) => weights[subcat.id] || 0);
                const totalWeight = categoryWeights.reduce((sum, weight) => sum + weight, 0);

                if (totalWeight === 100) {
                    categoriesWithValidWeights++;
                }

                category.subCategories.forEach((subcat, index) => {
                    const weight = categoryWeights[index];
                    if (weight > 0) {
                        subcategoriesWithWeights++;
                    } else {
                        // Only add to unset if it's not locked (locked subcategories can have 0% if user wants)
                        if (!lockedSubcategories.has(subcat.id)) {
                            unsetSubcategories.push({
                                categoryId: category.id,
                                subcategoryId: subcat.id
                            });
                        }
                    }
                });
            }
        });

        return {
            totalSubcategories,
            subcategoriesWithWeights,
            categoriesWithValidWeights,
            totalCategories,
            isComplete: subcategoriesWithWeights === totalSubcategories && categoriesWithValidWeights === totalCategories,
            unsetSubcategories,
        };
    }, [categories, watch, lockedSubcategories]);

    // Auto-focus on first unset subcategory
    React.useEffect(() => {
        if (overallStatus.unsetSubcategories.length > 0) {
            const firstUnset = overallStatus.unsetSubcategories[0];
            const inputElement = document.getElementById(`weight-${firstUnset.subcategoryId}`) as HTMLInputElement;
            if (inputElement) {
                // Small delay to ensure the element is rendered
                setTimeout(() => {
                    inputElement.focus();
                    inputElement.select();
                }, 100);
            }
        }
    }, [overallStatus.unsetSubcategories]);

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
            {/* Categories */}
            <div className="space-y-2">
                <h2 className="text-xl font-semibold">Assessment Criteria</h2>
                <p className="text-muted-foreground">Assign weights to each subcategory of each category. Each category must total exactly 100%.</p>
                {highlightInvalidCategories && (
                    <div className="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-lg">
                        <div className="flex-shrink-0">
                            <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                            </svg>
                        </div>
                        <div className="text-sm text-red-800 dark:text-red-200">
                            <strong>Cannot proceed:</strong> Each category must total exactly 100%. Please adjust the weights below to continue.
                            <div className="mt-2 text-xs text-red-700 dark:text-red-300">
                                Check the progress indicator above to see which categories need adjustment.
                            </div>
                        </div>
                    </div>
                )}
            </div>

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
                                        // Handle parsing error silently
                                    }
                                }}
                            />
                        </FormControl>
                    </FormItem>
                )}
            />

            {/* Overall Progress Indicator */}
            <div className="bg-muted/30 rounded-lg p-4 border border-border/50">
                <div className="flex items-center justify-between mb-3">
                    <h3 className="text-sm font-medium text-foreground">Overall Progress</h3>
                    <div className="text-sm text-muted-foreground">
                        {categoriesWithSubcategories.filter(cat => calculateCategoryTotal(cat.id) === 100).length} of {categoriesWithSubcategories.length} categories complete
                    </div>
                </div>
                <div className="space-y-2">
                    {categoriesWithSubcategories.map((category) => {
                        const total = calculateCategoryTotal(category.id);
                        const isComplete = total === 100;
                        return (
                            <div key={category.id} className="flex items-center gap-3">
                                <div className="flex-shrink-0 w-4 h-4">
                                    {isComplete ? (
                                        <div className="w-4 h-4 bg-green-500 dark:bg-green-400 rounded-full flex items-center justify-center">
                                            <svg className="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                        </div>
                                    ) : (
                                        <div className={`w-4 h-4 rounded-full ${
                                            total > 100 ? 'bg-red-500 dark:bg-red-400' : 'bg-yellow-500 dark:bg-yellow-400'
                                        }`}></div>
                                    )}
                                </div>
                                <div className="flex-1 min-w-0">
                                    <div className="text-sm font-medium text-foreground truncate">
                                        {category.category_name}
                                    </div>
                                </div>
                                <div className="flex-shrink-0 text-sm font-medium">
                                    <span className={isComplete ? 'text-green-600 dark:text-green-400' : total > 100 ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400'}>
                                        {total}%
                                    </span>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>

            <div className="space-y-6">
                {categoriesWithSubcategories.map((category) => {
                    expandedCategories.has(category.id);
                    const categoryTotal = calculateCategoryTotal(category.id);
                    const pieChartData = getPieChartData(category);
                    const isInvalid = categoryTotal !== 100;

                    return (
                        <Card 
                            key={category.id} 
                            id={`category-card-${category.category_name.toLowerCase().replace(/\s+/g, '-')}`}
                            className={`border-2 transition-all duration-300 ${
                                highlightInvalidCategories && isInvalid 
                                    ? 'ring-2 ring-red-500 ring-opacity-50 bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800 shadow-lg' 
                                    : isInvalid
                                    ? 'border-yellow-200 dark:border-yellow-800 bg-yellow-50/30 dark:bg-yellow-950/30'
                                    : 'border-green-200 dark:border-green-800 bg-green-50/30 dark:bg-green-950/30'
                            }`}
                        >
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle className="text-lg">{category.category_name}</CardTitle>
                                    <div className="flex items-center gap-4">
                                        <Button 
                                            type="button"
                                            variant="outline" 
                                            size="sm" 
                                            onClick={(e) => {
                                                e.preventDefault();
                                                resetToEqualWeights(category.id);
                                            }}
                                        >
                                            Reset to Equal
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>

                            <CardContent className="space-y-6">
                                {/* Three-Column Layout: Labels - Chart - Stats */}
                                <div className="flex flex-col lg:flex-row items-center justify-center gap-6 w-full">
                                    {/* Left: Weight Distribution Labels (Auto-sizing) */}
                                    <div className="flex-shrink-0">
                                        {pieChartData.length > 0 && pieChartData.some(item => item.value > 0) ? (
                                            <div className="space-y-2 min-w-[200px]">
                                                <h5 className="text-xs font-medium text-muted-foreground uppercase tracking-wide">Distribution</h5>
                                                <div className="space-y-1">
                                                    {pieChartData.map((item, index) => (
                                                        <div key={index} className="flex items-center justify-between py-1.5 gap-3">
                                                            <div className="flex items-center gap-2 min-w-0 flex-1">
                                                                <div 
                                                                    className="w-2.5 h-2.5 rounded-full flex-shrink-0" 
                                                                    style={{ backgroundColor: item.color }}
                                                                />
                                                                <span className="text-xs font-medium text-foreground truncate" title={item.name}>
                                                                    {item.name}
                                                                </span>
                                                            </div>
                                                            <span className="text-xs font-mono text-muted-foreground flex-shrink-0 text-right min-w-[3rem]">
                                                                {item.value}%
                                                            </span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="flex items-center justify-center h-24 w-48 bg-muted/30 rounded border border-dashed border-border/50">
                                                <div className="text-center">
                                                    <div className="text-muted-foreground text-xs">Set weights</div>
                                                </div>
                                            </div>
                                        )}
                                    </div>

                                    {/* Middle: Large Prominent Pie Chart */}
                                    <div className="flex-shrink-0 flex items-center justify-center">
                                        {pieChartData.length > 0 && pieChartData.some(item => item.value > 0) ? (
                                            <div className="relative" style={{ 
                                                width: `${Math.max(240, Math.min(320, 240 + (pieChartData.length * 12)))}px`,
                                                height: `${Math.max(240, Math.min(320, 240 + (pieChartData.length * 12)))}px`
                                            }}>
                                                <SimplePieChart
                                                    data={pieChartData}
                                                    title=""
                                                    totalWeight={100}
                                                    showLabels={false}
                                                    showLegend={false}
                                                    onSliceClick={(data) => {
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
                                        ) : (
                                            <div className="flex items-center justify-center w-80 h-80 bg-muted/30 rounded border border-dashed border-border/50">
                                                <div className="text-center">
                                                    <div className="text-muted-foreground text-lg">Set weights to see chart</div>
                                                </div>
                                            </div>
                                        )}
                                    </div>

                                    {/* Right: Fixed Stats */}
                                    <div className="flex-shrink-0">
                                        <div className="space-y-3 min-w-[140px]">
                                            {/* Completion Status */}
                                            <div className="text-center">
                                            <div className={`text-2xl font-bold ${
                                                categoryTotal === 100 ? 'text-green-600 dark:text-green-400' : 
                                                categoryTotal > 100 ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400'
                                            }`}>
                                                {categoryTotal}%
                                            </div>
                                                <div className={`text-xs font-medium ${
                                                    categoryTotal === 100 ? 'text-green-600 dark:text-green-400' : 
                                                    categoryTotal > 100 ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400'
                                                }`}>
                                                    {categoryTotal === 100 ? '✓ Complete' : 
                                                     categoryTotal > 100 ? '✗ Exceeds Limit' : '⚠ Incomplete'}
                                                </div>
                                            </div>

                                            {/* Compact Stats */}
                                            <div className="text-center space-y-1">
                                                <div className="text-xs text-muted-foreground">
                                                    {category.subCategories.length} subcategories
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    {category.subCategories.reduce((total, sub) => total + (sub.questions?.length || 0), 0)} questions
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                {/* Compact Weight Inputs */}
                                <div className="space-y-3">
                                    <div className="flex items-center justify-between">
                                        <h4 className="text-xs font-medium text-muted-foreground uppercase tracking-wide">Set Weights</h4>
                                        <div className="text-xs text-muted-foreground">
                                            Min: 1% per subcategory
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                        {category.subCategories.map((subcategory) => {
                                            const isQuestionsExpanded = expandedQuestions.has(subcategory.id);
                                            const subcategoryWeight = subcategoryWeights[subcategory.id] || 0;
                                            const questionCount = subcategory.questions ? subcategory.questions.length : 0;

                                            const isUnset = (subcategoryWeight === 0 || subcategoryWeight === undefined || subcategoryWeight === null) && !lockedSubcategories.has(subcategory.id);
                                            
                                            return (
                                                <div key={subcategory.id} className={`bg-muted/30 rounded-lg p-3 border border-border/50 transition-colors ${
                                                    lockedSubcategories.has(subcategory.id) 
                                                        ? 'bg-blue-50/50 dark:bg-blue-950/30 border-blue-200/50 dark:border-blue-800/50' 
                                                        : isUnset
                                                        ? 'bg-yellow-50/50 dark:bg-yellow-950/30 border-yellow-200/50 dark:border-yellow-800/50 ring-1 ring-yellow-200/50 dark:ring-yellow-800/50'
                                                        : 'hover:bg-muted/50'
                                                }`}>
                                                    <div className="flex items-center justify-between mb-2 gap-2">
                                                        <div className="flex-1 min-w-0">
                                                            <h5 className="font-medium text-foreground text-sm truncate flex items-center gap-1" title={subcategory.subcategory_name}>
                                                                {subcategory.subcategory_name}
                                                                {isUnset && (
                                                                    <span className="text-yellow-600 text-xs" title="This subcategory needs a weight">
                                                                        *
                                                                    </span>
                                                                )}
                                                            </h5>
                                                            <div className="flex items-center gap-2 text-xs text-muted-foreground mt-1">
                                                                <span className={`px-1.5 py-0.5 rounded text-xs font-medium ${
                                                                    subcategoryWeight === 0 ? 'bg-muted text-muted-foreground' :
                                                                    subcategoryWeight < 25 ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' :
                                                                    subcategoryWeight < 50 ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' :
                                                                    'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300'
                                                                }`}>
                                                                    {subcategoryWeight === 0 ? 'Unset' :
                                                                     subcategoryWeight < 25 ? 'Low' :
                                                                     subcategoryWeight < 50 ? 'Medium' : 'High'}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div className="flex items-center gap-1 flex-shrink-0">
                                                            <div className="relative">
                                                                <Input
                                                                    id={`weight-${subcategory.id}`}
                                                                    type="number"
                                                                    min="0"
                                                                    max="100"
                                                                    value={subcategoryWeight || ''}
                                                                    onChange={(e) => {
                                                                        const value = e.target.value;
                                                                        if (value === '') {
                                                                            handleWeightChange(category.id, subcategory.id, 0);
                                                                        } else {
                                                                            const numValue = Number(value);
                                                                            if (!isNaN(numValue)) {
                                                                                handleWeightChange(category.id, subcategory.id, numValue);
                                                                            }
                                                                        }
                                                                    }}
                                                                    onFocus={(e) => e.target.select()}
                                                                    disabled={lockedSubcategories.has(subcategory.id)}
                                                                    title={lockedSubcategories.has(subcategory.id) ? 'This subcategory is locked. Unlock to edit weight.' : ''}
                                                                    className={`w-16 h-7 text-center text-xs pr-6 pl-1 ${
                                                                        lockedSubcategories.has(subcategory.id) 
                                                                            ? 'bg-muted text-muted-foreground cursor-not-allowed' 
                                                                            : ''
                                                                    }`}
                                                                />
                                                                <span className="absolute right-2 top-1/2 transform -translate-y-1/2 text-xs text-muted-foreground pointer-events-none">%</span>
                                                            </div>
                                                            <Button
                                                                type="button"
                                                                variant={lockedSubcategories.has(subcategory.id) ? "default" : "outline"}
                                                                size="sm"
                                                                onClick={(e) => {
                                                                    e.preventDefault();
                                                                    onToggleSubcategoryLock(subcategory.id);
                                                                }}
                                                                className="h-7 w-7 p-0 flex items-center justify-center"
                                                                title={lockedSubcategories.has(subcategory.id) ? 'Unlock this subcategory' : 'Lock this subcategory'}
                                                            >
                                                                {lockedSubcategories.has(subcategory.id) ? (
                                                                    <svg className="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                                    </svg>
                                                                ) : (
                                                                    <svg className="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                                    </svg>
                                                                )}
                                                            </Button>
                                                        </div>
                                                    </div>

                                                    {/* Compact Progress Bar */}
                                                    <div className="space-y-1">
                                                        <div className="flex justify-between text-xs text-muted-foreground">
                                                            <span>0%</span>
                                                            <span className="font-medium">{subcategoryWeight}%</span>
                                                            <span>100%</span>
                                                        </div>
                                                        <div className="h-1.5 w-full rounded-full bg-muted relative overflow-hidden">
                                                            <div
                                                                className={`h-1.5 rounded-full transition-all duration-300 ${
                                                                    subcategoryWeight === 0 ? 'bg-muted-foreground' :
                                                                    subcategoryWeight < 25 ? 'bg-blue-500 dark:bg-blue-400' :
                                                                    subcategoryWeight < 50 ? 'bg-green-500 dark:bg-green-400' :
                                                                    'bg-orange-500 dark:bg-orange-400'
                                                                }`}
                                                            style={{ width: `${subcategoryWeight}%` }}
                                                        ></div>
                                                        </div>
                                                    </div>

                                                    {/* Compact Questions Toggle */}
                                                    <Collapsible open={isQuestionsExpanded} onOpenChange={() => toggleQuestions(subcategory.id)}>
                                                        <CollapsibleTrigger asChild>
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                className="h-5 w-full text-xs text-muted-foreground hover:text-foreground mt-1"
                                                            >
                                                                <span>Questions</span>
                                                                <ChevronDownIcon
                                                                    className={`h-3 w-3 ml-1 transition-transform duration-200 ${
                                                                        isQuestionsExpanded ? 'rotate-180' : ''
                                                                    }`}
                                                                />
                                                            </Button>
                                                        </CollapsibleTrigger>
                                                        <CollapsibleContent className="mt-1">
                                                            <div className="max-h-20 space-y-1 overflow-y-auto border-t pt-1">
                                                                {subcategory.questions && subcategory.questions.length > 0 ? (
                                                                    subcategory.questions.map((question, qIndex) => (
                                                                        <div
                                                                            key={question.id}
                                                                            className="text-xs text-muted-foreground border-l border-border pl-2"
                                                                        >
                                                                            <span className="font-medium">Q{qIndex + 1}:</span> {question.question}
                                                                        </div>
                                                                    ))
                                                                ) : (
                                                                    <div className="text-xs text-muted-foreground pl-2">No questions</div>
                                                                )}
                                                            </div>
                                                        </CollapsibleContent>
                                                    </Collapsible>
                                                </div>
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
