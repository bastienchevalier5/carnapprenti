<?php

namespace Tests\Unit\View\Components;

use App\View\Components\AppLayout;
use Tests\TestCase;

class AppLayoutTest extends TestCase
{
    public function test_it_renders_the_app_layout_view()
    {
        $component = new AppLayout;

        $this->assertEquals('layouts.app', $component->render()->getName());
    }
}
