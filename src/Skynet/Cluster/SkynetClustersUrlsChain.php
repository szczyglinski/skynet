<?php

/**
 * Skynet/Cluster/SkynetClustersUrlsChain.php
 *
 * @package Skynet
 * @version 1.0.0
 * @author Marcin Szczyglinski <szczyglis83@gmail.com>
 * @link http://github.com/szczyglinski/skynet
 * @copyright 2017 Marcin Szczyglinski
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since 1.0.0
 */

namespace Skynet\Cluster;

use Skynet\Error\SkynetErrorsTrait;
use Skynet\State\SkynetStatesTrait;
use Skynet\Common\SkynetHelper;

 /**
  * Skynet Cluster Urls Chain
  *
  * Stores clusters URLs in encoded chain
  */
class SkynetClustersUrlsChain
{
  /** @var string Raw urls chain */
  private $rawUrlsChain;

  /** @var string Decrypted urls chain */
  private $decryptedUrlsChain;

  /** @var string[] Array of clusters in chain */
  private $clusters = [];

  /** @var SkynetRequest Assigned request */
  private $request;

  /** @var string My cluster URL */
  private $myUrl;

  /** @var string Sender URL */
  private $senderUrl;

 /**
  * Constructor
  */
  public function __construct()
  {
    $this->myUrl = SkynetHelper::getMyUrl();
  }

 /**
  * Assigns $request object
  *
  * @param SkynetRequest $request
  */
  public function assignRequest(SkynetRequest $request)
  {
    $this->request = $request;
  }

 /**
  * Loads clusters urls saved in request into $this->clusters[] array
  */
  public function loadFromRequest()
  {
    if($this->request === null) return false;
    if($this->request->get('_skynet_clusters_chain') !== null)
    {
      $this->senderUrl = $this->request->getSenderClusterUrl();
      $this->rawUrlsChain = $this->request->get('_skynet_clusters_chain');
      $this->decryptedUrlsChain = $this->decryptRawChain($this->rawUrlsChain);
      $this->explodeChain();
    }
  }

 /**
  * Checks if cluster url exists in $this->clusters[] array
  *
  * @param string $url Cluster URL to check
  *
  * @return bool True if exists
  */
  public function isClusterInChain($url)
  {
    if(in_array($url, $this->clusters))
    {
      return true;
    }
  }

 /**
  * Checks if my cluster url exists in $this->clusters[] array
  *
  * @return bool True if exists
  */
  public function isMyClusterInChain()
  {
    if(in_array($this->myUrl, $this->clusters))
    {
      return true;
    }
  }

 /**
  * Checks if request sender's cluster url exists in $this->clusters[] array
  *
  * @return bool True if exists
  */
  public function isSenderClusterInChain()
  {
    if(in_array($this->senderUrl, $this->clusters))
    {
      return true;
    }
  }

 /**
  * Adds cluster URL into $this->clusters[] array
  *
  * @param string $url Cluster URL to add
  */
  public function addClusterToChain($url)
  {
    if($url !== null && !empty($url)) 
    {
      $this->clusters[] = $url;
    }
  }

 /**
  * Adds my cluster URL into $this->clusters[] array
  *
  * @return bool
  */
  public function addMyClusterToChain()
  {
    return $this->addClusterToChain($this->myUrl);
  }

 /**
  * Adds request sender's cluster URL into $this->clusters[] array
  *
  * @return bool
  */
  public function addSenderClusterToChain()
  {
    return $this->addClusterToChain($this->senderUrl);
  }

 /**
  * Returns base64 encrypted chain generated from $this->clusters[] array
  *
  * @return string Base64 encode clusters urls chain
  */
  public function getClustersUrlsChain()
  {
    return $this->encryptRawChain(implode(';', $this->clusters));
  }

 /**
  * Returns NOT encrypted raw chain generated from $this->clusters[] array
  *
  * @return string Base64 encode clusters urls chain
  */
  public function getClustersUrlsPlainChain()
  {
    return implode(';', $this->clusters);
  }

 /**
  * Encode raw chain by base64
  *
  * @param string $chain
  *
  * @return string Base64 encoded urls chain
  */
  private function encryptRawChain($chain)
  {
    return base64_encode($chain);
  }

 /**
  * Decode raw chain by base64
  *
  * @param string $chain
  *
  * @return string Base64 decoded urls chain
  */
  private function decryptRawChain($chain)
  {
    return base64_decode($chain);
  }

 /**
  * Explode decoded urls chain and returns aray of clusters
  *
  * @return string[] Array of clusters
  */
  private function explodeChain()
  {
    $this->clusters = explode(';', $this->decryptedUrlsChain);
    return $this->clusters;
  }
}