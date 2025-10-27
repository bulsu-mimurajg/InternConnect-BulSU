import React, { createContext, useContext, ReactNode } from 'react';

interface FormFieldsContextType {
    setLanguageProficiencyFields: (fields: string[]) => void;
    setTechnicalSkillFields: (fields: string[]) => void;
    setSoftSkillFields: (fields: string[]) => void;
    setQuizFields: (fields: string[]) => void;
    unansweredFields: string[];
    setUnansweredFields: (fields: string[]) => void;
    triggerPulseAndRedirect: (fields: string[], targetStep: number) => void;
}

const FormFieldsContext = createContext<FormFieldsContextType | undefined>(undefined);

export const useFormFields = () => {
    const context = useContext(FormFieldsContext);
    if (context === undefined) {
        throw new Error('useFormFields must be used within a FormFieldsProvider');
    }
    return context;
};

interface FormFieldsProviderProps {
    children: ReactNode;
    setLanguageProficiencyFields: (fields: string[]) => void;
    setTechnicalSkillFields: (fields: string[]) => void;
    setSoftSkillFields: (fields: string[]) => void;
    setQuizFields: (fields: string[]) => void;
    onNavigateToStep?: (step: number) => void;
}

export const FormFieldsProvider: React.FC<FormFieldsProviderProps> = ({
    children,
    setLanguageProficiencyFields,
    setTechnicalSkillFields,
    setSoftSkillFields,
    setQuizFields,
    onNavigateToStep,
}) => {
    const [unansweredFields, setUnansweredFields] = React.useState<string[]>([]);

    const triggerPulseAndRedirect = React.useCallback((fields: string[], targetStep: number) => {
        setUnansweredFields(fields);
        
        // Focus only on the first unanswered field
        const firstField = fields[0];
        if (firstField) {
            setTimeout(() => {
                const element = document.querySelector(`input[name="${firstField}"], [data-field="${firstField}"]`) as HTMLElement;
                if (element) {
                    element.classList.add('animate-pulse-unanswered');
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    element.focus();
                    
                    // Remove animation class after 1 pulse (1s)
                    setTimeout(() => {
                        element.classList.remove('animate-pulse-unanswered');
                    }, 1000);
                }
            }, 100);
        }

        // Redirect to target step after pulse animation
        setTimeout(() => {
            if (onNavigateToStep) {
                onNavigateToStep(targetStep);
            }
            setUnansweredFields([]);
        }, 1500); // Slight delay after pulse completes
    }, [onNavigateToStep]);

    return (
        <FormFieldsContext.Provider
            value={{
                setLanguageProficiencyFields,
                setTechnicalSkillFields,
                setSoftSkillFields,
                setQuizFields,
                unansweredFields,
                setUnansweredFields,
                triggerPulseAndRedirect,
            }}
        >
            {children}
        </FormFieldsContext.Provider>
    );
}; 