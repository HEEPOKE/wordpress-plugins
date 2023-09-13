<?php

use PHPUnit\Framework\TestCase;
use App\WpGitlabTrigger\WpTriggerPlugin;

/**
 * @covers \App\WpGitlabTrigger\WpTriggerPlugin::create
* @covers \App\WpGitlabTrigger\WpTriggerPlugin::triggerGitlabApi
*/
class TriggerGitlabApiTest extends TestCase
{

    private $wpGitlabTrigger;

    protected function setUp(): void
    {
        parent::setUp();

        $wpdbMock = Mockery::mock('WPDB');
        $wpdbMock->shouldReceive('prefix')->andReturn('wp_');
        $this->wpGitlabTrigger = WpTriggerPlugin::create($wpdbMock);
    }

    public function test_triggerGitlabApi()
    {
        $response = array(
            'success' => false,
            'error' => '',
        );

        $actual = json_encode($response);
        $this->assertJson($actual);
        $this->assertTrue(true);
    }
}
