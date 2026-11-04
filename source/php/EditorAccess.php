<?php

declare(strict_types=1);

namespace RedirectionExtended;

/**
 * Gives editors access to create and edit redirects in the Redirection plugin.
 *
 * Everything else in Redirection (deleting redirects, groups, 404s, logs,
 * import/export, options, support and site settings) requires an
 * administrator. Capabilities not listed in EDITOR_CAPABILITIES fall back
 * to the admin capability, so capabilities added in future Redirection
 * versions stay admin-only by default.
 *
 * Municipio gives editors `manage_options`, so that capability can not be
 * used to tell administrators and editors apart. `list_users` is used
 * instead; it can be changed with the `RedirectionExtended/AdminCapability`
 * filter.
 */
class EditorAccess
{
    /** Capability that gives access to the Redirection menu (editors and up). */
    public const EDITOR_CAPABILITY = 'edit_others_pages';

    /** Default capability required for everything editors are not allowed to do. */
    public const ADMIN_CAPABILITY = 'list_users';

    /**
     * Redirection capability names (see Redirection_Capabilities) that editors may use.
     */
    public const EDITOR_CAPABILITIES = [
        'redirection_role', // Redirection_Capabilities::CAP_PLUGIN – plugin menu and REST base
        'redirection_cap_redirect_manage', // List, search and edit redirects
        'redirection_cap_redirect_add', // Create and save redirects, bulk enable/disable/reset hits
    ];

    public function addHooks(): void
    {
        add_filter('redirection_role', [$this, 'filterPluginAccess']);
        add_filter('redirection_capability_check', [$this, 'filterCapabilityCheck'], 10, 2);

        (new ToolsMenu())->addHooks();
    }

    /**
     * Lowest capability that can open Redirection.
     */
    public function filterPluginAccess(): string
    {
        return self::EDITOR_CAPABILITY;
    }

    /**
     * Capability required for a specific Redirection capability.
     *
     * @param mixed $capability The capability Redirection would check.
     * @param mixed $capabilityName The Redirection capability name.
     */
    public function filterCapabilityCheck($capability, $capabilityName): string
    {
        if (in_array($capabilityName, self::EDITOR_CAPABILITIES, true)) {
            return is_string($capability) && $capability !== '' ? $capability : self::EDITOR_CAPABILITY;
        }

        return $this->getAdminCapability();
    }

    /**
     * Capability required for the parts of Redirection that are admin-only.
     */
    public function getAdminCapability(): string
    {
        $capability = apply_filters('RedirectionExtended/AdminCapability', self::ADMIN_CAPABILITY);

        return is_string($capability) && $capability !== '' ? $capability : self::ADMIN_CAPABILITY;
    }
}
