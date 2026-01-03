<?php

declare(strict_types=1);

namespace HostUK\TreesForAgents\Tests\Unit;

use HostUK\TreesForAgents\Services\AgentDetection;
use Orchestra\Testbench\TestCase;

class AgentDetectionTest extends TestCase
{
    private AgentDetection $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new AgentDetection();
    }

    /** @test */
    public function it_detects_claude_from_user_agent(): void
    {
        $identity = $this->detector->detect([
            'HTTP_USER_AGENT' => 'claude-3-opus-20240229',
        ]);

        $this->assertNotNull($identity);
        $this->assertEquals('anthropic', $identity->provider);
        $this->assertEquals('claude-opus', $identity->model);
    }

    /** @test */
    public function it_detects_gpt_from_user_agent(): void
    {
        $identity = $this->detector->detect([
            'HTTP_USER_AGENT' => 'gpt-4-turbo',
        ]);

        $this->assertNotNull($identity);
        $this->assertEquals('openai', $identity->provider);
        $this->assertEquals('gpt-4-turbo', $identity->model);
    }

    /** @test */
    public function it_detects_gemini_from_user_agent(): void
    {
        $identity = $this->detector->detect([
            'HTTP_USER_AGENT' => 'gemini-pro',
        ]);

        $this->assertNotNull($identity);
        $this->assertEquals('google', $identity->provider);
        $this->assertEquals('gemini-pro', $identity->model);
    }

    /** @test */
    public function it_returns_null_for_unknown_user_agent(): void
    {
        $identity = $this->detector->detect([
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        $this->assertNull($identity);
    }

    /** @test */
    public function it_handles_missing_user_agent(): void
    {
        $identity = $this->detector->detect([]);

        $this->assertNull($identity);
    }
}
