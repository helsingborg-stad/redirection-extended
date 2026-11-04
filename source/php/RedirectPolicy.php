<?php

declare(strict_types=1);

namespace RedirectionExtended;

/**
 * Decides which redirects are unsafe to let editors save.
 *
 * - Pass-through (`pass`) fetches a remote URL and prints the response on the
 *   site's own origin, which would let an editor serve arbitrary JavaScript.
 * - Regex sources are admin-only. A regex can match login, admin and API
 *   requests in ways that can not be reliably checked, e.g. `^/wp-json/.+`.
 * - Plain sources on login, admin, REST and XML-RPC paths could send users
 *   to phishing pages or lock people out.
 */
class RedirectPolicy
{
    public const VIOLATION_PASS_THROUGH = 'pass_through';
    public const VIOLATION_REGEX = 'regex';
    public const VIOLATION_PROTECTED_SOURCE = 'protected_source';

    /** Redirection action types that only admins may use. */
    public const ADMIN_ONLY_ACTION_TYPES = ['pass'];

    /** Matches a protected path segment anywhere in a plain source path. */
    private const PROTECTED_PATH_PATTERN = '#(^|/)(wp-login\.php|wp-admin|wp-json|xmlrpc\.php)(/|$)#i';

    /**
     * Finds what, if anything, makes the redirect admin-only.
     *
     * @param array<mixed> $data Sanitized redirect data (`action_type`, `url`, `regex`).
     * @return self::VIOLATION_*|null
     */
    public static function getViolation(array $data): ?string
    {
        if (in_array($data['action_type'] ?? null, self::ADMIN_ONLY_ACTION_TYPES, true)) {
            return self::VIOLATION_PASS_THROUGH;
        }

        if (filter_var($data['regex'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return self::VIOLATION_REGEX;
        }

        $url = $data['url'] ?? null;

        if (!is_string($url) || $url === '') {
            return null;
        }

        $path = explode('?', rawurldecode($url), 2)[0];

        return preg_match(self::PROTECTED_PATH_PATTERN, $path) === 1 ? self::VIOLATION_PROTECTED_SOURCE : null;
    }
}
