<?php

namespace JWare\GeoPHP;

use \JWare\GeoPHP\Geometry;
use \JWare\GeoPHP\Point;
use \JWare\GeoPHP\Line;

/**
 * Represents a single point in 2D space.
 */
class Polygon implements Geometry {
    private $points;

    /**
     * Constructor
     * @param Point[] $points Array of points that make up the polygon
     * @return Polygon New instance
     */
    public function __construct(array $points) {
        // First and last point must have the same values
        $this->setPoints($points);
    }

    /**
     * Getter of the points of the polygon
     * @return Point[] Array of points that make up the polygon
     */
    public function getPoints(): array {
        return $this->points;
    }

    /**
     * Setter for one point of the polygon
     * @param int $idx Index in the array of the polygon to replace
     * @param Point $point Point to put in the specified position
     * @return Polygon Current instance
     */
    public function setPoint(int $idx, Point $point): Polygon {
        $this->points[$idx] = $point;
        return $this;
    }

    /**
     * Setter for all the points of the polygon
     * @param Point[] $points Array of points that make up the polygon
     * @return Polygon Current instance
     */
    public function setPoints(array $points): Polygon {
        // A polygon has at least three points
        $pointsCount = count($points);
        // '< 4' because the last point has to be the same as first point
        if ($pointsCount < 4) { 
            throw new \Exception("The polygon has to have at least three diferent points", 1);
        }

        // Checks first/last equality
        $lastPosition = $pointsCount - 1;
        if (!$points[0]->isEqual($points[$lastPosition])) {
            throw new \Exception("First and last point must have the same values", 1);
        }

        $this->points = $points;
        return $this;
    }

    /**
     * Computes the polygon's centroid
     * @return Point Polygon's centroid point
     */
    private function findCentroid(): Point {
        $x = 0.0;
        $y = 0.0;
        foreach ($this->points as $point) {
            $x += $point->getX();
            $y += $point->getY();
        }

        $pointsCount = count($this->points);
        return new Point(
            $x / $pointsCount,
            $y / $pointsCount
        );
    }

    /**
     * Sorts the polygon's vertices in clockwise order
     * @return Point[] Array with the sorted vertices
     */
    private function getSortedVerticies(): array {
        // Gets polygon centroid
        $center = $this->findCentroid();
        $subArray = array_slice($this->points, 0, count($this->points) - 1);
        usort($subArray, function($point1, $point2) use ($center) {
            $a1 = (rad2deg(atan2($point1->getX() - $center->getX(), $point1->getY() - $center->getY())) + 360) % 360;
            $a2 = (rad2deg(atan2($point2->getX() - $center->getX(), $point2->getY() - $center->getY())) + 360) % 360;
            return $a2 - $a1;
        });
        $subArray[] = $subArray[0];
        return $subArray;
    }

    /**
     * Abstract method implementation
     * Uses the Shoelace Formula:
     * https://en.wikipedia.org/wiki/Shoelace_formula
     */
    public function area() {
        // Initialze area 
        $area = 0.0;

        // Sorted vertices are required
        $sortedVertices = $this->getSortedVerticies();
    
        // Calculate value of Shoelace formula 
        $j = $n = count($sortedVertices) - 1;
        for ($i = 0; $i <= $n; $i++) {
            $point1 = $sortedVertices[$i];
            $point2 = $sortedVertices[$j];
            $area += ($point2->getX() + $point1->getX()) * ($point2->getY() - $point1->getY());
            // j is previous vertex to i
            $j = $i;
        } 
    
        // Return absolute value
        return abs($area / 2.0);
    }

    /**
     * Checks whether the polygon intersects a line
     * @param Line $line Line to check
     * @return bool True if the polygon intersects with the line, false otherwise
     */
    public function intersectsLine(Line $line): bool {
        $n = sizeof($this->points);
        $polygonPoints = $this->points;

        $i = 0;
        do {
            $next = ($i + 1) % $n; 
    
            // Check if the line segment from 'p' to 'extreme' intersects 
            // with the line segment from 'polygonPoints[i]' to 'polygonPoints[next]' 
            $lineIToNext = new Line($polygonPoints[$i], $polygonPoints[$next]);
            if ($line->intersectsLine($lineIToNext)) {
                return true;
            } 
            $i = $next; 
        } while ($i != 0); 
        return false;
    }

    /**
     * Checks whether the polygon intersects another polygon
     * @param Polygon $polygon Polygon to check
     * @return bool True if the polygon intersects with the polygon, false otherwise
     */
    public function intersectsPolygon(Polygon $polygon): bool {
        $n = sizeof($this->points);
        $polygonPoints = $this->points;

        $i = 0;
        do {
            $next = ($i + 1) % $n; 
    
            // Check if the line segment from 'p' to 'extreme' intersects 
            // with the line segment from 'polygonPoints[i]' to 'polygonPoints[next]' 
            $lineIToNext = new Line($polygonPoints[$i], $polygonPoints[$next]);
            if ($polygon->intersectsLine($lineIToNext)) {
                return true;
            } 
            $i = $next; 
        } while ($i != 0); 
        return false;
    }

    /**
     * To find orientation of ordered triplet (p, q, r). 
     * The function returns following values 
     * @param Point $p Point 1
     * @param Point $q Point 2
     * @param Point $r Point 3
     * @return int 0 --> p, q and r are colinear, 1 --> Clockwise, 2 --> Counterclockwise 
     */
    private function orientation(Point $p, Point $q, Point $r): int { 
        $val = ($q->getY() - $p->getY()) * ($r->getX() - $q->getX()) - ($q->getX() - $p->getX()) * ($r->getY() - $q->getY()); 
        if ($val == 0) return 0; // colinear 
        return ($val > 0) ? 1 : 2; // clock or counterclock wise 
    }

    /**
     * Checks if a point is inside a polygon
     * @param Point $point Point to check
     * @return bool True if the point is inside the polygon, false otherwise
     */
    public function pointIsWithin(Point $point): bool {
        $n = sizeof($this->points);
        $polygonPoints = $this->points;

        // Create a line segment from p to infinite 
        $extremePoint = new Point(PHP_INT_MAX, $point->getY());
        $linePointToExtreme = new Line($point, $extremePoint);
    
        // Count intersections of the above line with sides of polygon 
        $count = 0; $i = 0; 
        do { 
            $next = ($i + 1) % $n; 
    
            // Check if the line segment from 'p' to 'extreme' intersects 
            // with the line segment from 'polygonPoints[i]' to 'polygonPoints[next]' 
            $lineIToNext = new Line($polygonPoints[$i], $polygonPoints[$next]);
            if ($linePointToExtreme->intersectsLine($lineIToNext)) {
                // If the $point is colinear with line segment 'i-next', 
                // then check if it lies on segment. If it lies, return true, 
                // otherwise false 
                if ($this->orientation($polygonPoints[$i], $point, $polygonPoints[$next]) == 0){
                    return $lineIToNext->intersectsPoint($point);
                }
    
                $count++; 
            } 
            $i = $next; 
        } while ($i != 0); 
    
        // Return true if count is odd, false otherwise 
        return $count % 2 == 1;
    }
}