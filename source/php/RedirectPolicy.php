<?php

declare(strict_types=1);

namespace RedirectionExtended;

/**
 * Decides which redirects are unsafe to let editors save.
 *
 * - Pass-through (`pass`) fetches a remote URL and prints the response on the
 *   site's own origin, which would let an editor serve arbitrary JavaScript.
 * - Redirects from login, admin, REST and XML-RPC paths could send users to
 *   phishing pages or lock people out. Regex sources are tested against those
 *   paths, so catch-all patterns are also caught.
 */
class RedirectPolicy
{
    public const VIOLATION_PASS_THROUGH = 'pass_through';
    public const VIOLATION_PROTECTED_SOURCE = 'protected_source';

    /** Redirection action types that only admins may use. */
    public const ADMIN_ONLY_ACTION_TYPES = ['pass'];

    /** Paths (relative to the site or WordPress root) that only admins may redirect from. */
    public const PROTECTED_PATHS = ['/wp-login.php', '/wp-admin/', '/wp-json/', '/xmlrpc.php'];

    /** Matches a protected path segment anywhere in a non-regex source path. */
    private const PROTECTED_PATH_PATTERN = '#(^|/)(wp-login\.php|wp-admin|wp-json|xmlrpc\.php)(/|$)#i';

    /**
     * Finds what, if anything, makes the redirect admin-only.
     *
     * @param array<mixed> $data Sanitized redirect data (`action_type`, `url`, `regex`).
     * @param list<string> $pathPrefixes Path prefixes to test regex sources with, e.g. '' and '/subsite'.
     * @return self::VIOLATION_*|null
     */
    public static function getViolation(array $data, array $pathPrefixes = ['']): ?string
    {
        if (in_array($data['action_type'] ?? null, self::ADMIN_ONLY_ACTION_TYPES, true)) {
            return self::VIOLATION_PASS_THROUGH;
        }

        $url = $data['url'] ?? null;

        if (!is_string($url) || $url === '') {
            return null;
        }

        $isRegex = filter_var($data['regex'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return self::isProtectedSource($url, $isRegex, $pathPrefixes) ? self::VIOLATION_PROTECTED_SOURCE : null;
    }

    /**
     * @param list<string> $pathPrefixes
     */
    private static function isProtectedSource(string $url, bool $isRegex, array $pathPrefixes): bool
    {
        $url = rawurldecode($url);

        if (!$isRegex) {
            $path = explode('?', $url, 2)[0];

            return preg_match(self::PROTECTED_PATH_PATTERN, $path) === 1;
        }

        // Same delimiter as Redirection, always case-insensitive to be safe.
        $pattern = '@' . str_replace('@', '\\@', $url) . '@si';

        foreach (self::getSamplePaths($pathPrefixes) as $samplePath) {
            // An invalid pattern never matches in Redirection either, which also silences it.
            // @mago-expect lint:no-error-control-operator
            if (@preg_match($pattern, $samplePath) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $pathPrefixes
     * @return list<string>
     */
    private static function getSamplePaths(array $pathPrefixes): array
    {
        $paths = [];

        foreach (array_unique([...$pathPrefixes, '']) as $prefix) {
            foreach (self::PROTECTED_PATHS as $protectedPath) {
                $paths[] = rtrim($prefix, '/') . $protectedPath;
                $paths[] = rtrim($prefix, '/') . rtrim($protectedPath, '/');
            }
        }

        return array_values(array_unique($paths));
    }
}
