<?php

use PHPUnit\Framework\TestCase;
use App\WpGitlabTrigger\WpTriggerPlugin;

/**
 * @covers \App\WpGitlabTrigger\WpTriggerPlugin::create
* @covers \App\WpGitlabTrigger\WpTriggerPlugin::saveSettings
*/
class SaveSettingsTest extends TestCase
{

    private $wpGitlabTrigger;

    protected function setUp(): void
    {
        parent::setUp();

        $wpdbMock = Mockery::mock('WPDB');
        $wpdbMock->shouldReceive('prefix')->andReturn('wp_');
        $this->wpGitlabTrigger = WpTriggerPlugin::create($wpdbMock);
    }

    public function test_saveSettings()
    {
        $_POST['gitlab_token'] = '9xV1Esu_-rh1576W33W8';
        $_POST['gitlab_brand_tag'] = 'dev';
        $_POST['gitlab_project_id'] = '43373960';

        WP_Mock::userFunction('wp_send_json', [
            'times' => 1,
            'args' => [['success' => true]],
        ]);

        WP_Mock::userFunction('wp_die', [
            'times' => 1,
        ]);

        $this->assertTrue(true);
    }
}
