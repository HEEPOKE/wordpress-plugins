<?php

use PHPUnit\Framework\TestCase;

/**
 * @covers \App\WpGitlabTrigger\WpTriggerPlugin::settingsLink
 */
class SettingsLinkTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $wpdbMock = Mockery::mock('WPDB');
        $wpdbMock->shouldReceive('prefix')->andReturn('wp_');
    }

    public function test_settingsLink()
    {
        WP_Mock::expectAction('admin_url', function ($callback) {
            $this->assertIsCallable($callback);
            call_user_func($callback);
        });

        $expected_link = '<a href="admin.php?page=gitlab-trigger-settings">Settings</a>';
        $this->assertSame($expected_link, '<a href="admin.php?page=gitlab-trigger-settings">Settings</a>');
    }
}
