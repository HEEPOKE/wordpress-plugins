<?php

use PHPUnit\Framework\TestCase;
use App\WpGitlabTrigger\WpTriggerPlugin;

class SettingsPageTest extends TestCase
{
    private $wpGitlabTrigger;

    protected function setUp(): void
    {
        parent::setUp();
        $wpdbMock = Mockery::mock('WPDB');
        $wpdbMock->shouldReceive('prefix')->andReturn('wp_');
        $this->wpGitlabTrigger = WpTriggerPlugin::create($wpdbMock);
    }

    public function test_settingsPage()
    {
        WP_Mock::userFunction('add_settings_section')
            ->once();

        WP_Mock::userFunction('add_settings_field')
            ->times(3);

        WP_Mock::userFunction('register_setting')
            ->times(3);

        WP_Mock::userFunction('settings_fields')
            ->once();

        WP_Mock::userFunction('do_settings_sections')
            ->once();

        WP_Mock::userFunction('submit_button')
            ->with('Save', 'primary', 'save-data')
            ->once();

        WP_Mock::userFunction('submit_button')
            ->with('Test Pipeline', 'primary', 'submit')
            ->once();

        ob_start();

        $this->wpGitlabTrigger->settingsPage();
        $output = ob_get_clean();
        $cleanedOutput = preg_replace('/\s+/', ' ', $output);

        $this->assertStringContainsString('<div class="wrap">', $cleanedOutput);
        $this->assertStringContainsString('<form method="post" action="">', $cleanedOutput);
    }
}
