<?php

use PHPUnit\Framework\TestCase;
use App\WpGitlabTrigger\WpTriggerPlugin;

class SectionCallBackTest extends TestCase
{
    private $wpGitlabTrigger;

    protected function setUp(): void
    {
        parent::setUp();
        $wpdbMock = Mockery::mock('WPDB');
        $wpdbMock->shouldReceive('prefix')->andReturn('wp_');
        $this->wpGitlabTrigger = WpTriggerPlugin::create($wpdbMock);
    }

    public function test_sectionCallBack()
    {
        ob_start();
        $this->wpGitlabTrigger->sectionCallback();
        $output = ob_get_clean();

        $this->assertStringContainsString('Welcome to the GitLab Trigger settings section.', $output);
        $this->assertStringContainsString('<strong>GitLab Token:</strong>', $output);
        $this->assertStringContainsString('<strong>Brand or Tag:</strong>', $output);
        $this->assertStringContainsString('<strong>Project ID:</strong>', $output);
    }
}
