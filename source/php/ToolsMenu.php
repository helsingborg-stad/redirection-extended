<?php

declare(strict_types=1);

namespace RedirectionExtended;

/**
 * Municipio removes the Tools menu for editors. This adds it back for them
 * with Redirection as the only visible item, so redirects are found in the
 * same place regardless of role.
 */
class ToolsMenu
{
    /** Redirection admin page slug under tools.php. */
    public const REDIRECTION_PAGE = 'redirection.php';

    /** WordPress core position of the Tools menu. */
    public const TOOLS_MENU_POSITION = 75;

    public function addHooks(): void
    {
        // Municipio removes the Tools menu for editors at priority 9000.
        add_action('admin_menu', [$this, 'restoreToolsMenuWithOnlyRedirection'], 9001);
    }

    /**
     * When the Tools menu has been removed for the current user (Municipio does
     * this for editors), it is added back with Redirection as the only visible
     * item. The other Tools items are only hidden, not removed, so access to
     * those pages is unchanged.
     */
    public function restoreToolsMenuWithOnlyRedirection(): void
    {
        if (!class_exists('\Redirection_Capabilities') || !current_user_can(EditorAccess::EDITOR_CAPABILITY)) {
            return;
        }

        // WordPress has no API for reading or rebuilding the admin menu, so the globals are used directly.
        // @mago-expect lint:no-global
        $menu = (array) ($GLOBALS['menu'] ?? []);
        // @mago-expect lint:no-global
        $toolsSubmenu = (array) ($GLOBALS['submenu']['tools.php'] ?? []);

        if (in_array('tools.php', array_column($menu, 2), true)) {
            return;
        }

        $submenu = self::redirectionOnlySubmenu($toolsSubmenu);

        if ($submenu === null) {
            return;
        }

        $position = self::TOOLS_MENU_POSITION;
        while (array_key_exists($position, $menu)) {
            $position++;
        }

        // Same entry as WordPress core (wp-admin/menu.php).
        // @mago-expect lint:no-global
        $GLOBALS['menu'][$position] = [__('Tools'), 'edit_posts', 'tools.php', '', 'menu-top menu-icon-tools', 'menu-tools', 'dashicons-admin-tools'];
        // @mago-expect lint:no-global
        $GLOBALS['submenu']['tools.php'] = $submenu;
    }

    /**
     * Puts Redirection first in the Tools submenu, so the Tools menu links to it,
     * and hides every other item with the WordPress `hidden` class.
     *
     * @param array<mixed> $toolsSubmenu
     * @return list<array<mixed>>|null Null when Redirection is not in the submenu.
     */
    public static function redirectionOnlySubmenu(array $toolsSubmenu): ?array
    {
        $redirection = [];
        $hidden = [];

        foreach ($toolsSubmenu as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (($item[2] ?? null) === self::REDIRECTION_PAGE) {
                $redirection[] = $item;
                continue;
            }

            $item[3] ??= $item[0] ?? '';
            $item[4] = trim((string) ($item[4] ?? '') . ' hidden');
            $hidden[] = $item;
        }

        return $redirection === [] ? null : [...$redirection, ...$hidden];
    }
}
