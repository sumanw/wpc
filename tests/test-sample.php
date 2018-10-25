<?php
/**
 * Class SampleTest
 *
 * @package WPC
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 * @test
	 */
	public function test_sample() {
		// Replace this with some actual testing code.		
		$this->assertTrue( true );
	}

/**
	 * A single example to test empty array.
	 * @test
	 */
	public function testEmpty()
    {
        $stack = [];
        $this->assertEmpty($stack);

        return $stack;
    }
}
