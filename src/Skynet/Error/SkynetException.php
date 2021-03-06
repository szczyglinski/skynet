<?php

/**
 * Skynet/Error/SkynetException
 *
 * @package Skynet
 * @version 1.0.0
 * @author Marcin Szczyglinski <szczyglis83@gmail.com>
 * @link http://github.com/szczyglinski/skynet
 * @copyright 2017 Marcin Szczyglinski
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since 1.0.0
 */

namespace Skynet\Error;

use Skynet\Error\SkynetErrorsTrait;

 /**
  * Skynet Exception
  *
  * Operates on exceptions
  */
class SkynetException extends \Exception
{
  use SkynetErrorsTrait;
 /**
  * Constructor
  *
  * @param mixed $message
  * @param integer $code
  * @param Exception|null $previous
  */
  public function __construct($message, $code = 0, \Exception $previous = null)
  {
    parent::__construct($message, $code, $previous);
    //$this->addError(SkynetTypes::EXCEPTION, 'SkynetException: '.$message, $this);
  }
}