<?php

/**
 * Skynet/EventListener/SkynetEventListenerExec.php
 *
 * @package Skynet
 * @version 1.1.6
 * @author Marcin Szczyglinski <szczyglis83@gmail.com>
 * @link http://github.com/szczyglinski/skynet
 * @copyright 2017 Marcin Szczyglinski
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since 1.0.0
 */

namespace Skynet\EventListener;

use Skynet\Common\SkynetTypes;
use Skynet\Common\SkynetHelper;

 /**
  * Skynet Event Listener - Exec
  *
  * Skynet Exec & System
  */
class SkynetEventListenerExec extends SkynetEventListenerAbstract implements SkynetEventListenerInterface
{  
 /**
  * Constructor
  */
  public function __construct()
  {
    parent::__construct();   
  }

 /**
  * onConnect Event
  *
  * Actions executes when onConnect event is fired
  *
  * @param SkynetConnectionInterface $conn Connection adapter instance
  */
  public function onConnect($conn = null)  { }

 /**
  * onRequest Event
  *
  * Actions executes when onRequest event is fired
  * Context: beforeSend - executes in sender when creating request.
  * Context: afterReceive - executes in responder when request received from sender.
  *
  * @param string $context Context - beforeSend | afterReceive
  */
  public function onRequest($context = null)
  {
    if($context == 'beforeSend')
    {
      
    }
    
    if($context == 'afterReceive')
    {
      /* exec() */
      if($this->request->get('@exec') !== null)
      {
        if(!isset($this->request->get('@exec')['cmd']))
        {
          $this->response->set('@<<exec', 'COMMAND IS NULL');
          return false;
        }
        $cmd = $this->request->get('@exec')['cmd'];
        $return = null;
        $output = [];                
        $result = @exec($cmd, $output, $return);
        $this->response->set('@<<execResult', $result);
        $this->response->set('@<<execReturn', $return); 
        $this->response->set('@<<execOutput', $output); 
        $this->response->set('@<<exec', $this->request->get('@exec')['cmd']);
      } 

      /* system() */
      if($this->request->get('@system') !== null)
      {
        if(!isset($this->request->get('@system')['cmd']))
        {
          $this->response->set('@<<system', 'COMMAND IS NULL');
          return false;
        }
        $cmd = $this->request->get('@system')['cmd']; 
        $return = null;        
        $result = @system($cmd, $return);
        $this->response->set('@<<systemResult', $result);
        $this->response->set('@<<systemReturn', $return);        
        $this->response->set('@<<system', $this->request->get('@system')['cmd']);
      } 
      
      /* proc_open() */
      if($this->request->get('@proc') !== null)
      {
        if(!isset($this->request->get('@proc')['proc']))
        {
          $this->response->set('@<<proc', 'COMMAND IS NULL');
          return false;
        }
        
        $proc = $this->request->get('@proc')['proc']; 
        $return = null;   
        
        $descriptorspec = array(
            0 => array('pipe', 'r'), 
            1 => array('pipe', 'w'), 
            2 => array('pipe', 'w') 
        );

        $process = proc_open($proc, $descriptorspec, $pipes);

        if(is_resource($process)) 
        {   
          $result = stream_get_contents($pipes[1]);
          fclose($pipes[0]);
          fclose($pipes[1]);   
          fclose($pipes[2]);
          $return = proc_close($process);
        }
        
        $this->response->set('@<<procResult', $result);
        $this->response->set('@<<procReturn', $return);        
        $this->response->set('@<<proc', $this->request->get('@proc')['proc']);
      }  

      /* eval() */
      if($this->request->get('@eval') !== null)
      {
        if(!isset($this->request->get('@eval')['php']))
        {
          $this->response->set('@<<eval', 'PHP CODE IS NULL');
          return false;
        }
        $php = $this->request->get('@eval')['php'];  
        
        $result = @eval($php);
        $this->response->set('@<<evalReturn', $result); 
        $this->response->set('@<<eval', $this->request->get('@eval')['php']);
      } 
    }
  }

