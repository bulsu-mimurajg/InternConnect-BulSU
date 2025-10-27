import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
    role: 'admin' | 'hte' | 'student' | 'adviser';
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
    subNav?: { title: string; href: string }[];
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    flash: {
        success?: string;
        error?: string;
    };
    [key: string]: unknown;
}

export interface User {
    id: number;
    username: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    hte?: HTE;
    student?: Student;
    [key: string]: unknown; // This allows for additional properties...
}

export interface Student {
    id: number;
    user_id: number;
    student_number: string;
    first_name: string;
    middle_name: string;
    last_name: string;
    phone: string;
    section_id: number;
    specialization: string;
    is_active: boolean;
    is_submit: boolean;
    is_placed: boolean;
    created_at: string;
    updated_at: string;
    section?: Section;
}

export interface Section {
    section_id: number;
    section_name: string;
    status: string;
    created_at: string;
    updated_at: string;
}

export interface HTE {
    id: number;
    company_name: string;
    company_address: string;
    company_email: string;
    cperson_fname: string;
    cperson_lname: string;
    cperson_position: string;
    cperson_contactnum: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface question {
    label?: string;
    question: string;
    type: 'text' | 'radio' | 'select';
    placeholder?: string;
    options?: string[];
    required?: boolean;
    access: 'Student' | 'HTE';
}

export interface Answer {
    answer_text: string;
    is_correct: boolean;
    display_order: number;
}

export interface Question {
    id: number;
    question: string;
    access: 'Student' | 'HTE';
    is_active: boolean;
    subcategory_id: number;
    question_type?: string;
    points?: number;
    answers?: Answer[];
    subcategory: {
        id: number;
        subcategory_name: string;
        category: {
            id: number;
            category_name: string;
        };
    };
    created_at: string;
    updated_at: string;
}

export interface Category {
    id: number;
    category_name: string;
    subCategories: SubCategory[];
}

export interface SubCategory {
    id: number;
    subcategory_name: string;
    category_id: number;
    category_name?: string;
}

export interface subcategory {
    id: number;
    subcategory_name: string;
    category_name: string;
    questions: Question[];
}
