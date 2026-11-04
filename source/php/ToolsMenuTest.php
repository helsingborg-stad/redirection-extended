<?php

declare(strict_types=1);

namespace RedirectionExtended;

use PHPUnit\Framework\TestCase;

class ToolsMenuTest extends TestCase
{
    public function testRedirectionOnlySubmenuPutsRedirectionFirstAndHidesTheRest(): void
    {
        $submenu = [
            5 => ['Available Tools', 'edit_posts', 'tools.php'],
            10 => ['Webhooks Manager', 'manage_options', 'webhooks-manager', 'Webhooks Manager', 'custom'],
            15 => ['Redirection', 'edit_others_pages', 'redirection.php', 'Redirection'],
        ];

        static::assertSame(
            [
                ['Redirection',      'edit_others_pages', 'redirection.php',  'Redirection'],
                ['Available Tools',  'edit_posts',        'tools.php',        'Available Tools',  'hidden'],
                ['Webhooks Manager', 'manage_options',    'webhooks-manager', 'Webhooks Manager', 'custom hidden'],
            ],
            ToolsMenu::redirectionOnlySubmenu($submenu),
        );
    }

    public function testRedirectionOnlySubmenuIsNullWithoutRedirection(): void
    {
        static::assertNull(ToolsMenu::redirectionOnlySubmenu([['Available Tools', 'edit_posts', 'tools.php']]));
    }
}
