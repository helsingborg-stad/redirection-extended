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

    #[DataProvider('regexRedirects')]
    public function testRegexIsNotAllowed(array $data): void
    {
        static::assertSame(RedirectPolicy::VIOLATION_REGEX, RedirectPolicy::getViolation($data));
    }

    #[DataProvider('allowedRedirects')]
    public function testAllowedRedirects(array $data): void
    {
        static::assertNull(RedirectPolicy::getViolation($data));
    }

    #[DataProvider('protectedSources')]
    public function testProtectedSourcesAreNotAllowed(array $data): void
    {
        static::assertSame(
            RedirectPolicy::VIOLATION_PROTECTED_SOURCE,
            RedirectPolicy::getViolation($data),
        );
    }

    public static function allowedRedirects(): array
    {
        return [
            'url to external' => [['action_type' => 'url', 'url' => '/old-page', 'regex' => 0]],
            'regex flag off' => [['action_type' => 'url', 'url' => '/old-page', 'regex' => false]],
            'url with query' => [['action_type' => 'url', 'url' => '/old-page?id=1', 'regex' => 0]],
            'error' => [['action_type' => 'error', 'url' => '/gone', 'regex' => 0]],
            'nothing' => [['action_type' => 'nothing', 'url' => '/ignored', 'regex' => 0]],
            'random' => [['action_type' => 'random', 'url' => '/random', 'regex' => 0]],
            'similar name' => [['action_type' => 'url', 'url' => '/my-wp-login-guide', 'regex' => 0]],
            'no url' => [['action_type' => 'url']],
        ];
    }

    public static function regexRedirects(): array
    {
        return [
            'narrow regex' => [['action_type' => 'url', 'url' => '^/news/(.*)$', 'regex' => 1]],
            'catch all' => [['action_type' => 'url', 'url' => '.*', 'regex' => 1]],
            'rest api subpaths' => [['action_type' => 'url', 'url' => '^/wp-json/.+', 'regex' => 1]],
            'login with query' => [['action_type' => 'url', 'url' => '^/wp-login\\.php.+', 'regex' => 1]],
            'invalid regex' => [['action_type' => 'url', 'url' => '^/news/(', 'regex' => 1]],
            'regex as string' => [['action_type' => 'url', 'url' => '^/x', 'regex' => '1']],
            'regex as bool' => [['action_type' => 'url', 'url' => '^/x', 'regex' => true]],
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
            'login escaped query' => [['action_type' => 'url', 'url' => '/wp-login.php\\?', 'regex' => 0]],
            'login escaped query with params' => [['action_type' => 'url', 'url' => '/wp-login.php\\?action=lostpassword', 'regex' => 0]],
            'admin escaped query' => [['action_type' => 'url', 'url' => '/wp-admin\\?', 'regex' => 0]],
            'rest escaped query' => [['action_type' => 'url', 'url' => '/wp-json\\?', 'regex' => 0]],
            'login encoded escaped query' => [['action_type' => 'url', 'url' => '/wp-login.php%5C%3F', 'regex' => 0]],
            'login double encoded' => [['action_type' => 'url', 'url' => '/wp%252Dlogin.php', 'regex' => 0]],
            'match url only' => [['action_type' => 'url', 'url' => '/harmless', 'match_url' => '/wp-login.php', 'regex' => 0]],
        ];
    }
}
