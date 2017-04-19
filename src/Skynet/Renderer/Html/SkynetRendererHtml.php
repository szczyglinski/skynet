<?php

/**
 * Skynet/Renderer/Html/SkynetRendererHtml.php
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

use Skynet\Error\SkynetErrorsTrait;
use Skynet\State\SkynetStatesTrait;
use Skynet\Renderer\SkynetRendererAbstract;
use Skynet\Renderer\SkynetRendererInterface;


 /**
  * Skynet Html Output Renderer 
  */
class SkynetRendererHtml extends SkynetRendererAbstract implements SkynetRendererInterface
{
  use SkynetErrorsTrait, SkynetStatesTrait;   
  

  /** @var string[] HTML elements of output */
  private $output = [];   
  
  /** @var SkynetRendererHtmlDebugRenderer Debug Renderer */
  private $debugRenderer;
  
  /** @var SkynetRendererHtmlElements HTML Tags generator */
  private $elements;
  
  /** @var SkynetRendererHtmlDatabaseRenderer Database Renderer */
  private $databaseRenderer;
  
  /** @var SkynetRendererHtmlConnectionsRenderer Connections Renderer */
  private $connectionsRenderer;
  
  /** @var SkynetRendererHtmlConsoleRenderer Console Renderer */
  private $consoleRenderer;
  

 /**
  * Constructor
  */
  public function __construct()
  {
    parent::__construct();       
     
    $this->elements = new SkynetRendererHtmlElements();    
    $this->debugRenderer = new SkynetRendererHtmlDebugRenderer();
    $this->databaseRenderer = new  SkynetRendererHtmlDatabaseRenderer();
    $this->connectionsRenderer = new  SkynetRendererHtmlConnectionsRenderer();
    $this->consoleRenderer = new  SkynetRendererHtmlConsoleRenderer();
  }
  
 /**
  * Renders and returns debug section
  *
  * @return string HTML code
  */     
  private function renderDebugSection()
  {
    $output = [];
     $errors_class = null;
     
    /* Center Main : Left Column */
    $output[] = $this->elements->addSectionId('columnDebug');   
    
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
    
    /* If console input */
    if(isset($_REQUEST['_skynetCmdConsoleInput'])) 
    {
       $output[] = $this->elements->addSectionId('consoleDebug');  
       $output[] = $this->elements->addHeaderRow($this->elements->addSubtitle('Console Input'));       
       $output[] = $this->elements->addRow($this->consoleRenderer->renderConsoleInput());
       $output[] = $this->elements->addSectionEnd(); 
    }

    /* Center Main : Left Column: summary */
    $output[] = $this->elements->addHeaderRow($this->elements->addSubtitle('Summary'));   
    
    $output[] = '<table>';
    $output[] = $this->debugRenderer->parseFields($this->fields);
    $output[] = '</table>';

    /* Center Main : Left Column: errors */   
   
    if(count($this->errorsFields) > 0) 
    {
       $errors_class = 'error';
    }  
    
    $output[] = '<table>';
    $output[] = $this->elements->addHeaderRow($this->elements->addSubtitle('Errors', $errors_class));
    $output[] = $this->debugRenderer->parseErrorsFields($this->errorsFields);
    $output[] = '</table>';


    /* Center Main : Left Column: states */
    $output[] = '<table>';
    $output[] = $this->elements->addHeaderRow($this->elements->addSubtitle('States'));
    $output[] = $this->debugRenderer->parseStatesFields($this->statesFields);
    $output[] = '</table>';

    /* Center Main : Left Column: Config */
    $output[] = '<table>';
    $output[] = $this->elements->addHeaderRow($this->elements->addSubtitle('Config'));
    $output[] = $this->debugRenderer->parseConfigFields($this->configFields);
    $output[] = '</table>';

    /* Center Main : Left Column: END */  
    $output[] = $this->elements->addSectionEnd(); 
    
    return implode('', $output);
  }

