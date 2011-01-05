<?php
/**
 * @package sfAmazonPlugin
 * @subpackage s3
 * @author Joshua Estes <Joshua.Estes@ScenicCityLabs.com>
 */
class s3SyncTask extends sfBaseTask
{
  /**
   * @var AmazonS3
   */
  private $s3 = null;

  protected function configure()
  {
     $this->addArguments(array(
       new sfCommandArgument('source', sfCommandArgument::REQUIRED, 'Path to source folder'),
       new sfCommandArgument('destination', sfCommandArgument::REQUIRED, 'Path to dest folder'),
     ));

    $this->addOptions(array(
      new sfCommandOption('app', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name','frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 's3';
    $this->name             = 'sync';
    $this->briefDescription = 'Sync files with an amazon s3 bucket';
    $this->detailedDescription = <<<EOF
The [s3:sync|INFO] task does things.
Call it with:

  [php symfony s3:sync|INFO]


EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->configuration = ProjectConfiguration::getApplicationConfiguration($options['app'], $options['env'], true);

    if (!sfConfig::get('app_sf_amazon_plugin_access_key', false))
      throw new sfException(sprintf('You have not set an amazon access key'));

    if (!sfConfig::get('app_sf_amazon_plugin_secret_key', false))
      throw new sfException(sprintf('You have not set an amazon secret key'));

    $this->s3 = new AmazonS3(sfConfig::get('app_sf_amazon_plugin_access_key'), sfConfig::get('app_sf_amazon_plugin_secret_key'));

    if (strpos($arguments['source'], ':'))
      $this->syncFromS3($arguments,$options);
    else
      $this->syncToS3($arguments,$options);
  }

  protected function syncToS3($arguments = array(), $options = array())
  {
    list($bucket,$prefix) = explode(':', $arguments['destination']);
    $file_list = sfFinder::type('file')->in($arguments['source']);
    
    $object_list_response = $this->s3->list_objects($bucket);

    if (!$object_list_response->isOk())
      throw new sfException($object_list_response->body->Message);

    if (isset($object_list_response->body->Contents))
    {
      foreach ($object_list_response->body->Contents as $object)
      {
        // var_dump($object->LastModified);
        $object_list[] = $object->Key;
      }
    }

    $files_queued = 0;
    foreach ($file_list as $file)
    {
      $filename = explode(DIRECTORY_SEPARATOR,$file);
      $filename = array_pop($filename);
      $offset = strpos($file, $arguments['source']);
      $s3_location = substr(str_replace($arguments['source'],'',substr($file,$offset)),1);

      if (in_array($s3_location, $object_list))
        continue;
      
      $this->s3->batch()->create_object($bucket,$s3_location,array(
        'fileUpload' => $file
      ));
      $files_queued++;
      $this->logSection('file+',$bucket.':'.$s3_location);
    }

    if ($files_queued <= 0)
    {
      $this->log('All files have already been synced, no need to upload any files');
      return;
    }

    $upload_response = $this->s3->batch()->send();

    if (!$upload_response->areOk())
      throw new sfException($upload_response->body->Message);

    $this->log('Files synced to bucket');
  }

  protected function syncFromS3($arguments = array(), $options = array())
  {

  }
}
