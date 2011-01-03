<?php

/**
 * @package sfAmazonPlugin
 * @subpackage S3
 * @author Joshua Estes <Joshua.Estes@ScenicCityLabs.com>
 */

/**
 *
 * @param string  $path
 * @param boolean $absolute
 * @return string
 */
function s3_upload_path($path, $absolute=false)
{
  $uploadPath = sfConfig::get('sf_upload_read_dir');
  if (0 === strpos($uploadPath . '/' . $path))
  {
    return $uploadPath . '/' . $path;
  }
  else
  {
    $uploadPath = str_replace(sfConfig::get('sf_web_dir'), '', $uploadPath);
    
    return public_path($uploadPath . '/' . $path, $absolute);
  }
}