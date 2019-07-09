<?php

namespace Genarito\GeoPHP;

use \Genarito\GeoPHP\Geometry;
use \Genarito\GeoPHP\Line;

/**
 * Represents a single point in 2D space.
 */
class Point implements Geometry {
    private $x;
    private $y;

    /**
     * Constructor
     * @param float|double $x Value for x
     * @param float|double $y Value for y
     * @return Point New instance
     */
    public function __construct($x = 0, $y = 0) {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * Getter of x
     * @return float|double Value of x
     */
    public function getX() {
        return $this->x;
    }

    /**
     * Setter of x
     * @param float|double $newX New value for x
     * @return Point Instance
     */
    public function setX($newX) {
        $this->x = $newX;
        return $this;
    }

    /**
     * Getter of y
     * @return float|double Value of y
     */
    public function getY() {
        return $this->y;
    }

    /**
     * Setter of y
     * @param float|double $newY New value for y
     * @return Point Instance
     */
    public function setY($newY) {
        $this->y = $newY;
        return $this;
    }

    /**
     * Getter for x and y
     * @return float|double[] Array with two values: X (0) and Y (1)
     */
    public function getXAndY() {
        return [$this->x, $this->y];
    }

    /**
     * Checks whether two points are equals
     * (i.e. have the same coordinates)
     * @param Point $otherPoint The second point to compare
     * @return bool True if are equals, false otherwise
     */
    public function isEqual(Point $otherPoint) {
        return $this->x === $otherPoint->getX()
            && $this->y === $otherPoint->getY();
    }

    /**
     * Computes the magnitude of the point's vector
     * @return float|double Magnitude of the point's vector
     */
    public function getMagnitude() {
        return sqrt($this->x ** 2 + $this->y ** 2);
    }

    /**
     * Converts x and y components of the Point from radians to degrees
     * @return Point Point with its components in degrees
     */
    public function fromRadiansToDegrees() {
        return new Point(
            rad2deg($this->x),
            rad2deg($this->y)
        );
    }

    /**
     * Converts x and y components of the Point from degrees to radians
     * @return Point Point with its components in radians
     */
    public function fromDegreesToRadians() {
        return new Point(
            rad2deg($this->x),
            rad2deg($this->y)
        );
    }

    /**
     * Computes the angle between two points
     * @param Point $otherPoint Other point to get the angle
     * @param bool $inDegrees If true the result is returned in degrees. In radians otherwise
     * @return float|double The angle between the two points
     */
    public function getAngle(Point $otherPoint, $inDegrees = true) {
        // Get magnitudes
        $magnitudeThis = $this->getMagnitude();
        $magnitudeOtherPoint = $otherPoint->getMagnitude();

        // Computes angle
        $angleInRadians = acos(($this->x * $otherPoint->getX() + $this->y * $otherPoint->getY()) / ($magnitudeThis * $magnitudeOtherPoint));
        return $inDegrees ? rad2deg($angleInRadians) : $angleInRadians;
    }

    /**
     * Computes the dot product of the two points
     * @param Point $otherPoint Other point to compute the dot product
     * @return float|double Dot product = x1 * x2 + y1 * y2
     */
    public function dotProduct(Point $otherPoint) {
        return $this->x * $otherPoint->getX() + $this->y * $otherPoint->getY();
    }

    /**
     * Computes the cross product of the three points
     * @param Point $point2 second point to compare
     * @param Point $point3 third point to compare
     * @return float A positive value implies $this → #point2 → $point3 is counter-clockwise, negative implies clockwise.
     */
    public function crossProduct(Point $point2, Point $point3) {
        return ($point2->getX() - $this->x) * ($point3->getY() - $this->y)
            - ($point2->getY() - $this->y) * ($point3->getX() - $this->x);
    }

    /**
     * Computes the euclidean distance between two points
     * @param Point $otherPoint Other point to compute the euclidean distance
     * @return float|double Euclidean distance between the two points
     */
    public function euclideanDistance(Point $otherPoint) {
        return sqrt(
            (($otherPoint->getX() - $this->x) ** 2)
            + (($otherPoint->getY() - $this->y) ** 2)
        );
    }

    /**
     * Abstract method implementation
     */
    public function area() {
        return 0;
    }

    /**
     * Abstract method implementation
     */
    public function intersects(Geometry $otherGeometry): bool {
        $class = get_class($otherGeometry);
        switch ($class) {
            case Point::class:
                $intersects = $this->isEqual($otherGeometry);
                break;
            case Line::class:
                // Uses double dispatching
                $intersects = $otherGeometry->intersectsPoint($this);
                break;
            default:
                throw new Exception("Not valid geometry", 1);
                break;
        }

        return $intersects;
    }
}


?>