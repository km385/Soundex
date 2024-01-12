<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolsTest extends TestCase
{
    public function test_cutter_tool_page_is_displayed(): void
    {
        $response = $this
            ->get('/tools/cutter');

        $response->assertOk();
    }

    public function test_diagnosis_tool_page_is_displayed(): void
    {
        $response = $this
            ->get('/tools/diagnosis');

        $response->assertOk();
    }

    public function test_tag_editor_tool_page_is_displayed(): void
    {
        $response = $this
            ->get('/tools/tageditor');

        $response->assertOk();
    }
}
