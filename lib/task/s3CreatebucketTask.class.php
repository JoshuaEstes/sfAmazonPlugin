<?php

class s3CreatebucketTask extends sfBaseTask
{

  /**
   * @var CFResponse
   */
  private $s3_response;

  protected function configure()
  {
    // add your own arguments here
    $this->addArguments(array(
      new sfCommandArgument('bucket', sfCommandArgument::REQUIRED, 'Name of bucket you want to create'),
    ));

    $this->addOptions(array(
      new sfCommandOption('app', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('region', null, sfCommandOption::PARAMETER_REQUIRED, 'The region to place the bucket in', 'us-west-1'),
      new sfCommandOption('acl', null, sfCommandOption::PARAMETER_REQUIRED, 'Default ACL', 'public-read'),
      // add your own options here
    ));

    $this->namespace = 's3';
    $this->name = 'create-bucket';
    $this->briefDescription = 'Create a S3 bucket';
    $this->detailedDescription = <<<EOF
The [s3:create-bucket|INFO] task creates a S3 bucket on amazon.
Call it with:

  [php symfony s3:create-bucket bucketName|INFO]


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

    $this->s3_response = $s3->create_bucket($arguments['bucket'], $options['region'], $options['acl']);

    if ($this->s3_response->isOk())
    {
      $this->log('Bucketed is being created...');
      /* Since AWS follows an "eventual consistency" model, sleep and poll
        until the bucket is available. */
      $exists = $s3->if_bucket_exists($arguments['bucket']);
      while (!$exists)
      {
        // Not yet? Sleep for 1 second, then check again
        sleep(1);
        $exists = $s3->if_bucket_exists($arguments['bucket']);
      }
      $this->logSection('Bucket+', sprintf('"%s" created successfully',$arguments['bucket']));
    }
    else
    {
      throw new sfException($this->s3_response->body->Message);
    }
  }

}
