import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { PieChart } from '@/components/ui/pie-chart';
import { useFormContext } from 'react-hook-form';
import { useCallback, useMemo } from 'react';
import { ChevronDownIcon, ChevronRightIcon } from '@radix-ui/react-icons';
import { Eye, EyeOff } from 'lucide-react';
import { usePage } from '@inertiajs/react';
import { type SharedData } from '@/types';
import React from 'react'; // Added missing import

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
}

export default function Criteria({
    categories,
    loading,
    expandedCategories,
    expandedSubcategories,
    expandedQuestions,
    setExpandedCategories,
    setExpandedSubcategories,
    setExpandedQuestions
}: CriteriaProps) {
    const { control, watch, setValue } = useFormContext();
    const { auth } = usePage<SharedData>().props;

    // Watch the weight values for validation
    const subcategoryWeights: Record<string, number> = watch('subcategoryWeights') || {};

    // Debug: Log the current weights state
    console.log('Criteria Component - Current subcategoryWeights:', subcategoryWeights);
    console.log('Criteria Component - Weights count:', Object.keys(subcategoryWeights).length);

    // Initialize default weights when categories are loaded
    React.useEffect(() => {
        if (categories.length > 0 && Object.keys(subcategoryWeights).length === 0) {
            console.log('Initializing default weights for categories');
            categories.forEach(category => {
                if (category.subCategories && category.subCategories.length > 0) {
                    const equalWeight = Math.round(100 / category.subCategories.length);
                    const remainder = 100 % category.subCategories.length;
                    
                    category.subCategories.forEach((subcat: any, index: number) => {
                        const weight = index < remainder ? equalWeight + 1 : equalWeight;
                        console.log('Setting default weight for subcategory:', subcat.id, 'to:', weight);
                        setValue(`subcategoryWeights.${subcat.id}`, weight);
                    });
                }
            });
        }
    }, [categories, setValue, subcategoryWeights]);

    // Memoize toggle functions to prevent unnecessary re-renders
    const toggleCategory = useCallback((categoryId: number) => {
        setExpandedCategories(prev => {
            const newExpanded = new Set(prev);
            if (newExpanded.has(categoryId)) {
                newExpanded.delete(categoryId);
            } else {
                newExpanded.add(categoryId);
            }
            return newExpanded;
        });
    }, [setExpandedCategories]);

    const toggleSubcategory = useCallback((subcategoryId: number) => {
        setExpandedSubcategories(prev => {
            const newExpanded = new Set(prev);
            if (newExpanded.has(subcategoryId)) {
                newExpanded.delete(subcategoryId);
            } else {
                newExpanded.add(subcategoryId);
            }
            return newExpanded;
        });
    }, [setExpandedSubcategories]);

    const toggleQuestions = useCallback((subcategoryId: number) => {
        setExpandedQuestions(prev => {
            const newExpanded = new Set(prev);
            if (newExpanded.has(subcategoryId)) {
                newExpanded.delete(subcategoryId);
            } else {
                newExpanded.add(subcategoryId);
            }
            return newExpanded;
        });
    }, [setExpandedQuestions]);

    const handleWeightChange = useCallback((categoryId: number, subcategoryId: number, newWeight: number) => {
        console.log('handleWeightChange called:', { categoryId, subcategoryId, newWeight });
        
        const category = categories.find(cat => cat.id === categoryId);
        if (!category || !category.subCategories) return;

        // Ensure weight is within valid range
        const clampedWeight = Math.max(0, Math.min(100, newWeight));

        const subcategories = category.subCategories;
        const currentTotal = subcategories.reduce((sum, subcat) => {
            if (subcat.id === subcategoryId) return sum + clampedWeight;
            return sum + (subcategoryWeights[subcat.id] || 0);
        }, 0);

        // If the new weight would exceed 100%, adjust other subcategories proportionally
        if (currentTotal > 100) {
            const excess = currentTotal - 100;
            const otherSubcategories = subcategories.filter(subcat => subcat.id !== subcategoryId);
            const otherTotal = otherSubcategories.reduce((sum, subcat) => sum + (subcategoryWeights[subcat.id] || 0), 0);
            
            if (otherTotal > 0) {
                otherSubcategories.forEach(subcat => {
                    const currentWeight = subcategoryWeights[subcat.id] || 0;
                    const proportion = currentWeight / otherTotal;
                    const adjustedWeight = Math.max(0, currentWeight - (excess * proportion));
                    setValue(`subcategoryWeights.${subcat.id}`, Math.round(adjustedWeight));
                });
            }
        }

        // Set the new weight for the changed subcategory
        console.log('Setting weight for subcategory:', subcategoryId, 'to:', clampedWeight);
        setValue(`subcategoryWeights.${subcategoryId}`, clampedWeight);
        
        // Debug: Log the updated weights after setting
        setTimeout(() => {
            const updatedWeights = watch('subcategoryWeights');
            console.log('Updated weights after setValue:', updatedWeights);
        }, 100);
    }, [categories, subcategoryWeights, setValue, watch]);

    const resetToEqualWeights = useCallback((categoryId: number) => {
        const category = categories.find(cat => cat.id === categoryId);
        if (!category || !category.subCategories) return;

        const subcategories = category.subCategories;
        const equalWeight = Math.round(100 / subcategories.length);
        const remainder = 100 % subcategories.length;
        
        subcategories.forEach((subcat: SubCategory, index: number) => {
            const weight = index < remainder ? equalWeight + 1 : equalWeight;
            setValue(`subcategoryWeights.${subcat.id}`, weight);
        });
    }, [categories, setValue]);

    // Memoize computed values to prevent unnecessary recalculations
    const getPieChartData = useCallback((category: Category): PieChartData[] => {
        if (!category.subCategories) return [];
        
        const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8', '#82CA9D', '#FFC658', '#FF6B6B'];
        
        return category.subCategories.map((subcat, index) => ({
            name: subcat.subcategory_name,
            value: subcategoryWeights[subcat.id] || 0,
            color: COLORS[index % COLORS.length]
        }));
    }, [subcategoryWeights]);

    const calculateCategoryTotal = useCallback((categoryId: number) => {
        const category = categories.find(cat => cat.id === categoryId);
        if (!category || !category.subCategories) return 0;
        
        return category.subCategories.reduce((sum, subcat) => {
            const weight = subcategoryWeights[subcat.id] || 0;
            return sum + (Number(weight) || 0);
        }, 0);
    }, [categories, subcategoryWeights]);

    const getWeightValidationColor = useCallback((total: number) => {
        if (total === 100) return 'text-green-600';
        if (total > 100) return 'text-red-600';
        return 'text-yellow-600';
    }, []);

    // Memoize filtered categories to prevent unnecessary re-filtering
    const categoriesWithSubcategories = useMemo(() => {
        return categories.filter(category => {
            const hasSubCategories = category.subCategories && category.subCategories.length > 0;
            return hasSubCategories;
        });
    }, [categories]);

    if (loading) {
        return (
            <div className="flex items-center justify-center p-8">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto"></div>
                    <p className="mt-2 text-gray-600">Loading criteria...</p>
                </div>
            </div>
        );
    }

    // Check if user is authenticated and has HTE role
    if (!auth.user || auth.role !== 'hte') {
        return (
            <div className="text-center py-8">
                <div className="text-red-500 mb-4">
                    <svg className="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 className="text-lg font-medium text-gray-900 mb-2">Authentication Error</h3>
                <p className="text-gray-600">You must be logged in as an HTE user to access this page.</p>
            </div>
        );
    }

    if (categoriesWithSubcategories.length === 0) {
        return (
            <div className="text-center py-8">
                <div className="text-gray-500 mb-4">
                    <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 className="text-lg font-medium text-gray-900 mb-2">No Assessment Criteria Available</h3>
                <p className="text-gray-600">There are currently no categories with subcategories set up in the system. Please contact an administrator to configure the assessment criteria.</p>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <h2 className="text-xl font-semibold">Assessment Criteria</h2>
            <p className="text-gray-600 dark:text-gray-400">
                Assign weights to each subcategory. Weights within each category must total 100%.
            </p>

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
                    const isExpanded = expandedCategories.has(category.id);
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
                                            <div className={`px-2 py-1 rounded-full text-xs font-medium ${
                                                categoryTotal === 100 
                                                    ? 'bg-green-100 text-green-800' 
                                                    : categoryTotal > 100 
                                                    ? 'bg-red-100 text-red-800' 
                                                    : 'bg-yellow-100 text-yellow-800'
                                            }`}>
                                                {categoryTotal === 100 ? '✓ Valid' : 
                                                 categoryTotal > 100 ? '✗ Exceeds 100%' : '⚠ Incomplete'}
                                            </div>
                                        </div>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => resetToEqualWeights(category.id)}
                                        >
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
                                        onSliceClick={(data, index) => {
                                            // Find the subcategory by name and focus on its weight input
                                            const subcategory = category.subCategories.find(sub => sub.subcategory_name === data.name);
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
                                <div className="bg-gray-50 p-4 rounded-lg">
                                    <h5 className="font-medium text-gray-700 mb-3">Weight Distribution Summary</h5>
                                    
                                    {/* Progress Bar */}
                                    <div className="mb-4">
                                        <div className="flex items-center justify-between text-sm text-gray-600 mb-2">
                                            <span>Total Weight: {categoryTotal}%</span>
                                            <span>Target: 100%</span>
                                        </div>
                                        <div className="w-full bg-gray-200 rounded-full h-2">
                                            <div 
                                                className={`h-2 rounded-full transition-all duration-300 ${
                                                    categoryTotal === 100 
                                                        ? 'bg-green-500' 
                                                        : categoryTotal > 100 
                                                        ? 'bg-red-500' 
                                                        : 'bg-yellow-500'
                                                }`}
                                                style={{ width: `${Math.min(categoryTotal, 100)}%` }}
                                            ></div>
                                        </div>
                                    </div>
                                    
                                    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                        {category.subCategories.map((subcat, index) => {
                                            const weight = subcategoryWeights[subcat.id] || 0;
                                            const percentage = ((weight / 100) * 100).toFixed(1);
                                            const questionCount = subcat.questions ? subcat.questions.length : 0;
                                            return (
                                                <div key={subcat.id} className="text-center">
                                                    <div className="text-lg font-semibold text-blue-600">{weight}%</div>
                                                    <div className="text-xs text-gray-600">{subcat.subcategory_name}</div>
                                                    <div className="text-xs text-gray-500">{percentage}% of total</div>
                                                    <div className="text-xs text-gray-400">{questionCount} question{questionCount !== 1 ? 's' : ''}</div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>

                                {/* Subcategories with Weight Inputs */}
                                <div className="space-y-4">
                                    <h4 className="font-medium text-gray-700">Subcategory Weights</h4>
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        {category.subCategories.map((subcategory) => {
                                            const isSubExpanded = expandedSubcategories.has(subcategory.id);
                                            const isQuestionsExpanded = expandedQuestions.has(subcategory.id);
                                            const subcategoryWeight = subcategoryWeights[subcategory.id] || 0;
                                            
                                            return (
                                                <Card key={subcategory.id} className="border">
                                                    <CardHeader className="pb-3">
                                                        <Collapsible 
                                                            open={isQuestionsExpanded} 
                                                            onOpenChange={() => toggleQuestions(subcategory.id)}
                                                        >
                                                            <CollapsibleTrigger asChild>
                                                                <Button
                                                                    variant="ghost"
                                                                    className="w-full justify-between p-0 h-auto font-normal hover:bg-transparent"
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
                                                                    onChange={(e) => handleWeightChange(category.id, subcategory.id, Number(e.target.value))}
                                                                    className="w-20"
                                                                />
                                                                <span className="text-sm">%</span>
                                                            </div>
                                                            
                                                            {/* Weight Bar */}
                                                            <div className="w-full bg-gray-200 rounded-full h-2">
                                                                <div 
                                                                    className="bg-blue-500 h-2 rounded-full transition-all duration-300"
                                                                    style={{ width: `${subcategoryWeight}%` }}
                                                                ></div>
                                                            </div>
                                                            <div className="text-xs text-gray-500 text-center">
                                                                {subcategoryWeight}% of category total
                                                            </div>
                                                        </div>

                                                        {/* Questions - Now revealed with dropdown animation */}
                                                        <Collapsible open={isQuestionsExpanded}>
                                                            <CollapsibleContent className="space-y-2 animate-in fade-in-0 slide-in-from-top-2 duration-200">
                                                                <h5 className="text-xs font-medium text-gray-600">Questions ({subcategory.questions ? subcategory.questions.length : 0}):</h5>
                                                                <div className="space-y-1 max-h-32 overflow-y-auto">
                                                                    {subcategory.questions && subcategory.questions.length > 0 ? (
                                                                        subcategory.questions.map((question, qIndex) => (
                                                                            <div key={question.id} className="text-xs text-gray-600 pl-2 border-l-2 border-gray-200">
                                                                                <span className="font-medium text-gray-700">Q{qIndex + 1}:</span> {question.question}
                                                                            </div>
                                                                        ))
                                                                    ) : (
                                                                        <div className="text-xs text-gray-500 pl-2">No questions available</div>
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