 /**
  * Renders and returns logout link
  *
  * @return string HTML code
  */    
  private function renderLogoutLink()
  {
    return $this->elements->addUrl('?_skynetLogout=1', $this->elements->addBold('LOGOUT'), false, 'aLogout');    
  }
  
 /**
  * Renders and returns connections view
  *
  * @return string HTML code
  */    
  private function renderConnectionsSection()
  {
    $output = [];   
    /* Center Main : Right Column: */
    $output[] = $this->elements->addSectionId('columnConnections');         
    $output[] = $this->connectionsRenderer->renderConnections($this->connectionsData);
    $output[] = $this->elements->addSectionEnd();  
    return implode('', $output);      
  } 

 /**
  * Renders and returns Switch View links
  *
  * @return string HTML code
  */   
  private function renderViewSwitcher()
  {    
    $modes = [];
    $modes['connections'] = 'CONNECTIONS ('.$this->connectionsCounter.')';
    $modes['database'] = 'DATABASE';   
    
    $links = [];
    foreach($modes as $k => $v)
    {
      $name = $v;
      if($this->mode == $k) 
      {
        $name = $this->elements->addBold($v, 'viewActive');
      }
      $links[] = ' <a class="aSwitch" href="?_skynetView='.$k.'" title="Switch to view: '.$v.'">'.$name.'</a> ';     
    }    
    return implode(' ', $links);
  } 

 /**
  * Renders and returns header
  *
  * @return string HTML code
  */ 
  private function renderHeaderSection()
  {
    $output = [];  
    $header = $this->elements->addSkynetHeader();   
    
    /* --- Header --- */
    $output[] = $this->elements->addSectionId('header');

    /* Header Left */
    $output[] = $this->elements->addSectionId('headerLogo');
    $output[] = $header;           
    $output[] = $this->elements->addSectionEnd();

    /* Header Right */
    $output[] = $this->elements->addSectionId('headerSwitcher');
    $output[] = $this->elements->addHtml('Select view mode: '.$this->renderViewSwitcher());
    $output[] = $this->renderLogoutLink();
    
    if($this->mode == 'connections')
    {
      $output[] = $this->connectionsRenderer->renderGoToConnection($this->connectionsData);
    }
    
    $output[] = $this->elements->addSectionEnd();

    /* Clear floats */  
    $output[] = $this->elements->addClr();
    /* !End of Header */
    $output[] = $this->elements->addSectionEnd();  

    return implode('', $output);
  }

 /**
  * Renders and returns HTML output
  *
  * @return string HTML code
  */
  public function render()
  {     
    $this->consoleRenderer->setListenersOutput($this->consoleOutput);
    
    $this->output[] = $this->elements->addHeader();
    
    /* Start wrapper div */
    $this->output[] = $this->elements->addSectionId('wrapper');    

    /* Render header */
    $this->output[] = $this->renderHeaderSection();    
   
    switch($this->mode)
    {
      case 'connections':
         /* --- Center Main --- */
         $this->output[] = $this->elements->addSectionId('main');          
         $this->output[] = $this->renderDebugSection();
         $this->output[] = $this->renderConnectionsSection();        
         $this->output[] = $this->elements->addClr();  
         $this->output[] = $this->elements->addSectionEnd();         
         
         /* Render console */
         $this->output[] = $this->elements->addSectionId('console');
         $this->output[] = $this->consoleRenderer->renderConsole();
         $this->output[] = $this->elements->addSectionEnd();    
      break; 

      case 'database':
         /* --- Center Main --- */
         $this->output[] = $this->elements->addSectionId('dbSwitch'); 
         $this->output[] = $this->databaseRenderer->renderDatabaseSwitch();
         $this->output[] = $this->elements->addSectionEnd();
         
         $this->output[] = $this->elements->addSectionId('dbRecords'); 
         $this->output[] = $this->databaseRenderer->renderDatabaseView();
         $this->output[] = $this->elements->addSectionEnd();
      break;
    }   
    /* Center Main : END */   

    /* !End of wrapper */
    $this->output[] = $this->elements->addSectionEnd();
    $this->output[] = $this->elements->addFooter();
    
    return implode('', $this->output);
  } 
}