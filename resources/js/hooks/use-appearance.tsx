import { useCallback, useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';

const prefersDark = () => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const applyTheme = (appearance: Appearance) => {
    const isDark = appearance === 'dark' || (appearance === 'system' && prefersDark());

    document.documentElement.classList.toggle('dark', isDark);
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const handleSystemThemeChange = () => {
    const currentAppearance = localStorage.getItem('appearance') as Appearance;
    applyTheme(currentAppearance || 'system');
};

export function initializeTheme() {
    // Ensure we're in a browser environment
    if (typeof window === 'undefined' || typeof document === 'undefined') {
        return;
    }

    // Function to safely get saved appearance
    const getSavedAppearance = (): Appearance => {
        try {
            const saved = localStorage.getItem('appearance') as Appearance;
            return saved || 'system';
        } catch {
            return 'system';
        }
    };

    // Function to safely apply theme
    const safeApplyTheme = (appearance: Appearance) => {
        try {
            applyTheme(appearance);
        } catch (error) {
            console.warn('Failed to apply theme:', error);
        }
    };

    // Get and apply the saved appearance
    const savedAppearance = getSavedAppearance();
    safeApplyTheme(savedAppearance);

    // Add the event listener for system theme changes
    const mediaQueryInstance = mediaQuery();
    if (mediaQueryInstance) {
        try {
            mediaQueryInstance.addEventListener('change', handleSystemThemeChange);
        } catch (error) {
            console.warn('Failed to add theme change listener:', error);
        }
    }
}

export function useAppearance() {
    const [appearance, setAppearance] = useState<Appearance>('system');

    const updateAppearance = useCallback((mode: Appearance) => {
        setAppearance(mode);

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', mode);

        // Store in cookie for SSR...
        setCookie('appearance', mode);

        applyTheme(mode);
    }, []);

    useEffect(() => {
        const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
        updateAppearance(savedAppearance || 'system');

        return () => mediaQuery()?.removeEventListener('change', handleSystemThemeChange);
    }, [updateAppearance]);

    return { appearance, updateAppearance } as const;
}
