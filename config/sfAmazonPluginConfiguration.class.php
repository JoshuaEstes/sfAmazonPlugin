<?php

/**
 * sfAmazonPlugin configuration.
 * 
 * @package     sfAmazonPlugin
 * @subpackage  config
 * @author      Joshua Estes <Joshua.Estes@ScenicCityLabs.com>
 */
class sfAmazonPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '1.0.0';

  public function initialize()
  {
    $this->initializeAmazonS3();
  }

  public function initializeAmazonS3()
  {
    $uploadDir = sfConfig::get('sf_upload_dir');
    if (
      sfConfig::get('app_sf_amazon_plugin_s3_enabled', false)
      &&
      $bucket = sfConfig::get('app_sf_amazon_plugin_s3_bucket', false)
    )
    {
//      $s3 = new AmazonS3(
//          sfConfig::get('app_sf_amazon_plugin_access_key'),
//          sfConfig::get('app_sf_amazon_plugin_secret_key')
//      );

      $path = str_replace(sfConfig::get('sf_web_dir'), '', $uploadDir);
      sfConfig::add(array(
          'sf_upload_read_dir' => 'http://' . $bucket . '.s3.amazonaws.com' . $path,
          'sf_upload_write_dir' => 's3://' . $bucket . $path,
        ));
    }
    else
    {
      sfConfig::add(array(
          'sf_upload_read_dir' => $uploadDir,
          'sf_upload_write_dir' => $uploadDir,
        ));
    }
  }

}
