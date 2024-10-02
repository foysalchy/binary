<?php

if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = sanitizeInput($value); // Recursively sanitize arrays
            }
            return $input;
        }

        // List of patterns to match dangerous programming constructs (PHP, SQL, JavaScript, etc.)
        $patterns = [
            '/\b(?:insert|update|delete|drop|truncate|alter|create|exec|union|grant|revoke)\b/i', // SQL keywords
            '/\b(?:echo|print|require|include|exit|die|eval|assert|system|shell_exec)\b/i', // PHP functions
            '/<\?php\b/i', // PHP opening tag
            '/<script\b[^>]*>/i', // JavaScript <script> tag
            '/<style\b[^>]*>/i', // CSS <style> tag
            '/[\'"\\;]/', // Match special characters like quotes and semicolons
        ];

        // Remove dangerous programming constructs but keep HTML tags
        $sanitizedInput = preg_replace($patterns, '', $input);

        // Return the sanitized input as JSON
        return $sanitizedInput;
    }
}
