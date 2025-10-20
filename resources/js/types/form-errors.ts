// Form error types for consistent error handling across the application

export interface FormErrors {
    [key: string]: string | string[] | undefined;
}

export interface AdviserFormErrors extends FormErrors {
    email?: string;
    username?: string;
    password?: string;
    password_confirmation?: string;
    adviser_fname?: string;
    adviser_lname?: string;
    section_ids?: string;
}

export interface HTEFormErrors extends FormErrors {
    email?: string;
    username?: string;
    password?: string;
    password_confirmation?: string;
    company_name?: string;
    company_address?: string;
    company_email?: string;
    cperson_fname?: string;
    cperson_lname?: string;
    cperson_position?: string;
    cperson_contactnum?: string;
}

export interface EmailValidation {
    isValid: boolean;
    message: string;
}

export interface EmailValidationState {
    create: EmailValidation;
    edit: EmailValidation;
}
