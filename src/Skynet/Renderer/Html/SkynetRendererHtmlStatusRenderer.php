<?php

/**
 * Skynet/Renderer/Html//SkynetRendererHtmlStatusRenderer.php
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

use Skynet\Renderer\SkynetRendererAbstract;

 /**
  * Skynet Renderer Status Renderer
  *
  */
class SkynetRendererHtmlStatusRenderer extends SkynetRendererAbstract
{     
  /** @var string[] HTML elements of output */
  private $output = [];    
  
  /** @var SkynetRendererHtmlElements HTML Tags generator */
  private $elements;  
  
  /** @var SkynetRendererHtmlConsoleRenderer Console Renderer */
  private $summaryRenderer;
  
  /** @var SkynetRendererHtmlConnectionsRenderer Connections Renderer */
  private $connectionsRenderer;
  
  /** @var SkynetRendererHtmlModeRenderer Mode Renderer */
  private $modeRenderer;
  
  /** @var SkynetRendererHtmlClustersRenderer Clusters Renderer */
  private $clustersRenderer;
  
  /** @var SkynetRendererHtmlConsoleRenderer Console Renderer */
  private $consoleRenderer;
  
  /** @var SkynetRendererHtmlDebugParser Debug Parser */
  private $debugParser;

 /**
  * Constructor
  */
  public function __construct()
  {
    $this->elements = new SkynetRendererHtmlElements();  
    $this->summaryRenderer = new  SkynetRendererHtmlSummaryRenderer();
    $this->connectionsRenderer = new  SkynetRendererHtmlConnectionsRenderer();
    $this->modeRenderer = new  SkynetRendererHtmlModeRenderer();
    $this->clustersRenderer = new  SkynetRendererHtmlClustersRenderer();
    $this->debugParser = new SkynetRendererHtmlDebugParser();
    $this->consoleRenderer = new  SkynetRendererHtmlConsoleRenderer();        
  }  
   
 /**
  * Renders monits
  *
  * @return string HTML code
  */   
  private function renderMonits()
  {
    $output = [];
    
    $c = count($this->monits);
    if($c > 0)
    {
      $output[] = $this->elements->addSectionClass('monits');
      $output[] = $this->elements->addBold('Information: ');
      foreach($this->monits as $monit)
      {
        $output[] = $monit.$this->elements->getNl();       
      } 
      $output[] = $this->elements->addSectionEnd(); 
    }    
    return implode($output);
  }
  
 /**
  * Renders tabs
  *
  * @return string HTML code
  */  
  private function renderTabs()
  {     
    $output = [];
    $output[] = $this->elements->addSectionClass('tabsHeader');
    $output[] = $this->elements->addTabBtn('States ('.count($this->statesFields).')', 'javascript:skynetControlPanel.switchTab(\'tabStates\');', 'tabStatesBtn active');
    $output[] = $this->elements->addTabBtn('Errors ('.count($this->errorsFields).')', 'javascript:skynetControlPanel.switchTab(\'tabErrors\');', 'tabErrorsBtn errors');
    $output[] = $this->elements->addTabBtn('Config ('.count($this->configFields).')', 'javascript:skynetControlPanel.switchTab(\'tabConfig\');', 'tabConfigBtn');
    $output[] = $this->elements->addTabBtn('Console', 'javascript:skynetControlPanel.switchTab(\'tabConsole\');', 'tabConsoleBtn');
    $output[] = $this->elements->addSectionEnd();     
    return implode($output);
  }
  
 /**
  * Renders errors
  *
  * @return string HTML code
  */    
  private function renderErrors()
  {
    /* Center Main : Left Column: errors */   
    $errors_class = null;
    if(count($this->errorsFields) > 0) 
    {
       $errors_class = 'error';
    }  
    
    $output = [];
    $output[] = $this->elements->addSectionClass('tabErrors');
    $output[] = $this->elements->beginTable('tblErrors');
    $output[] = $this->elements->addHeaderRow($this->elements->addSubtitle('Errors ('.count($this->errorsFields).')', $errors_class));
    $output[] = $this->debugParser->parseErrorsFields($this->errorsFields);
    $output[] = $this->elements->endTable();
    $output[] = $this->elements->addSectionEnd(); 
    
    return implode($output);   
  }  
  
 /**
  * Renders states
  *
  * @return string HTML code
  */    
  private function renderStates()
  {
    $output = [];
    
    /* Center Main : Left Column: states */
    $output[] = $this->elements->addSectionClass('tabStates');
    $output[] = $this->elements->beginTable('tblStates');
    $output[] = $this->elements->addHeaderRow($this->elements->addSubtitle('States ('.count($this->statesFields).')'));
    $output[] = $this->renderMonits();
    $output[] = $this->debugParser->parseStatesFields($this->statesFields);
    $output[] = $this->elements->endTable();
    $output[] = $this->elements->addSectionEnd();  
    
    return implode($output);   
  }
 
