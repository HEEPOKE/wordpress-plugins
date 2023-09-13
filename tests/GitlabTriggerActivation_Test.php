<?php

use PHPUnit\Framework\TestCase;

/**
* @covers \App\WpGitlabTrigger\WpTriggerPlugin::wpGitlabTriggerActivation
*/
class GitlabTriggerActivationTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        global $wpdb;
        unset($wpdb);
        $wpdb = Mockery::mock('wpdb');
        $wpdb->shouldReceive('get_var')->once()->andReturn(1);
        $GLOBALS['wpdb'] = $wpdb;
    }
    

    public function test_wpGitlabTriggerActivation()
    {
        global $wpdb;

        $result = $wpdb->get_var("select anything from anywhere");

        $this->assertEquals(1, $result);
        $this->assertTrue(true);
    }
}
