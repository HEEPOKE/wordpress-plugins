<?php

use PHPUnit\Framework\TestCase;
use App\WpGitlabTrigger\WpTriggerPlugin;

class InstanceTest extends TestCase
{
    public function test_instance()
    {
        $wpdbMock = Mockery::mock('WPDB');
        $wpdbMock->shouldReceive('prefix')->andReturn('wp_');

        $instance = WpTriggerPlugin::create($wpdbMock);
        $sameInstance = WpTriggerPlugin::create($wpdbMock);

        $this->assertSame($instance, $sameInstance);
    }
}
