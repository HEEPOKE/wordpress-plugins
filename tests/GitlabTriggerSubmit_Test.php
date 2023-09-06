<?php

use PHPUnit\Framework\TestCase;
use App\WpGitlabTrigger\WpTriggerPlugin;

class GitlabTriggerSubmitTest extends TestCase
{
    private $wpGitlabTrigger;

    protected function setUp(): void
    {
        parent::setUp();
        $wpdbMock = Mockery::mock('WPDB');
        $wpdbMock->shouldReceive('prefix')->andReturn('wp_');
        $this->wpGitlabTrigger = WpTriggerPlugin::create($wpdbMock);
    }

    public function test_gitlabTriggerSubmit()
    {
        $_POST['submit'] = 'Submit';
        WP_Mock::userFunction('get_option')->andReturn('Z6HEJofAsvsZJfMBLPsu');
        WP_Mock::userFunction('get_option')->andReturn('dev');
        WP_Mock::userFunction('get_option')->andReturn('29508317');

        ob_start();
        $this->wpGitlabTrigger->gitlabTriggerSubmit();
        ob_get_clean();
        
        $this->assertTrue(true);
    }
}
