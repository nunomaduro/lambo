<?php

namespace Tests\Feature;

use App\Actions\OpenInEditor;
use App\Shell\Shell;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class OpenInEditorTest extends TestCase
{
    /** @test */
    function it_opens_the_project_folder_in_the_specified_editor()
    {
        Config::set('lambo.store.editor', 'my-editor');

        $this->mock(Shell::class, function ($shell) {
            $shell->shouldReceive('execInProject')
                ->with("my-editor .")
                ->once();
        });

        app(OpenInEditor::class)();
    }

    /** @test */
    function it_does_not_open_the_project_folder_if_an_editor_is_not_specified()
    {
        $this->assertEmpty(Config::get('lambo.store.editor'));

        $this->mock(Shell::class, function ($shell) {
            $shell->shouldNotReceive('execInProject')
                ->with("my-editor .");
        });

        app(OpenInEditor::class)();
    }
}
