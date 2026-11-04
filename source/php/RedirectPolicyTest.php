<?php

declare(strict_types=1);

namespace RedirectionExtended;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RedirectPolicyTest extends TestCase
{
    public function testPassThroughIsNotAllowed(): void
    {
        static::assertSame(
            RedirectPolicy::VIOLATION_PASS_THROUGH,
            RedirectPolicy::getViolation(['action_type' => 'pass', 'url' => '/x', 'regex' => 0]),
        );
    }

    #[DataProvider('allowedRedirects')]
    public function testAllowedRedirects(array $data): void
    {
        static::assertNull(RedirectPolicy::getViolation($data, ['', '/helsingborg', '/helsingborg/wp']));
    }

    #[DataProvider('protectedSources')]
    public function testProtectedSourcesAreNotAllowed(array $data): void
    {
        static::assertSame(
            RedirectPolicy::VIOLATION_PROTECTED_SOURCE,
            RedirectPolicy::getViolation($data, ['', '/helsingborg', '/helsingborg/wp']),
        );
    }

    public static function allowedRedirects(): array
    {
        return [
            'url to external' => [['action_type' => 'url', 'url' => '/old-page', 'regex' => 0]],
            'url with query' => [['action_type' => 'url', 'url' => '/old-page?id=1', 'regex' => 0]],
            'error' => [['action_type' => 'error', 'url' => '/gone', 'regex' => 0]],
            'nothing' => [['action_type' => 'nothing', 'url' => '/ignored', 'regex' => 0]],
            'random' => [['action_type' => 'random', 'url' => '/random', 'regex' => 0]],
            'similar name' => [['action_type' => 'url', 'url' => '/my-wp-login-guide', 'regex' => 0]],
            'narrow regex' => [['action_type' => 'url', 'url' => '^/news/(.*)$', 'regex' => 1]],
            'invalid regex' => [['action_type' => 'url', 'url' => '^/news/(', 'regex' => 1]],
            'no url' => [['action_type' => 'url']],
        ];
    }

    public static function protectedSources(): array
    {
        return [
            'login' => [['action_type' => 'url', 'url' => '/wp-login.php', 'regex' => 0]],
            'login with query' => [['action_type' => 'url', 'url' => '/wp-login.php?action=lostpassword', 'regex' => 0]],
            'login upper case' => [['action_type' => 'url', 'url' => '/WP-Login.php', 'regex' => 0]],
            'login encoded' => [['action_type' => 'url', 'url' => '/wp%2Dlogin.php', 'regex' => 0]],
            'login under wp' => [['action_type' => 'url', 'url' => '/wp/wp-login.php', 'regex' => 0]],
            'login on subsite' => [['action_type' => 'url', 'url' => '/helsingborg/wp-login.php', 'regex' => 0]],
            'admin' => [['action_type' => 'url', 'url' => '/wp-admin', 'regex' => 0]],
            'admin page' => [['action_type' => 'url', 'url' => '/wp-admin/edit.php', 'regex' => 0]],
            'rest api' => [['action_type' => 'url', 'url' => '/wp-json/wp/v2/users', 'regex' => 0]],
            'xmlrpc' => [['action_type' => 'url', 'url' => '/xmlrpc.php', 'regex' => 0]],
            'regex catch all' => [['action_type' => 'url', 'url' => '.*', 'regex' => 1]],
            'regex login' => [['action_type' => 'url', 'url' => '^/wp-log', 'regex' => 1]],
            'regex case' => [['action_type' => 'url', 'url' => '^/WP-ADMIN', 'regex' => 1]],
            'regex subsite login' => [['action_type' => 'url', 'url' => '^/helsingborg/wp/wp-login\.php$', 'regex' => 1]],
            'regex with at sign' => [['action_type' => 'url', 'url' => '^/wp-json(@)?', 'regex' => 1]],
        ];
    }
}
