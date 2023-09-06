<?php

use PHPUnit\Framework\TestCase;
use App\WpGitlabTrigger\WpTriggerPlugin;

class AdminMenuTest extends TestCase
{
    private $wpGitlabTrigger;

    protected function setUp(): void
    {
        parent::setUp();
        $wpdbMock = Mockery::mock('WPDB');
        $wpdbMock->shouldReceive('prefix')->andReturn('wp_');
        $this->wpGitlabTrigger = WpTriggerPlugin::create($wpdbMock);
        WP_Mock::setUp();
    }

    public function test_adminMenu()
    {
        WP_Mock::expectAction('admin_menu', function ($callback) {
            $this->assertIsCallable($callback);
            call_user_func($callback);
        });

        $this->wpGitlabTrigger->addAdminMenu();

        $this->assertTrue(true);
    }
}
