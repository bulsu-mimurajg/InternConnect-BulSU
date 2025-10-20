/**
 * OTP (One-Time Password) utility functions
 */

/**
 * Generate a random OTP password
 * @param length - Length of the OTP (default: 8)
 * @returns Generated OTP string
 */
export const generateOTP = (length: number = 8): string => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
};

/**
 * Copy text to clipboard
 * @param text - Text to copy
 * @returns Promise that resolves when copy is complete
 */
export const copyToClipboard = async (text: string): Promise<void> => {
    try {
        await navigator.clipboard.writeText(text);
    } catch (err) {
        console.error('Failed to copy to clipboard:', err);
        throw err;
    }
};

/**
 * OTP generation hook for forms
 * @param initialOTP - Initial OTP value
 * @returns OTP state and handlers
 */
export const useOTP = (initialOTP: string = '') => {
    const [otp, setOtp] = useState(initialOTP);
    const [showOTP, setShowOTP] = useState(false);

    const generateNewOTP = (length: number = 8) => {
        const newOTP = generateOTP(length);
        setOtp(newOTP);
        setShowOTP(true);
        return newOTP;
    };

    const copyOTP = async () => {
        if (otp) {
            await copyToClipboard(otp);
        }
    };

    const resetOTP = () => {
        setOtp('');
        setShowOTP(false);
    };

    return {
        otp,
        showOTP,
        generateNewOTP,
        copyOTP,
        resetOTP,
        setShowOTP
    };
};

// Import useState for the hook
import { useState } from 'react';
