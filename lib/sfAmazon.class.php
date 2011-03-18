<?php

/**
 *
 * @author Joshua Estes
 * @package
 * @subpackage
 */
require sfConfig::get('sf_plugins_dir').'/sfAmazonPlugin/lib/vendor/amazon/sdk.class.php';

class sfAmazon
{
  protected $_key;
  protected $_secret_key;
  protected $_account_id;
  protected $_assoc_id;


  public function __construct()
  {
    $this->_key        = sfConfig::get('app_sf_amazon_plugin_access_key');
    $this->_secret_key = sfConfig::get('app_sf_amazon_plugin_secret_key');
    $this->_account_id = sfConfig::get('app_sf_amazon_plugin_account_id');
    $this->_assoc_id   = sfConfig::get('app_sf_amazon_plugin_associate_id');
  }

  /**
   * @return AmazonS3
   */
  public function getS3()
  {
    require sfConfig::get('sf_plugins_dir').'/sfAmazonPlugin/lib/vendor/amazon/services/s3.class.php';
    return new AmazonS3($this->_key,$this->_secret_key);
  }
}