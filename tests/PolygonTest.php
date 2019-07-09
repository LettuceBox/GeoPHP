<?php

use \Genarito\GeoPHP\Polygon;
use \Genarito\GeoPHP\Point;

class PolygonTest extends \PHPUnit\Framework\TestCase {
	public function testInstantiationOfPolygon() {
        $somePoint = new Point(0, 0);
        $otherPoint = new Point(1, 0);
        
        // Checks correct instance
		$this->assertInstanceOf('\Genarito\GeoPHP\Polygon', new Polygon([
            $somePoint,
            $otherPoint,
            $somePoint
        ]));

        // Checks invalid Polygon
        $this->expectException(\Exception::class);
        $invalidPolygon = new Polygon([
            new Point(0, 1),
            new Point(3, 1),
            new Point(1, 1) // Not equals to first point
        ]);
    }

    /**
     * Test the area computation
     */
    public function testArea() {
        $polygon1 = new Polygon([
            new Point(0, 0),
            new Point(4, 0),
            new Point(4, 4),
            new Point(0, 4),
            new Point(0, 0),
        ]);

        $polygon2 = new Polygon([
            new Point(0, 0),
            new Point(4, 0),
            new Point(2, 4),
            new Point(0, 0)
        ]);

        $this->assertEquals(16, $polygon1->area());
        $this->assertEquals(8, $polygon2->area());
    }
}