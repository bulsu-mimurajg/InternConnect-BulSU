import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { useFormContext } from 'react-hook-form';
import { useEffect, useState } from 'react';
import { useFormFields } from '@/contexts/FormFieldsContext';

interface Choice {
    id: number;
    choice_text: string;
    is_correct: boolean;
}

interface Question {
    id: number;
    question: string;
    question_type: string;
    subcategory_id: number;
    subcategory_name?: string;
    choices: Choice[];
}

interface QuizSection {
    subcategory_name: string;
    questions: Question[];
}

interface QuizQuestionsProps {
    category?: {
        name: string;
        questions: Array<{
            id: number;
            question: string;
            subcategory_name: string;
            choices: Array<{
                id: number;
                choice_text: string;
                is_correct: boolean;
            }>;
        }>;
    };
}

export default function QuizQuestions({ category }: QuizQuestionsProps) {
    const { control, trigger } = useFormContext();
    const { setQuizFields } = useFormFields();
    const [quizSections, setQuizSections] = useState<QuizSection[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (!category) return;
        
        try {
            setLoading(true);
            
            // Group questions by subcategory
            const sectionsMap = new Map<string, Question[]>();
            
            category.questions.forEach((q) => {
                const subcategoryName = q.subcategory_name || 'Uncategorized';
                if (!sectionsMap.has(subcategoryName)) {
                    sectionsMap.set(subcategoryName, []);
                }
                sectionsMap.get(subcategoryName)?.push(q as Question);
            });
            
            const sections: QuizSection[] = Array.from(sectionsMap.entries()).map(([name, questions]) => ({
                subcategory_name: name,
                questions,
            }));
            
            setQuizSections(sections);
            
            // Register fields for validation
            const fields = category.questions.map((question) => {
                const categoryNameClean = category.name.toLowerCase().replace(/[+/\s-]/g, '_');
                return `${categoryNameClean}_${question.id}`;
            });
            
            setQuizFields(fields);
            setError(null);
        } catch (err) {
            console.error('Failed to process quiz questions:', err);
            setError('Failed to load quiz questions');
        } finally {
            setLoading(false);
        }
    }, [category, setQuizFields]);

    if (loading) {
        return (
            <div className="flex flex-col gap-4">
                <div className="animate-pulse">
                    <div className="h-6 bg-gray-200 rounded w-3/4 mb-4"></div>
                    <div className="space-y-2">
                        <div className="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div className="h-4 bg-gray-200 rounded w-2/3"></div>
                    </div>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex flex-col gap-4">
                <div className="text-red-600">
                    <p>Error: {error}</p>
                    <p>Please refresh the page to try again.</p>
                </div>
            </div>
        );
    }

    if (quizSections.length === 0) {
        return (
            <div className="flex flex-col gap-4">
                <p className="text-muted-foreground">No quiz questions available.</p>
            </div>
        );
    }

    return (
        <div className="flex flex-col gap-4">
            <div>
                <h1 className="text-lg font-semibold mb-4">
                    Please answer the following multiple choice questions:
                </h1>
            </div>

            <div className="max-h-[500px] overflow-y-auto pr-2">
                {quizSections.map((section) => (
                    <div key={section.subcategory_name} className="mb-8">
                        <h2 className="text-md font-semibold mb-4">{section.subcategory_name}</h2>
                        {section.questions.map((question) => {
                            const categoryName = category?.name.toLowerCase().replace(/[+/\s-]/g, '_') || 'general';
                            const fieldName = `${categoryName}_${question.id}`;
                            return (
                                <FormField
                                    key={question.id}
                                    control={control}
                                    name={fieldName}
                                    render={({ field }) => (
                                        <FormItem className="my-6 space-y-4" data-field-name={fieldName}>
                                            <FormLabel className="text-base font-medium">
                                                {question.question} <span className="text-red-500">*</span>
                                            </FormLabel>
                                            <FormControl>
                                                <RadioGroup
                                                    onValueChange={(val) => {
                                                            field.onChange(val);
                                                            trigger(field.name);
                                                        }}
                                                    value={field.value?.toString() ?? ''}
                                                    className="flex flex-row gap-4 md:gap-6 flex-wrap"
                                                >
                                                    {question.choices.map((choice, index) => {
                                                        const letters = ['A', 'B', 'C', 'D', 'E', 'F'];
                                                        const letter = letters[index] || String.fromCharCode(65 + index);
                                                        return (
                                                            <div key={choice.id} className="flex items-center space-x-2 min-w-[200px] md:min-w-[240px]">
                                                                <FormControl>
                                                                    <RadioGroupItem value={choice.id.toString()} id={`${question.id}-${choice.id}`} />
                                                                </FormControl>
                                                                <FormLabel 
                                                                    htmlFor={`${question.id}-${choice.id}`}
                                                                    className="font-normal cursor-pointer flex items-center gap-2"
                                                                >
                                                                    <span className="font-semibold text-primary mr-1">{letter}.</span>
                                                                    {choice.choice_text}
                                                                </FormLabel>
                                                            </div>
                                                        );
                                                    })}
                                                </RadioGroup>
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    )}
                                />
                            );
                        })}
                    </div>
                ))}
            </div>
        </div>
    );
}

