<?php

declare(strict_types=1);

namespace RedirectionExtended;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

if (!function_exists(__NAMESPACE__ . '\\apply_filters')) {
    // Minimal stand-in for WordPress apply_filters() so the class can be tested without WordPress.
    function apply_filters(string $hookName, mixed $value, mixed ...$args): mixed
    {
        return TestFilters::$values[$hookName] ?? $value;
    }
}

final class TestFilters
{
    /** @var array<string, mixed> */
    public static array $values = [];
}

class EditorAccessTest extends TestCase
{
    protected function tearDown(): void
    {
        TestFilters::$values = [];
    }

    public function testPluginAccessIsEditorCapability(): void
    {
        static::assertSame('edit_others_pages', (new EditorAccess())->filterPluginAccess());
    }

    #[DataProvider('editorCapabilities')]
    public function testEditorCapabilitiesUseEditorCapability(string $capabilityName): void
    {
        static::assertSame(
            'edit_others_pages',
            (new EditorAccess())->filterCapabilityCheck('edit_others_pages', $capabilityName),
        );
    }

    #[DataProvider('adminCapabilities')]
    public function testOtherCapabilitiesRequireAdmin(string $capabilityName): void
    {
        static::assertSame(
            'list_users',
            (new EditorAccess())->filterCapabilityCheck('edit_others_pages', $capabilityName),
        );
    }

    public function testAdminCapabilityCanBeFiltered(): void
    {
        TestFilters::$values['RedirectionExtended/AdminCapability'] = 'manage_network';

        static::assertSame(
            'manage_network',
            (new EditorAccess())->filterCapabilityCheck('edit_others_pages', 'redirection_cap_404_manage'),
        );
    }

    public function testEmptyFilteredAdminCapabilityFallsBackToDefault(): void
    {
        TestFilters::$values['RedirectionExtended/AdminCapability'] = '';

        static::assertSame('list_users', (new EditorAccess())->getAdminCapability());
    }

    public static function editorCapabilities(): array
    {
        return [
            ['redirection_role'],
            ['redirection_cap_redirect_manage'],
            ['redirection_cap_redirect_add'],
        ];
    }

    public static function adminCapabilities(): array
    {
        return [
            ['redirection_cap_redirect_delete'],
            ['redirection_cap_group_manage'],
            ['redirection_cap_group_add'],
            ['redirection_cap_group_delete'],
            ['redirection_cap_404_manage'],
            ['redirection_cap_404_delete'],
            ['redirection_cap_log_manage'],
            ['redirection_cap_log_delete'],
            ['redirection_cap_io_manage'],
            ['redirection_cap_option_manage'],
            ['redirection_cap_support_manage'],
            ['redirection_cap_site_manage'],
            ['redirection_cap_rss'],
            ['redirection_cap_some_future_capability'],
        ];
    }
}