 /**
  * Renders config
  *
  * @return string HTML code
  */    
  private function renderConfig()
  {
    $output = [];
    
    /* Center Main : Left Column: Config */
    $output[] = $this->elements->addSectionClass('tabConfig');
    $output[] = $this->elements->beginTable('tblConfig');
    $output[] = $this->elements->addHeaderRow($this->elements->addSubtitle('Config ('.count($this->configFields).')'));
    $output[] = $this->debugParser->parseConfigFields($this->configFields);
    $output[] = $this->elements->endTable();
    $output[] = $this->elements->addSectionEnd(); 
    
    return implode($output);   
  }   
  
 /**
  * Renders errors
  *
  * @return string HTML code
  */    
  private function renderConsoleDebug()
  {
    $output = [];
    
     /* If console input */
    $output[] = $this->elements->addSectionClass('tabConsole');
    if(isset($_REQUEST['_skynetCmdConsoleInput'])) 
    {
       $output[] = $this->elements->addSectionId('consoleDebug');  
       $output[] = $this->elements->addHeaderRow($this->elements->addSubtitle('Console Input'));       
       $output[] = $this->elements->addRow($this->consoleRenderer->renderConsoleInput());
       $output[] = $this->elements->addSectionEnd(); 
    }
    $output[] = $this->elements->addSectionEnd();     
    
    return implode($output);   
  }

 /**
  * Renders warns
  *
  * @return string HTML code
  */    
  private function renderWarnings()
  {
    $output = [];
    
    /* Empty password warning */
    if(empty(\SkynetUser\SkynetConfig::PASSWORD))
    {
      $output[] = $this->elements->addBold('SECURITY WARNING: ', 'error').$this->elements->addSpan('Access password is not set yet. Use [pwdgen.php] to generate your password and place generated password into [/src/SkynetUser/SkynetConfig.php]', 'error').$this->elements->getNl();
    }
    
    /* Default ID warning */
    if(empty(\SkynetUser\SkynetConfig::KEY_ID) || \SkynetUser\SkynetConfig::KEY_ID == '1234567890')
    {
      $output[] = $this->elements->addBold('SECURITY WARNING: ', 'error').$this->elements->addSpan('Skynet ID KEY is empty or set to default value. Use [keygen.php] to generate new random ID KEY and place generated key into [/src/SkynetUser/SkynetConfig.php]', 'error');
    }
    
    return implode($output);   
  }
  
 /**
  * Renders mode
  *
  * @return string HTML code
  */    
  private function renderMode()
  {   
    $output = [];
    
    $output[] = $this->elements->addSectionClass('innerMode');
    $output[] = $this->elements->addSectionClass('hdrConnection');
    $output[] = $this->modeRenderer->render();
    $output[] = $this->elements->addSectionEnd();
    $output[] = $this->elements->addSectionEnd(); 
    
    return implode($output);   
  }
  
 /**
  * Renders warns
  *
  * @return string HTML code
  */    
  private function renderClusters()
  {
    $output = [];
    
    $output[] = $this->elements->addSectionClass('innerAddresses');    
    $output[] = $this->elements->beginTable('tblClusters');
    $output[] = $this->clustersRenderer->render();    
    $output[] = $this->elements->endTable();   
    $output[] = $this->elements->addSectionEnd();   
    
    return implode($output);   
  }

 /**
  * Renders warns
  *
  * @return string HTML code
  */    
  private function renderConsole()
  {
    $output = [];
    
    $output[] = $this->elements->addSectionClass('sectionConsole');   
    $output[] = $this->consoleRenderer->renderConsole();
    $output[] = $this->elements->addSectionEnd();  
    
    return implode($output);   
  }   
  
 /**
  * Renders and returns debug section
  *
  * @return string HTML code
  */     
  public function render()
  {
    $this->modeRenderer->setConnectionMode($this->connectionMode);
    $this->clustersRenderer->setClustersData($this->clustersData);
     
    $output = [];     
     
    /* Center Main : Left Column */
    $output[] = $this->elements->addSectionClass('columnDebug');      
    
      $output[] = $this->elements->addSectionClass('sectionStatus');       
      
        $output[] = $this->elements->addSectionClass('sectionAddresses');  
        $output[] = $this->renderMode();    
        $output[] = $this->renderClusters();    
        $output[] = $this->elements->addSectionEnd(); 
        
        
        $output[] = $this->elements->addSectionClass('sectionStates');   
        
          $output[] = $this->elements->addSectionClass('innerStates');    
          $output[] = $this->renderWarnings();      
          $output[] = $this->renderTabs();    
          $output[] = $this->renderErrors();
          $output[] = $this->renderConsoleDebug();
          $output[] = $this->renderStates();
          $output[] = $this->renderConfig();    
          $output[] = $this->elements->addSectionEnd();     
          
        /* end sectionStates */
        $output[] = $this->elements->addSectionEnd(); 
        
        $output[] = $this->elements->addClr();     
       
      /* end sectionStatus */
      $output[] = $this->elements->addSectionEnd();     
      
      $output[] = $this->renderConsole();   

    /* Center Main : Left Column: END */  
    $output[] = $this->elements->addSectionEnd(); 
    
    return implode('', $output);
  } 
}