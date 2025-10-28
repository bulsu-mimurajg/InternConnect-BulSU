import { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { CheckCircle, XCircle, FileText, ChevronDown, Loader2 } from 'lucide-react';

interface Answer {
    id: number;
    text: string;
    is_correct: boolean;
    display_order: number;
}

interface QuestionResponse {
    question_id: number;
    question_text: string;
    code_snippet?: string;
    question_type: string;
    points: number;
    all_answers: Answer[];
    selected_answer_id: number;
    selected_answer_text: string;
    is_correct: boolean;
    points_earned: number;
    submitted_at: string;
}

interface Subcategory {
    subcategory_name: string;
    questions: QuestionResponse[];
}

interface CategoryResponse {
    category_name: string;
    subcategories: Subcategory[];
}

interface AssessmentData {
    student_name: string;
    submitted_at: string;
    categories: CategoryResponse[];
}

export function ViewAssessmentAnswers() {
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [data, setData] = useState<AssessmentData | null>(null);
    const [expandedCategories, setExpandedCategories] = useState<Set<string>>(new Set());
    const [expandedSubcategories, setExpandedSubcategories] = useState<Set<string>>(new Set());

    useEffect(() => {
        if (open && !data) {
            fetchAssessmentResponses();
        }
    }, [open]);

    const fetchAssessmentResponses = async () => {
        setLoading(true);
        try {
            const response = await fetch('/assessment/responses');
            if (!response.ok) {
                const errorData = await response.json();
                console.error('API Error:', errorData);
                throw new Error(errorData.error || 'Failed to fetch');
            }
            const responseData = await response.json();
            console.log('Assessment responses:', responseData);
            setData(responseData);
        } catch (error) {
            console.error('Failed to fetch assessment responses:', error);
            setData(null);
        } finally {
            setLoading(false);
        }
    };

    const toggleCategory = (categoryName: string) => {
        setExpandedCategories(prev => {
            const newSet = new Set(prev);
            if (newSet.has(categoryName)) {
                newSet.delete(categoryName);
            } else {
                newSet.add(categoryName);
            }
            return newSet;
        });
    };

    const toggleSubcategory = (subcategoryName: string) => {
        setExpandedSubcategories(prev => {
            const newSet = new Set(prev);
            if (newSet.has(subcategoryName)) {
                newSet.delete(subcategoryName);
            } else {
                newSet.add(subcategoryName);
            }
            return newSet;
        });
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="outline" size="sm" className="gap-2">
                    <FileText className="h-4 w-4" />
                    View Assessment Answers
                </Button>
            </DialogTrigger>
            <DialogContent className="max-w-4xl max-h-[80vh]">
                <DialogHeader>
                    <DialogTitle>My Assessment Answers</DialogTitle>
                </DialogHeader>

                {loading ? (
                    <div className="flex items-center justify-center py-12">
                        <Loader2 className="h-8 w-8 animate-spin text-primary" />
                    </div>
                ) : data ? (
                    <div className="h-[60vh] overflow-y-auto pr-4">
                        {data.categories && data.categories.length > 0 ? (
                            <div className="space-y-4">
                                <div className="bg-muted/50 p-4 rounded-lg">
                                    <p className="text-sm text-muted-foreground">
                                        Submitted: {new Date(data.submitted_at).toLocaleDateString('en-US', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })}
                                    </p>
                                </div>

                                {data.categories.map((category) => (
                                <div key={category.category_name} className="border rounded-lg">
                                    <Collapsible
                                        open={expandedCategories.has(category.category_name)}
                                        onOpenChange={() => toggleCategory(category.category_name)}
                                    >
                                        <CollapsibleTrigger className="w-full p-4 hover:bg-muted/50 transition-colors">
                                            <div className="flex items-center justify-between">
                                                <h3 className="text-lg font-semibold">{category.category_name}</h3>
                                                <ChevronDown className={`h-5 w-5 transition-transform ${expandedCategories.has(category.category_name) ? 'rotate-180' : ''}`} />
                                            </div>
                                        </CollapsibleTrigger>
                                        <CollapsibleContent>
                                            <div className="px-4 pb-4 space-y-3">
                                                {category.subcategories.map((subcategory) => (
                                                    <div key={subcategory.subcategory_name} className="border rounded-lg">
                                                        <Collapsible
                                                            open={expandedSubcategories.has(subcategory.subcategory_name)}
                                                            onOpenChange={() => toggleSubcategory(subcategory.subcategory_name)}
                                                        >
                                                            <CollapsibleTrigger className="w-full p-3 hover:bg-muted/50 transition-colors">
                                                                <div className="flex items-center justify-between">
                                                                    <h4 className="font-medium">{subcategory.subcategory_name}</h4>
                                                                    <div className="flex items-center gap-2">
                                                                        <span className="text-sm text-muted-foreground">
                                                                            {subcategory.questions.length} question{subcategory.questions.length !== 1 ? 's' : ''}
                                                                        </span>
                                                                        <ChevronDown className={`h-4 w-4 transition-transform ${expandedSubcategories.has(subcategory.subcategory_name) ? 'rotate-180' : ''}`} />
                                                                    </div>
                                                                </div>
                                                            </CollapsibleTrigger>
                                                            <CollapsibleContent>
                                                                <div className="p-3 space-y-4">
                                                                    {subcategory.questions.map((question, index) => (
                                                                        <div key={question.question_id} className="bg-muted/30 p-4 rounded-lg space-y-3">
                                                                            <div className="flex items-start justify-between gap-2">
                                                                                <p className="font-medium">
                                                                                    {index + 1}. {question.question_text}
                                                                                </p>
                                                                                {question.is_correct ? (
                                                                                    <CheckCircle className="h-5 w-5 text-green-600 flex-shrink-0" />
                                                                                ) : (
                                                                                    <XCircle className="h-5 w-5 text-red-600 flex-shrink-0" />
                                                                                )}
                                                                            </div>

                                                                            {question.code_snippet && (
                                                                                <div className="my-3 p-4 bg-gray-900 dark:bg-gray-800 rounded-lg border border-gray-700">
                                                                                    <pre className="text-sm text-gray-100 overflow-x-auto">
                                                                                        <code>{question.code_snippet}</code>
                                                                                    </pre>
                                                                                </div>
                                                                            )}

                                                                            <div className="space-y-2">
                                                                                {question.all_answers.map((answer) => {
                                                                                    const isSelected = answer.id === question.selected_answer_id;
                                                                                    const isCorrect = answer.is_correct;

                                                                                    return (
                                                                                        <div
                                                                                            key={answer.id}
                                                                                            className={`p-2 rounded ${
                                                                                                isSelected && isCorrect
                                                                                                    ? 'bg-green-100 border border-green-300 dark:bg-green-950 dark:border-green-800'
                                                                                                    : isSelected && !isCorrect
                                                                                                    ? 'bg-red-100 border border-red-300 dark:bg-red-950 dark:border-red-800'
                                                                                                    : isCorrect
                                                                                                    ? 'bg-green-50 border border-green-200 dark:bg-green-950/50 dark:border-green-900'
                                                                                                    : 'bg-background'
                                                                                            }`}
                                                                                        >
                                                                                            <div className="flex items-center gap-2">
                                                                                                {isSelected && (
                                                                                                    <span className="text-xs font-medium">
                                                                                                        Your answer:
                                                                                                    </span>
                                                                                                )}
                                                                                                {isCorrect && !isSelected && (
                                                                                                    <span className="text-xs font-medium text-green-700 dark:text-green-400">
                                                                                                        Correct answer:
                                                                                                    </span>
                                                                                                )}
                                                                                                <span>{answer.text}</span>
                                                                                            </div>
                                                                                        </div>
                                                                                    );
                                                                                })}
                                                                            </div>

                                                                            <div className="flex items-center justify-between text-sm text-muted-foreground pt-2 border-t">
                                                                                <span>Points: {question.points_earned} / {question.points}</span>
                                                                            </div>
                                                                        </div>
                                                                    ))}
                                                                </div>
                                                            </CollapsibleContent>
                                                        </Collapsible>
                                                    </div>
                                                ))}
                                            </div>
                                        </CollapsibleContent>
                                    </Collapsible>
                                </div>
                            ))}
                        </div>
                        ) : (
                            <div className="text-center py-12">
                                <p className="text-muted-foreground">No assessment responses found.</p>
                                <p className="text-sm text-muted-foreground mt-2">
                                    Your answers may not have been saved properly.
                                </p>
                            </div>
                        )}
                    </div>
                ) : (
                    <div className="text-center py-12 text-muted-foreground">
                        No assessment data available
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}

