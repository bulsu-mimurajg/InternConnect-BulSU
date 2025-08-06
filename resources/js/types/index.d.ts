import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
    role: 'admin' | 'hte' | 'student' | 'adviser' | 'guest';
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
    [key: string]: unknown; // This allows for additional properties...
}

export interface question {
    label?: string;
    question: string;
    type: 'text' | 'radio' | 'select';
    placeholder?: string;
    options?: string[];
    required?: boolean;
    access: 'student' | 'hte';
}

export interface subcategory {
    id: number;
    subcategory_name: string;
    category_name: string;
    questions: Question[];
}
