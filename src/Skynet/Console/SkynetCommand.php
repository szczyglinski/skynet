<?php

/**
 * Skynet/Console/SkynetCommand.php
 *
 * @package Skynet
 * @version 1.0.0
 * @author Marcin Szczyglinski <szczyglis83@gmail.com>
 * @link http://github.com/szczyglinski/skynet
 * @copyright 2017 Marcin Szczyglinski
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since 1.0.0
 */

namespace Skynet\Console;

 /**
  * Skynet Command
  */
class SkynetCommand
{    
  /** @var string Command name */
  private $code;
  
 /** @var mixed[] Command params */
  private $params = [];
  
  /** @var string Command helper params */
  private $helperDescription;
  

 /**
  * Constructor
  *
  * @param string $code Command code
  * @param string[] $params Command params
  * @param string $desc Command description
  */
  public function __construct($code = null, $params = null, $desc = null)
  {
    if($code !== null) $this->code = $code;
    if($params !== null) $this->params = $params;
    if($desc !== null) $this->helperDescription = $desc;
  }

 /**
  * Sets command code
  *
  * @param string $code Command code
  */
  public function setCode($code)
  {
    $this->code = $code;
  }
  
 /**
  * Sets command params
  *
  * @param string[] $params Command params
  */
  public function setParams($params)
  {
    $this->params = $params;
  }
  
 /**
  * Sets command helper description
  *
  * @param string $desc Command description
  */
  public function setHelperDescription($desc)
  {
    $this->helperDescription = $desc;
  }   
  
 /**
  * Returns command code
  *
  * @return string Command code
  */
  public function getCode()
  {
    return $this->code;
  }
  
 /**
  * Returns command params
  *
  * @return string[] Command params
  */
  public function getParams()
  {
    return $this->params;
  }
  
 /**
  * Returns command helper description
  *
  * @return string Command description
  */
  public function getHelperDescription()
  {
    return $this->helperDescription;
  }   
}