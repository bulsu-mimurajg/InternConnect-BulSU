import React, { createContext, useContext, ReactNode } from 'react';

interface FormFieldsContextType {
    setLanguageProficiencyFields: (fields: string[]) => void;
    setTechnicalSkillFields: (fields: string[]) => void;
    setSoftSkillFields: (fields: string[]) => void;
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
}

export const FormFieldsProvider: React.FC<FormFieldsProviderProps> = ({
    children,
    setLanguageProficiencyFields,
    setTechnicalSkillFields,
    setSoftSkillFields,
}) => {
    return (
        <FormFieldsContext.Provider
            value={{
                setLanguageProficiencyFields,
                setTechnicalSkillFields,
                setSoftSkillFields,
            }}
        >
            {children}
        </FormFieldsContext.Provider>
    );
}; 