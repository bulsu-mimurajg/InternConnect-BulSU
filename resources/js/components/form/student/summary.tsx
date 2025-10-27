import { useFormContext } from 'react-hook-form';
import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';

interface Choice {
    id: number;
    choice_text: string;
    is_correct: boolean;
}

interface Question {
    id: number;
    question: string;
    subcategory_name: string;
    choices: Choice[];
}

interface Category {
    name: string;
    questions: Question[];
}

interface AdditionalInfo {
    id: number;
    info_name: string;
    is_active: boolean;
}

interface PageProps extends Record<string, unknown> {
    additionalInfos?: AdditionalInfo[];
}

interface PersonalField {
    name: string;
    label: string;
}

interface QuizAnswer {
    fieldName: string;
    question: string;
    answer: string;
}

interface SummaryData {
    additionalInfo: PersonalField[];
    quizAnswers: QuizAnswer[];
}

export default function Summary() {
    const { watch } = useFormContext();
    const formData = watch();
    const { additionalInfos = [] } = usePage<PageProps>().props;
    const [categories, setCategories] = useState<Category[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchCategories = async () => {
            try {
                setLoading(true);
                const response = await fetch('/assessment/categories-quiz');
                const data = await response.json();
                setCategories(data);
            } catch (error) {
                console.error('Error fetching categories:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchCategories();
    }, []);

    // Create additional info fields
    const additionalInfoFields = additionalInfos.map((info) => ({
        name: info.info_name.toLowerCase().replace(/[ -]/g, '_'),
        label: info.info_name,
    }));

    // Function to get choice text by ID
    const getChoiceText = (question: Question, choiceId: string) => {
        const choice = question.choices.find(c => c.id.toString() === choiceId);
        return choice ? choice.choice_text : 'Not answered';
    };

    // Get all quiz answers from form data
    const getQuizAnswers = () => {
        const answers: QuizAnswer[] = [];
        
        categories.forEach(category => {
            category.questions.forEach(question => {
                const fieldName = `${category.name.toLowerCase().replace(/[+\/\s-]/g, '_')}_${question.id}`;
                const answer = formData[fieldName];
                if (answer) {
                    answers.push({
                        fieldName,
                        question: question.question,
                        answer: getChoiceText(question, answer)
                    });
                }
            });
        });
        
        return answers;
    };

    const summaryData: SummaryData = {
        additionalInfo: additionalInfoFields,
        quizAnswers: getQuizAnswers(),
    };

    const renderAdditionalInfo = () => (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
            {summaryData.additionalInfo.map((field: PersonalField) => (
                <div key={field.name} className="flex justify-between items-center">
                    <span className="font-medium text-gray-700">{field.label}:</span>
                    <span className="text-gray-900">
                        {formData[field.name] ? formData[field.name] : 'Not answered'}
                    </span>
                </div>
            ))}
        </div>
    );

    const renderQuizAnswers = () => {
        // Group answers by category
        const answersByCategory = new Map<string, QuizAnswer[]>();
        
        summaryData.quizAnswers.forEach(answer => {
            categories.forEach(category => {
                if (answer.fieldName.startsWith(category.name.toLowerCase().replace(/[+\/\s-]/g, '_'))) {
                    if (!answersByCategory.has(category.name)) {
                        answersByCategory.set(category.name, []);
                    }
                    answersByCategory.get(category.name)?.push(answer);
                }
            });
        });

        return (
            <div className="space-y-4">
                {Array.from(answersByCategory.entries()).map(([categoryName, answers]) => (
                    <div key={categoryName} className="border-t pt-3">
                        <h4 className="font-semibold text-gray-800 mb-3">{categoryName}</h4>
                        <div className="space-y-3">
                            {answers.map((answer, index) => (
                                <div key={answer.fieldName} className="flex flex-col gap-1">
                                    <span className="text-gray-600 text-sm font-medium">{answer.question}</span>
                                    <span className="text-gray-900 font-medium pl-4">{answer.answer}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        );
    };

    if (loading) {
        return (
            <div className="flex flex-col gap-4">
                <div className="animate-pulse">
                    <div className="h-6 bg-gray-200 rounded w-3/4 mb-4"></div>
                    <div className="space-y-2">
                        <div className="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div className="h-4 bg-gray-200 rounded w-2/3"></div>
                        <div className="h-4 bg-gray-200 rounded w-1/3"></div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="max-h-[500px] overflow-y-auto pr-2 space-y-6">
            {/* Additional Information Section */}
            {summaryData.additionalInfo.length > 0 && (
                <div className="border rounded-lg p-4">
                    <h3 className="text-lg font-semibold mb-3 text-blue-600">Additional Information</h3>
                    {renderAdditionalInfo()}
                </div>
            )}

            {/* Quiz Answers Section */}
            {summaryData.quizAnswers.length > 0 && (
                <div className="border rounded-lg p-4">
                    <h3 className="text-lg font-semibold mb-3 text-blue-600">Quiz Answers</h3>
                    {renderQuizAnswers()}
                </div>
            )}
        </div>
    );
}

