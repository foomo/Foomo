<?php
namespace Foomo;

class GDTest extends \PHPUnit_Framework_TestCase {
	public function testMaxResize()
	{
        if(function_exists('gd_info')) {
            $src = __DIR__ . \DIRECTORY_SEPARATOR . 'gdResources' . \DIRECTORY_SEPARATOR . 'source.png';
            $target = tempnam(Config::getTempDir(), 'GDTest-');
            GD::resampleImageToMaxValues('image/png', 'image/png', $src, $target, 60, 60);
            $s = getimagesize($target);
            $this->assertEquals(60, $s[1]);
		} else {
			$this->markTestSkipped('gd is not installed');
		}
    }
}