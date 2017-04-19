<?php

/**
 * Skynet/Secure/SkynetVerifier.php
 *
 * Checking and veryfing access to skynet
 *
 * @package Skynet
 * @version 1.0.0
 * @author Marcin Szczyglinski <szczyglis83@gmail.com>
 * @link http://github.com/szczyglinski/skynet
 * @copyright 2017 Marcin Szczyglinski
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since 1.0.0
 */

namespace Skynet\Secure;

use Skynet\Encryptor\SkynetEncryptorsFactory;
use Skynet\Data\SkynetRequest;
use Skynet\Data\SkynetResponse;
use Skynet\Common\SkynetTypes;
use Skynet\Common\SkynetHelper;
use Skynet\Database\SkynetDatabase;
use Skynet\Filesystem\SkynetLogFile;

 /**
  * Skynet Verifier
  *
  * Verification and validation
  */
class SkynetVerifier
{
  /** @var string Remote cluster key */
  private $requestKey;

  /** @var string This cluster key */
  private $packageKey;

  /** @var SkynetEncryptorInterface Data encryptor */
  private $encryptor;
  
  /** @var SkynetRequest Request instance */
  private $request;
  
  /** @var string Checksum */
  private $checksum;

 /**
  * Constructor
  */
  public function __construct()
  {
    $this->encryptor = SkynetEncryptorsFactory::getInstance()->getEncryptor();
    $this->packageKey = \SkynetUser\SkynetConfig::KEY_ID;    
  }
  
 /**
  * Returns hashed key
  *
  * @param SkynetRequest $request Request object
  */
  public function assignRequest(SkynetRequest $request)
  {
    $this->request = $request;
  }  
  
 /**
  * Returns hashed key
  *
  * @return string Hashed Skynet Key ID
  */
  public function getKeyHashed()
  {
    return password_hash(\SkynetUser\SkynetConfig::KEY_ID, PASSWORD_BCRYPT);    
  }
  
 /**
  * Checks for skynetID key exists in request
  *
  * @return bool True if exists
  */
  private function isRequestKey()
  {
    if(isset($_REQUEST['_skynet_id']) && !empty($_REQUEST['_skynet_id'])) 
    {
      return true;
    }
  }
  
/**
  * Checks for checksum exists in request
  *
  * @return bool True if exists
  */
  private function isChecksum()
  {
    if(isset($_REQUEST['_skynet_checksum']) && !empty($_REQUEST['_skynet_checksum'])) 
    {
      return true;
    }    
  }

 /**
  * Sets checksum from request
  *
  * @return bool True if exists
  */  
  private function getChecksum()
  {
    if(\SkynetUser\SkynetConfig::get('core_raw'))
    {
      $this->checksum = $_REQUEST['_skynet_checksum'];
    } else {
      $this->checksum = $this->encryptor->decrypt($_REQUEST['_skynet_checksum']);
    }   
  }
  
 /**
  * Generates MD5 checksum from request fields
  *
  * @param string[] $requests Array with requests
  *
  * @return string Generated MD5 checksum
  */  
  public function generateChecksum($requests)
  {
    $data = '';
    if(is_array($requests))
    {
      foreach($requests as $k => $v)
      {
        if($k != '_skynet_checksum')
        {
          $data.= $v;
        }
      }      
    }
    
    $sum = md5($data); 
    if(\SkynetUser\SkynetConfig::get('core_raw'))
    {
      return $sum;
    } else {
      return $this->encryptor->encrypt($sum);    
    }
  }
 
 /**
  * Checks data integrity
  *
  * @param string $mode Request or response
  *
  * @return bool True if checksums OK.
  */ 
  public function verifyChecksum($mode = 'request')
  {
    $data = '';
    if($this->isChecksum())
    {
      $this->getChecksum();
    }
    
    $ary = [];
    switch($mode)
    {
      case 'request':
      
      break;
      
    }
    
    if($this->checksum !== null && !empty($this->checksum))
    {
      if(isset($_REQUEST) && count($_REQUEST) > 1)
      {      
        foreach($_REQUEST as $k => $v)
        {
          if($k != '_skynet_checksum')
          {
            if(\SkynetUser\SkynetConfig::get('core_raw'))
            {
              $data.= $v;
            } else {
              $data.= $v;
            }  
          }         
        } 
        $sum = md5($data);        
        if(strcmp($sum, $this->checksum) === 0)
        {
          return true;
        }
      }     
    } 
  }

