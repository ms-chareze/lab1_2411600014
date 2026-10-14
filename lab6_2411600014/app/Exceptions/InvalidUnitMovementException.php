<?php

namespace App\Exceptions;

use DomainException;

/**
 * Thrown when a transaction would push a student's enrolled units
 * below 0 or above the maximum allowed load.
 */
class InvalidUnitMovementException extends DomainException
{
}
