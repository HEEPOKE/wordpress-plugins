<?php

use PHPUnit\Framework\TestCase;
use App\WpGitlabTrigger\WpTriggerPlugin;

/**
 * @covers \App\WpGitlabTrigger\WpTriggerPlugin::create
* @covers \App\WpGitlabTrigger\WpTriggerPlugin::registerSettings
*/
class RegisterSettingsTest extends TestCase
{
    private $wpGitlabTrigger;

    protected function setUp(): void
    {
        parent::setUp();
        $wpdbMock = Mockery::mock('WPDB');
        $wpdbMock->shouldReceive('prefix')->andReturn('wp_');
        $this->wpGitlabTrigger = WpTriggerPlugin::create($wpdbMock);
    }

    public function test_registerSettings()
    {
        WP_Mock::userFunction('add_settings_section')->once();
        
        WP_Mock::userFunction('add_settings_field')
            ->with(
                'gitlab_token',
                'GitLab Token',
                [$this->wpGitlabTrigger, 'fieldCallback'],
                'gitlab-trigger-settings',
                'gitlab_trigger_section',
                ['field' => 'gitlab_token']
            )
            ->once()
            ->andReturn(true);

        WP_Mock::userFunction('register_setting')
            ->with(
                'gitlab_trigger_settings_group',
                'gitlab_token'
            )
            ->once()
            ->andReturn(true);

        $this->assertTrue(true);
    }
}