 /**
  * Validates skynetID key from request
  *
  * If requested key not match with my key then return false
  *
  * @param string $key Requested key
  *
  * @return bool True if valid
  */
  public function isRequestKeyVerified($key = null)
  {
    $success = false;
    
    if($this->isRequestKey() || $key !== null)
    {
      if($key !== null)
      {
        $this->requestKey = $key;

      } elseif(\SkynetUser\SkynetConfig::get('core_raw'))
      {
        $this->requestKey = $_REQUEST['_skynet_id'];

      } else {
        $this->requestKey = $this->encryptor->decrypt($_REQUEST['_skynet_id']);
      }
      
      if(password_verify($this->packageKey, $this->requestKey))
      {
        $success = true;
      }     
      
      if($success === true)
      {       
        return true;        
      } else {         
        $this->saveAccessLogs();
      }         
    }    
  }

 /**
  * Generates hash
  *
  * @return string Hash
  */
  public function generateHash()
  {
    $hash = sha1(SkynetHelper::getMyUrl().\SkynetUser\SkynetConfig::KEY_ID);
    return $hash;
  }

 /**
  * Checks for cluster URL address is correct
  *
  * @param string $address URL to checkdate
  *
  * @return bool True if exists
  */
  public function isAddressCorrect($address)
  {
    if(
      !empty($address)
      && $address != 'http://'.SkynetHelper::getMyUrl()
      && $address != 'https://'.SkynetHelper::getMyUrl())
    {
      return true;
    }
  }

 /**
  * Checks if Skynet is not under another Skynet connection
  *
  * @return bool True if is another connection
  */
  public function isPing()
  {
    if(isset($_REQUEST['_skynet_cluster_url']))
    {
      return true;
    }
  }
  
 /**
  * Checks if Skynet has opened database view
  *
  * @return bool True if is another connection
  */
  public function isDatabaseView()
  {
    if(isset($_REQUEST['_skynetView']) && $_REQUEST['_skynetView'] == 'database')
    {
      return true;
    }
  }
  
 /**
  * Checks env for CLI
  *
  * @return bool True if in console
  */ 
  public function isCli()
  {
    if(defined('STDIN'))
    {
       return true;
    }

    if(php_sapi_name() === 'cli')
    {
       return true;
    }

    if(array_key_exists('SHELL', $_ENV)) 
    {
       return true;
    }

    if(empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) 
    {
       return true;
    } 

    if(!array_key_exists('REQUEST_METHOD', $_SERVER))
    {
       return true;
    }

    return false;
  }

 /**
  * Checks for parameter is internal Skynet core param
  *
  * @param string $key Key to check
  *
  * @return bool True if internal param
  */
  public function isInternalParameter($key)
  {
     if(strpos($key, '_skynet') === 0 || strpos($key, '_skynet') === 1)
     {
       return true;
     }
  }
  
 /**
  * Saves access logs
  *
  * @param string[] $request Array with requests
  */ 
  public function saveAccessLogs($request = null)
  {
    if($request === null && isset($_REQUEST))
    {
      $request = $_REQUEST;
    }
    if(\SkynetUser\SkynetConfig::get('logs_txt_access_errors'))
    {
      $this->saveAccessErrorInLogFile($request);
    }
    if(\SkynetUser\SkynetConfig::get('logs_db_access_errors'))
    {
      $this->saveAccessErrorInDb($request);   
    }   
  }
  
