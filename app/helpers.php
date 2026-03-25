<?php

if (!function_exists('formatCurrency')) {
    /**
     * Format a number as Philippine Peso currency.
     */
    function formatCurrency($amount, string $symbol = '₱'): string
    {
        if ($amount === null || $amount === '') {
            return $symbol . '0.00';
        }
        return $symbol . number_format((float) $amount, 2);
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format a date string to a human-readable format.
     */
    function formatDate(?string $date, string $format = 'M d, Y'): string
    {
        if (!$date || $date === '0000-00-00') {
            return '—';
        }
        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime(?string $datetime, string $format = 'M d, Y h:i A'): string
    {
        if (!$datetime) return '—';
        try {
            return \Carbon\Carbon::parse($datetime)->format($format);
        } catch (\Exception $e) {
            return $datetime;
        }
    }
}

if (!function_exists('formatPercent')) {
    function formatPercent($value, int $decimals = 1): string
    {
        return number_format((float) $value, $decimals) . '%';
    }
}

if (!function_exists('statusBadge')) {
    /**
     * Return a Tailwind CSS class string for a status badge.
     */
    function statusBadge(string $status): string
    {
        return match (strtolower($status)) {
            'active', 'completed', 'approved', 'paid'  => 'bg-green-100 text-green-800',
            'maintenance', 'in_progress', 'pending'     => 'bg-yellow-100 text-yellow-800',
            'coding', 'denied', 'cancelled'             => 'bg-red-100 text-red-800',
            'retired', 'expired'                        => 'bg-gray-100 text-gray-600',
            default                                     => 'bg-blue-100 text-blue-800',
        };
    }
}

if (!function_exists('base_url')) {
    /**
     * Get the base URL of the application (Laravel equivalent of CodeIgniter base_url).
     * 
     * @param string $path - Additional path to append to base URL
     * @param bool $secure - Force HTTPS (true) or HTTP (false). null for auto-detect
     * @return string - Complete URL
     */
    function base_url($path = '', $secure = null): string
    {
        // Get the base URL from config or use Laravel's url() helper
        $baseUrl = config('app.url') ?: url('/', $secure);
        
        // Ensure base URL ends with single slash
        $baseUrl = rtrim($baseUrl, '/') . '/';
        
        // Clean the path (remove leading slash)
        $path = ltrim($path, '/');
        
        // If path is empty, return base URL
        if (empty($path)) {
            return $baseUrl;
        }
        
        // Combine base URL with path
        return $baseUrl . $path;
    }
}
