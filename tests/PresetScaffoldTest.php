<?php

namespace Tests;

use TightenCo\Jigsaw\Scaffold\PresetScaffold;
use org\bovigo\vfs\vfsStream;

class PresetScaffoldTest extends TestCase
{
    /**
     * @test
     */
    public function named_preset_resolves_to_predefined_package_path()
    {
        $preset = $this->app->make(PresetScaffold::class);
        $package = explode('/', $preset::PRESETS['blog']);
        $vfs = vfsStream::setup('virtual', null, ['vendor' => [$package[0] => [$package[1] => []]]]);
        $preset->base = $vfs->url();

        $preset->init('blog');

        $this->assertEquals($vfs->url() . '/vendor/' . $preset::PRESETS['blog'], $preset->packagePath);
    }

    /**
     * @test
     */
    public function named_preset_resolves_to_vendor_package_path_if_not_predefined()
    {
        $preset = $this->app->make(PresetScaffold::class);
        $vfs = vfsStream::setup('virtual', null, ['vendor' => ['test' => ['package' => []]]]);
        $preset->base = $vfs->url();

        $preset->init('test/package');

        $this->assertEquals($vfs->url() . '/vendor/' . 'test/package', $preset->packagePath);
    }

    /**
     * @test
     */
    public function exception_is_thrown_if_package_can_not_be_found()
    {
        $preset = $this->app->make(PresetScaffold::class);
        $vfs = vfsStream::setup('virtual', null, ['vendor' => ['test' => ['package' => []]]]);
        $preset->base = $vfs->url();

        try {
            $preset->init('test/other-package');
            $this->fail('Exception not thrown');
        } catch (\Exception $e) {
            $this->assertContains(
                "The package 'test/other-package' could not be found.",
                $e->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function exception_is_thrown_if_package_is_missing_a_slash()
    {
        $preset = $this->app->make(PresetScaffold::class);
        $vfs = vfsStream::setup('virtual', null, ['vendor' => ['test' => ['package' => []]]]);
        $preset->base = $vfs->url();

        try {
            $preset->init('test');
            $this->fail('Exception not thrown');
        } catch (\Exception $e) {
            $this->assertContains(
                "'test' is not a valid package name.",
                $e->getMessage()
            );
        }
    }
}