 /**
  * Save access error in file
  *
  * @param string[] $requestAry Array with requests
  *
  * @return bool
  */ 
  private function saveAccessErrorInLogFile($requestAry = null)
  {
    $fileName = 'access';
    $logFile = new SkynetLogFile('UNAUTHORIZED ACCESS');
    $logFile->setFileName($fileName);
    $logFile->setTimePrefix(false);
    $logFile->setHeader("#UNAUTHORIZED ACCESS ERRORS:");    
    $time_prefix = '@'.date('H:i:s d.m.Y').' ['.time().']: ';
    $data = implode('; ', $requestAry);    
    
    if($requestAry === null && $this->request !== null)
    {
      $requestAry = $this->request->getRequestsData();
    }
    
    $remote_host = '';
    $remote_cluster = '';
    $request_uri = '';
    $remote_ip = '';
    if(isset($_SERVER['REMOTE_ADDR']))
    {
       $request_uri = $_SERVER['REQUEST_URI'];
    }
    if(isset($_SERVER['REMOTE_ADDR']))
    {
       $remote_ip = $_SERVER['REMOTE_ADDR'];
    }    
    if(isset($_SERVER['REMOTE_HOST']))
    {
      $remote_host = $_SERVER['REMOTE_HOST'];
    }
    if(isset($requestAry['_skynet_cluster_url'])) $remote_cluster = $requestAry['_skynet_cluster_url'];
    
    $logFile->addLine($time_prefix.' {');  
    $logFile->addLine('@REMOTE_CLUSTER_URL: '.$remote_cluster); 
    $logFile->addLine('@REQUEST URI: '.$request_uri);    
    $logFile->addLine('@REMOTE_CLUSTER_HOST: '.$remote_host);
    $logFile->addLine('@REMOTE_CLUSTER_UP: '.$remote_ip);
    $logFile->addLine('#RAW REQUEST:');
    foreach($requestAry as $k => $v)
    {
      $logFile->addLine(' '.$k.': '.$v);  
    }
    $logFile->addLine('}');     
    $logFile->addLine();
    return $logFile->save('after');
  }
 
 /**
  * Save access error in database
  *
  * @param string[] $requestAry Array with requests
  *
  * @return bool
  */  
  private function saveAccessErrorInDb($requestAry = null)
  {
    $db = SkynetDatabase::getInstance()->getDB();
    if($requestAry === null && $this->request !== null)
    {
      $requestAry = $this->request->getRequestsData();
    }
    
    $rawRequest = '';
    foreach($requestAry as $k => $v)
    {
      $rawRequest = $k.'='.$v.';';
    }
    
    try
    {
      $stmt = $db->prepare(
        'INSERT INTO skynet_access_errors (skynet_id, created_at, request, remote_cluster, request_uri, remote_host, remote_ip)
        VALUES(:skynet_id, :created_at, :request, :remote_cluster, :request_uri, :remote_host, :remote_ip)'
        );
      $time = time();
      $remote_host = '';
      $remote_cluster = '';
      $request_uri = '';
      $remote_ip = '';
      if(isset($_SERVER['REMOTE_ADDR']))
      {
         $request_uri = $_SERVER['REQUEST_URI'];
      }
      if(isset($_SERVER['REMOTE_ADDR']))
      {
         $remote_ip = $_SERVER['REMOTE_ADDR'];
      }    
      if(isset($_SERVER['REMOTE_HOST']))
      {
        $remote_host = $_SERVER['REMOTE_HOST'];
      }
      if(isset($requestAry['_skynet_cluster_url'])) $remote_cluster = $requestAry['_skynet_cluster_url'];
      $skynet_id = \SkynetUser\SkynetConfig::KEY_ID;    
      
      $stmt->bindParam(':skynet_id', $skynet_id, \PDO::PARAM_STR);
      $stmt->bindParam(':created_at', $time, \PDO::PARAM_INT);
      $stmt->bindParam(':request', $rawRequest, \PDO::PARAM_STR);
      $stmt->bindParam(':remote_cluster', $remote_cluster, \PDO::PARAM_STR);
      $stmt->bindParam(':request_uri', $request_uri, \PDO::PARAM_STR);
      $stmt->bindParam(':remote_host', $remote_host, \PDO::PARAM_STR);
      $stmt->bindParam(':remote_ip', $remote_ip, \PDO::PARAM_STR);
      if($stmt->execute())
      {
        return true;
      }    
    } catch(\PDOException $e)  
    {  
      $this->addError(SkynetTypes::PDO, 'DB SAVE ACCESS ERROR: '.$e->getMessage(), $e);
    }
  }
}