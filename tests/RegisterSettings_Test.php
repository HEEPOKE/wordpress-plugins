<?php

use PHPUnit\Framework\TestCase;
use App\WpGitlabTrigger\WpTriggerPlugin;

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
        WP_Mock::userFunction('add_settings_section')
        ->once();

        WP_Mock::userFunction('add_settings_field')
        ->with(
            $this->equalTo('gitlab_token'),
            $this->anything(),
            $this->callback(function ($arg) {
                return is_callable($arg);
            }),
            $this->equalTo('gitlab-trigger-settings'),
            $this->equalTo('gitlab_trigger_section'),
            $this->equalTo(['field' => 'gitlab_token'])
        )
        ->once()
        ->andReturn(true);

        WP_Mock::userFunction('register_setting')
        ->with(
            $this->equalTo('gitlab_trigger_settings_group'),
            $this->equalTo('gitlab_token')
        )
        ->once()
        ->andReturn(true);

        $this->wpGitlabTrigger->registerSettings();
        $this->assertTrue(true);
    }
}
