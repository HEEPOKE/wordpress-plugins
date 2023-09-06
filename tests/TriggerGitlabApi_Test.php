<?php

use PHPUnit\Framework\TestCase;
use App\WpGitlabTrigger\WpTriggerPlugin;

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
        WP_Mock::userFunction('get_option')
            ->times(3)
            ->andReturn('Z6HEJofAsvsZJfMBLPsu', 'dev', '29508317');

        $this->assertNotFalse($this->wpGitlabTrigger->triggerGitlabApi());
        $this->assertTrue(true);
    }
}
