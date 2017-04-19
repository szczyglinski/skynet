<?php

/**
 * Skynet/Renderer/Html//SkynetRendererHtmlDebugRenderer.php
 *
 * @package Skynet
 * @version 1.0.0
 * @author Marcin Szczyglinski <szczyglis83@gmail.com>
 * @link http://github.com/szczyglinski/skynet
 * @copyright 2017 Marcin Szczyglinski
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since 1.0.0
 */

namespace Skynet\Renderer\Html;

 /**
  * Skynet Renderer Debug Renderer
  *
  */
class SkynetRendererHtmlDebugRenderer
{     
  /** @var SkynetRendererHtmlElements HTML Tags generator */
  private $elements;
  

 /**
  * Constructor
  */
  public function __construct()
  {
    $this->elements = new SkynetRendererHtmlElements();   
  }  
  
 /**
  * Assigns Elements Generator
  *
  * @param SkynetRendererHtmlElements $elements
  */
  public function assignElements($elements)
  {
    $this->elements = $elements;   
  } 
  
 /**
  * Parses assigned custom fields
  *
  * @param SkynetField[] $fields
  *
  * @return string HTML code
  */    
  public function parseFields($fields)
  {
    $rows = [];
    foreach($fields as $field)
    {
      $rows[] = $this->elements->addValRow($this->elements->addBold($field->getName()), $field->getValue());
    }    
    return implode('', $rows);
  }
  
 /**
  * Parses assigned states data
  *
  * @param SkynetField[] $fields
  *
  * @return string HTML code
  */    
  public function parseStatesFields($fields)
  {
    $rows = [];
    foreach($fields as $field)
    {
      $rows[] = $this->elements->addRow($this->elements->addBold($field->getName()).' '.$field->getValue());   
    }    
    return implode('', $rows);
  } 

 /**
  * Parses assigned errors data
  *
  * @param SkynetField[] $fields
  *
  * @return string HTML code
  */   
  public function parseErrorsFields($fields)
  {
    $rows = [];
    foreach($fields as $field)
    {
      $errorData = $field->getValue();
      $errorMsg = $errorData[0];
      $errorException = $errorData[1];
      
      $ex = '';
      if($errorException !== null && \SkynetUser\SkynetConfig::get('debug_exceptions'))
      {
        $ex = $this->elements->addSpan($this->elements->getNl().
        $this->elements->addBold('Exception: ').$errorException->getMessage().$this->elements->getNl().
        $this->elements->addBold('File: ').$errorException->getFile().$this->elements->getNl().
        $this->elements->addBold('Line: ').$errorException->getLine().$this->elements->getNl().
        $this->elements->addBold('Trace: ').str_replace('#', $this->elements->getNl().'#', $errorException->getTraceAsString()), 'exception');
      }
      $rows[] = $this->elements->addRow($this->elements->addBold($field->getName(), 'error').' '.$this->elements->addSpan($errorData[0], 'error').$ex);      
    }  
    if(count($rows) == 0) 
    {
      return $this->elements->addRow('-- no errors --');
    }    
    return implode('', $rows);
  }
  
 /**
  * Parses config value
  * 
  * @param mixed $value
  *
  * @return string HTML code
  */    
  public function parseConfigValue($value)
  {
    $parsed = $value;   
    if(is_bool($value))
    {
      if($value == true)
      {
        $parsed = $this->elements->addSpan('YES', 'yes');
      } else {
        $parsed = $this->elements->addSpan('NO', 'no');
      }
    }    
    return $parsed;        
  }
  
 /**
  * Parses assigned config data
  *
  * @param SkynetField[] $fields
  *
  * @return string HTML code
  */     
  public function parseConfigFields($fields)
  {
    $rows = [];
    foreach($fields as $field)
    {
      $rows[] = $this->elements->addValRow($this->elements->addBold($field->getName()), $this->parseConfigValue($field->getValue()));  
    }
    
    return implode('', $rows);
  } 
}