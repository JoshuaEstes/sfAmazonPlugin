<?php

class s3DeletebucketTask extends sfBaseTask
{
  protected function configure()
  {
     $this->addArguments(array(
       new sfCommandArgument('bucket', sfCommandArgument::REQUIRED, 'Name of bucket'),
     ));

    $this->addOptions(array(
      new sfCommandOption('app', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name','frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('force', null, sfCommandOption::PARAMETER_NONE, 'Force remove of bucket and all files'),
    ));

    $this->namespace        = 's3';
    $this->name             = 'delete-bucket';
    $this->briefDescription = 'Delete an Amazon S3 Bucket';
    $this->detailedDescription = <<<EOF
The [s3:delete-bucket|INFO] task does things.
Call it with:

  [php symfony s3:delete-bucket bucketName|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->configuration = ProjectConfiguration::getApplicationConfiguration($options['app'], $options['env'], true);

    if (!sfConfig::get('app_sf_amazon_plugin_access_key', false))
      throw new sfException(sprintf('You have not set an amazon access key'));

    if (!sfConfig::get('app_sf_amazon_plugin_secret_key', false))
      throw new sfException(sprintf('You have not set an amazon secret key'));

    $s3 = new AmazonS3(sfConfig::get('app_sf_amazon_plugin_access_key'), sfConfig::get('app_sf_amazon_plugin_secret_key'));

    $response = $s3->delete_bucket($arguments['bucket'], $options['force']);

    if ($response->isOk())
    {
      $this->logSection('Bucket-', sprintf('"%s" bucket has been deleted',$arguments['bucket']));
    }
    else
    {
      throw new sfException($this->body->Message);
    }
  }
}