 /**
  * onResponse Event
  *
  * Actions executes when onResponse event is fired.
  * Context: beforeSend - executes in responder when creating response for request.
  * Context: afterReceive - executes in sender when response for request is received from responder.
  *
  * @param string $context Context - beforeSend | afterReceive
  */
  public function onResponse($context = null)
  {
    if($context == 'afterReceive')
    {
      /* exec */
      if($this->response->get('@<<exec') !== null)
      {        
        $this->addMonit('[EXEC CMD] exec($cmd, , ): '.$this->response->get('@<<exec'));
      }
      if($this->response->get('@<<execReturn') !== null)
      {        
        $this->addMonit('[EXEC RETURN] exec( , , $return): '.$this->response->get('@<<execReturn'));
      }
      if($this->response->get('@<<execResult') !== null)
      {        
        $this->addMonit('[EXEC RESULT] $result = exec(): '.$this->response->get('@<<execResult'));
      }
      if($this->response->get('@<<execOutput') !== null)
      {        
        $output = $this->response->get('@<<execOutput');
        if(is_array($output))
        {
          $output = '<br>'.implode('<br>', $output);
        }
        $this->addMonit('[EXEC OUTPUT] exec( , $output[], ): '.$output);
      }
      
      
      /* system */
      if($this->response->get('@<<system') !== null)
      {        
        $this->addMonit('[SYSTEM CMD] system($cmd, ): '.$this->response->get('@<<system'));
      }
      if($this->response->get('@<<systemReturn') !== null)
      {        
        $this->addMonit('[SYSTEM RETURN] system( , $return): '.$this->response->get('@<<systemReturn'));
      }
      if($this->response->get('@<<systemResult') !== null)
      {        
        $this->addMonit('[SYSTEM RESULT] $result = system(): '.$this->response->get('@<<systemResult'));
      }
      
      /* proc */
      if($this->response->get('@<<proc') !== null)
      {        
        $this->addMonit('[PROCESS] proc_open($proc, , ): '.$this->response->get('@<<proc'));
      }
      if($this->response->get('@<<procReturn') !== null)
      {        
        $this->addMonit('[PROCESS RETURN] $return = proc_close($proc): '.$this->response->get('@<<procReturn'));
      }
      if($this->response->get('@<<procResult') !== null)
      {        
        $output = $this->response->get('@<<procResult');
        if(is_array($output))
        {
          $output = '<br>'.implode('<br>', $output);
        }
        $this->addMonit('[PROCESS RESULT] $result = stream_get_contents():<br>'.$output);
      }
      
      /* eval */
      if($this->response->get('@<<eval') !== null)
      {        
        $this->addMonit('[EVAL]: '.$this->response->get('@<<eval'));
      }
      if($this->response->get('@<<evalReturn') !== null)
      {        
        $output = $this->response->get('@<<evalReturn');
        if(is_array($output))
        {
          $output = '<br>'.implode('<br>', $output);
        }
        $this->addMonit('[EVAL RETURN]:<br>'.$output);
      }
    }

    if($context == 'beforeSend')
    {      
      
    }
  }

 /**
  * onBroadcast Event
  *
  * Actions executes when onBroadcast event is fired.
  * Context: beforeSend - executes in responder when @broadcast command received from request.
  * Context: afterReceive - executes in sender when response for @broadcast received.
  *
  * @param string $context Context - beforeSend | afterReceive
  */
  public function onBroadcast($context = null)
  {
    if($context == 'beforeSend')
    {
      
    }
  }

 /**
  * onEcho Event
  *
  * Actions executes when onEcho event is fired.
  * Context: beforeSend - executes in responder when @echo command received from request.
  * Context: afterReceive - executes in sender when response for @echo received.
  *
  * @param string $context Context - beforeSend | afterReceive
  */
  public function onEcho($context = null)
  {
    if($context == 'beforeSend')
    {
      
    }
  }     
     
 /**
  * onCli Event
  *
  * Actions executes when CLI command in input
  * Access to CLI: $this->cli
  */ 
  public function onCli()
  {
  
  }

 /**
  * onConsole Event
  *
  * Actions executes when HTML Console command in input
  * Access to Console: $this->console
  */   
  public function onConsole()
  {    
    
  }   
  
 /**
  * Registers commands
  * 
  * Must returns: 
  * ['cli'] - array with cli commands [command, description]
  * ['console'] - array with console commands [command, description]
  *
  * @return array[] commands
  */   
  public function registerCommands()
  {    
    $cli = [];
    $console = [];
    $console[] = ['@exec', 'cmd:"commands_to_execute"', ''];     
    $console[] = ['@system', 'cmd:"commands_to_execute"', '']; 
    $console[] = ['@proc', 'proc:"proccess_to_open"', ''];
    $console[] = ['@eval', 'php:"code_to_execute"', 'no args=TO ALL'];    
    
    return array('cli' => $cli, 'console' => $console);    
  }  
    
 /**
  * Registers database tables
  * 
  * Must returns: 
  * ['queries'] - array with create/insert queries
  * ['tables'] - array with tables names
  * ['fields'] - array with tables fields definitions
  *
  * @return array[] tables data
  */   
  public function registerDatabase()
  {
    $queries = [];
    $tables = [];
    $fields = [];
    return array('queries' => $queries, 'tables' => $tables, 'fields' => $fields);  
  }
}