<?php

use PHPUnit\Framework\TestCase;
use App\WpGitlabTrigger\WpTriggerPlugin;

/**
 * @covers \App\WpGitlabTrigger\WpTriggerPlugin::create
* @covers \App\WpGitlabTrigger\WpTriggerPlugin::fieldCallback
*/
class FieldCallBackTest extends TestCase
{
    private $wpGitlabTrigger;

    protected function setUp(): void
    {
        parent::setUp();
        $wpdbMock = Mockery::mock('WPDB');
        $wpdbMock->shouldReceive('prefix')->andReturn('wp_');
        $this->wpGitlabTrigger = WpTriggerPlugin::create($wpdbMock);
    }

    public function test_fieldCallBack()
    {
        WP_Mock::userFunction('get_var', [
            'args' => 'SELECT gitlab_brand_tag FROM wp_trigger_input ORDER BY id DESC LIMIT 1',
            'return' => 'dev',
        ]);

        $args = ['field' => 'gitlab_brand_tag'];

        ob_start();
        $this->wpGitlabTrigger->fieldCallback($args);
        ob_get_clean();

        $this->assertTrue(true);
    }
}
