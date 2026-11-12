<?php

declare(strict_types=1);

namespace RedirectionExtended;

/**
 * Stops non-admins from saving redirects that RedirectPolicy marks as admin-only.
 *
 * Runs on Redirection's `redirection_validate_redirect` filter, which is
 * applied when a redirect is created or updated. Returning a WP_Error stops
 * the save.
 */
class RedirectValidator
{
    public const ERROR_CODE = 'redirection_extended_not_allowed';

    public function __construct(
        private EditorAccess $editorAccess,
    ) {}

    public function addHooks(): void
    {
        add_filter('redirection_validate_redirect', [$this, 'validate']);
    }

    /**
     * @param mixed $data Sanitized redirect data from Redirection.
     * @return mixed The data, or a WP_Error when the current user may not save it.
     */
    public function validate($data)
    {
        if (!is_array($data) || current_user_can($this->editorAccess->getAdminCapability())) {
            return $data;
        }

        $violation = RedirectPolicy::getViolation($data);

        if ($violation === null) {
            return $data;
        }

        return new \WP_Error(self::ERROR_CODE, self::getMessage($violation), ['status' => 403]);
    }

    private static function getMessage(string $violation): string
    {
        if ($violation === RedirectPolicy::VIOLATION_PASS_THROUGH) {
            return __('Only administrators can create pass-through redirects.', 'redirection-extended');
        }

        if ($violation === RedirectPolicy::VIOLATION_REGEX) {
            return __('Only administrators can create regex redirects.', 'redirection-extended');
        }

        return __('Only administrators can create redirects from login, admin or API addresses.', 'redirection-extended');
    }
}
