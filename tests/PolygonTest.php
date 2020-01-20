<?php

use \JWare\GeoPHP\Polygon;
use \JWare\GeoPHP\Point;
use \JWare\GeoPHP\Line;

class PolygonTest extends \PHPUnit\Framework\TestCase {
	public function testInstantiationOfPolygon() {
        $somePoint = new Point(0, 0);
        $otherPoint = new Point(1, 0);
        $otherPoint2 = new Point(1, 1);
        
        // Checks correct instance
		$this->assertInstanceOf('\JWare\GeoPHP\Polygon', new Polygon([
            $somePoint,
            $otherPoint,
            $otherPoint2,
            $somePoint
        ]));

        // Checks invalid Polygon
        $this->expectException(\Exception::class);
        $invalidPolygon = new Polygon([
            new Point(0, 1),
            new Point(3, 1),
            new Point(4, 2),
            new Point(1, 1) // Not equals to first point
        ]);

        // Has only 2 different points
        $invalidPolygon2 = new Polygon([
            new Point(0, 1),
            new Point(3, 1),
            new Point(1, 1) 
        ]);
    }

    /**
     * Tests clone method
     */
    public function testClone() {
        $polygon = new Polygon([
            new Point(0, 0),
            new Point(4, 0),
            new Point(4, 4),
            new Point(0, 4),
            new Point(0, 0),
        ]);
        $clone = $polygon->clone();
        $this->assertEquals($clone->getPoints(), $polygon->getPoints());
    }

    /**
     * Tests setPoint method
     */
    public function testSetPoint() {
        $polygon = new Polygon([
            new Point(0, 0),
            new Point(4, 0),
            new Point(4, 4),
            new Point(0, 4),
            new Point(0, 0),
        ]);
        $newPoint = new Point(4, 1);
        $polygon->setPoint(1, $newPoint);
        $this->assertEquals($polygon->getPoints()[1], $newPoint);

        // Checks invalid Polygon's point setting: It's not allowed to set
        // the first or last Point. You must create a new Polygon
        $this->expectException(\Exception::class);
        $polygon->setPoint(0, $newPoint);
        $polygon->setPoint(4, $newPoint);
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

    /**
     * Test contains method
     */
    public function testContains() {
        $polygon1 = new Polygon([
            new Point(0, 0),
            new Point(4, 0),
            new Point(4, 4),
            new Point(0, 4),
            new Point(0, 0)
        ]);

        $polygon2 = new Polygon([
            new Point(4, 0),
            new Point(8, 0),
            new Point(8, 4),
            new Point(4, 4),
            new Point(4, 0)
        ]);

        $polygon3 = new Polygon([
            new Point(1, 2),
            new Point(2, 2),
            new Point(2, 3),
            new Point(1, 2)
        ]);

        $polygon4 = new Polygon([
            new Point(3, 4),
            new Point(4, 5),
            new Point(3, 5),
            new Point(3, 4)
        ]);

        $polygon5 = new Polygon([
            new Point(1, -1),
            new Point(2, 1),
            new Point(3, -1),
            new Point(2, -2),
            new Point(1, -1)
        ]);

        $line1 = new Line(
            new Point(2, 2),
            new Point(3, 3)
        );

        $line2 = new Line(
            new Point(-1, -1),
            new Point(2, 3)
        );

        // With Point
        $this->assertTrue($polygon1->containsPoint(new Point(2, 2)));
        $this->assertTrue($polygon1->containsPoint(new Point(4, 4)));
        $this->assertTrue($polygon1->containsPoint(new Point(0, 2)));
        $this->assertFalse($polygon1->containsPoint(new Point(10, 12)));
        $this->assertFalse($polygon1->containsPoint(new Point(-1, 2)));
        $this->assertFalse($polygon1->containsPoint(new Point(5, -2)));

        // With Line
        $this->assertTrue($polygon1->containsLine($line1));
        $this->assertFalse($polygon1->containsLine($line2));

        // With Polygon
        $this->assertTrue($polygon1->containsPolygon($polygon3));
        $this->assertFalse($polygon1->containsPolygon($polygon2));
        $this->assertFalse($polygon1->containsPolygon($polygon4));
        $this->assertFalse($polygon1->containsPolygon($polygon5));
    }

    /**
     * Test intersection method
     */
    public function testIntersects() {
        // Polygons
        $polygon1 = new Polygon([
            new Point(2, 4),
            new Point(4, 4),
            new Point(5, 2),
            new Point(3, 1),
            new Point(2.5, 3),
            new Point(2, 4)
        ]);

        $polygon2 = new Polygon([
            new Point(-3, -4),
            new Point(-1, -5),
            new Point(-2, -6),
            new Point(-3, -4)
        ]);

        $polygon3 = new Polygon([
            new Point(4, 3),
            new Point(6, 2.5),
            new Point(6, 1.5),
            new Point(4.5, 1.25),
            new Point(4, 3)
        ]);

        $polygon4 = new Polygon([
            new Point(-1.5, -5.5),
            new Point(-0.5, -6.5),
            new Point(-2, -7),
            new Point(-1.5, -5.5),
        ]);

        // With lines is already tested in LineTest

        // With Polygon
        $this->assertTrue($polygon1->intersectsPolygon($polygon3));
        $this->assertTrue($polygon4->intersectsPolygon($polygon2));
        $this->assertFalse($polygon1->intersectsPolygon($polygon2));
        $this->assertFalse($polygon3->intersectsPolygon($polygon2));
    }
}