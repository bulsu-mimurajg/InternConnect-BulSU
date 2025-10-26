<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class EmailUrlHelper
{
    /**
     * Safely generate absolute URL for emails with validation and logging
     */
    public static function generateEmailUrl(string $path): string
    {
        // Get APP_URL from environment
        $baseUrl = env('APP_URL');
        
        // Validate APP_URL
        if (empty($baseUrl)) {
            Log::error('EMAIL URL ERROR: APP_URL is not set in .env file', [
                'path' => $path,
                'env_app_url' => env('APP_URL'),
                'config_app_url' => config('app.url'),
            ]);
            throw new \Exception('APP_URL is not set in .env file. Cannot generate email URL.');
        }
        
        // Remove trailing slash from base URL
        $baseUrl = rtrim($baseUrl, '/');
        
        // Validate URL format
        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            Log::error('EMAIL URL ERROR: APP_URL is not a valid URL', [
                'base_url' => $baseUrl,
                'path' => $path,
            ]);
            throw new \Exception('APP_URL is not a valid URL: ' . $baseUrl);
        }
        
        // Generate the full URL
        $fullUrl = $baseUrl . '/' . ltrim($path, '/');
        
        // Log the generated URL for debugging
        Log::info('EMAIL URL GENERATED', [
            'base_url' => $baseUrl,
            'path' => $path,
            'full_url' => $fullUrl,
        ]);
        
        return $fullUrl;
    }
    
    /**
     * Generate login URL for credential emails
     */
    public static function generateLoginUrl(): string
    {
        return self::generateEmailUrl('/login');
    }
    
    /**
     * Generate reset password URL
     */
    public static function generateResetPasswordUrl(string $token, string $email): string
    {
        return self::generateEmailUrl('/reset-password/' . $token . '?email=' . urlencode($email));
    }
}
