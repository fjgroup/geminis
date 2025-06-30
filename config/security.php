<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | application, including rate limiting, input validation, and audit logging.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for different types of requests.
    |
    */
    'rate_limiting' => [
        'admin' => [
            'max_attempts' => env('ADMIN_RATE_LIMIT_ATTEMPTS', 100),
            'decay_minutes' => env('ADMIN_RATE_LIMIT_DECAY', 1),
        ],
        'api' => [
            'max_attempts' => env('API_RATE_LIMIT_ATTEMPTS', 60),
            'decay_minutes' => env('API_RATE_LIMIT_DECAY', 1),
        ],
        'login' => [
            'max_attempts' => env('LOGIN_RATE_LIMIT_ATTEMPTS', 5),
            'decay_minutes' => env('LOGIN_RATE_LIMIT_DECAY', 15),
        ],
        'security_violations' => [
            'max_attempts' => env('SECURITY_VIOLATIONS_LIMIT', 5),
            'decay_minutes' => env('SECURITY_VIOLATIONS_DECAY', 60),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Validation
    |--------------------------------------------------------------------------
    |
    | Configure input validation and sanitization settings.
    |
    */
    'input_validation' => [
        'max_payload_size' => env('MAX_PAYLOAD_SIZE', 1048576), // 1MB
        'max_string_length' => env('MAX_STRING_LENGTH', 10000),
        'enable_sanitization' => env('ENABLE_INPUT_SANITIZATION', true),
        'block_malicious_patterns' => env('BLOCK_MALICIOUS_PATTERNS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Configure session security settings.
    |
    */
    'session' => [
        'max_inactive_time' => env('SESSION_MAX_INACTIVE_TIME', 7200), // 2 hours
        'validate_user_agent' => env('SESSION_VALIDATE_USER_AGENT', true),
        'user_agent_similarity_threshold' => env('SESSION_USER_AGENT_THRESHOLD', 80),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Configure audit logging settings.
    |
    */
    'audit_logging' => [
        'enabled' => env('AUDIT_LOGGING_ENABLED', true),
        'log_channel' => env('AUDIT_LOG_CHANNEL', 'daily'),
        'log_failed_operations' => env('LOG_FAILED_OPERATIONS', true),
        'log_bulk_operations' => env('LOG_BULK_OPERATIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Malicious Pattern Detection
    |--------------------------------------------------------------------------
    |
    | Patterns to detect potentially malicious input.
    |
    */
    'malicious_patterns' => [
        // SQL Injection
        'sql_injection' => '/(\bUNION\b.*\bSELECT\b|\bSELECT\b.*\bFROM\b|\bINSERT\b.*\bINTO\b|\bDELETE\b.*\bFROM\b|\bDROP\b.*\bTABLE\b)/i',
        
        // XSS
        'xss_script' => '/<script[^>]*>.*?<\/script>/i',
        'xss_javascript' => '/javascript:/i',
        'xss_events' => '/on\w+\s*=/i',
        
        // Path Traversal
        'path_traversal' => '/\.\.\/|\.\.\\\\/',
        
        // Command Injection
        'command_injection' => '/(\b(exec|system|shell_exec|passthru|eval|file_get_contents|file_put_contents|fopen|fwrite)\b)/i',
        
        // LDAP Injection
        'ldap_injection' => '/(\*|\(|\)|\||&)/i',
        
        // NoSQL Injection
        'nosql_injection' => '/(\$where|\$ne|\$gt|\$lt|\$regex)/i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Security
    |--------------------------------------------------------------------------
    |
    | Additional security settings specific to admin panel.
    |
    */
    'admin' => [
        'require_2fa' => env('ADMIN_REQUIRE_2FA', false),
        'session_timeout' => env('ADMIN_SESSION_TIMEOUT', 3600), // 1 hour
        'log_all_actions' => env('ADMIN_LOG_ALL_ACTIONS', true),
        'ip_whitelist' => env('ADMIN_IP_WHITELIST', null), // Comma-separated IPs
        'super_admin_email' => env('SUPER_ADMIN_EMAIL', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Configure security headers to be sent with responses.
    |
    */
    'headers' => [
        'x_frame_options' => env('X_FRAME_OPTIONS', 'DENY'),
        'x_content_type_options' => env('X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'x_xss_protection' => env('X_XSS_PROTECTION', '1; mode=block'),
        'referrer_policy' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'content_security_policy' => env('CONTENT_SECURITY_POLICY', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';"),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Configure file upload security settings.
    |
    */
    'file_upload' => [
        'max_file_size' => env('MAX_FILE_UPLOAD_SIZE', 2048), // KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'scan_for_malware' => env('SCAN_UPLOADS_FOR_MALWARE', false),
        'quarantine_suspicious_files' => env('QUARANTINE_SUSPICIOUS_FILES', true),
    ],

];
