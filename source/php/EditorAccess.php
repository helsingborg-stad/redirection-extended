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

    /** Menu slug used when the Tools menu is hidden for the current user. */
    public const MENU_SLUG = 'tools.php?page=redirection.php';

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

        // Municipio removes the Tools menu for editors at priority 9000.
        add_action('admin_menu', [$this, 'addMenuWhenToolsIsHidden'], 9001);
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

    /**
     * Adds a top-level "Redirects" menu item when the Tools menu has been removed
     * for the current user, so that editors can reach the Redirection page.
     */
    public function addMenuWhenToolsIsHidden(): void
    {
        if (!class_exists('\Redirection_Capabilities') || !current_user_can(self::EDITOR_CAPABILITY)) {
            return;
        }

        // WordPress has no API for reading the admin menu, so the global is read directly.
        // @mago-expect lint:no-global
        $menu = $GLOBALS['menu'] ?? [];

        if (in_array('tools.php', array_column((array) $menu, 2), true)) {
            return;
        }

        add_menu_page(
            __('Redirects', 'redirection-extended'),
            __('Redirects', 'redirection-extended'),
            self::EDITOR_CAPABILITY,
            self::MENU_SLUG,
            '',
            'dashicons-randomize',
            21,
        );
    }
}
