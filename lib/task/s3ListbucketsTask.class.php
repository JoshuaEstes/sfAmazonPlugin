<?php

class s3ListbucketsTask extends sfBaseTask
{

  protected function configure()
  {
    // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('app', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace = 's3';
    $this->name = 'list-buckets';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [s3:list-buckets|INFO] task does things.
Call it with:

  [php symfony s3:list-buckets|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->configuration = ProjectConfiguration::getApplicationConfiguration($options['app'], $options['env'], true);
    // initialize the database connection
//    $databaseManager = new sfDatabaseManager($this->configuration);
//    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    if (!sfConfig::get('app_sf_amazon_plugin_access_key', false))
      throw new sfException(sprintf('You have not set an amazon access key'));

    if (!sfConfig::get('app_sf_amazon_plugin_secret_key', false))
      throw new sfException(sprintf('You have not set an amazon secret key'));

    $s3 = new AmazonS3(sfConfig::get('app_sf_amazon_plugin_access_key'), sfConfig::get('app_sf_amazon_plugin_secret_key'));

    $response = $s3->list_buckets();

    if (!isset($response->body->Buckets->Bucket))
      throw new sfException($response->body->Message);

    foreach ($response->body->Buckets->Bucket as $bucket)
      $this->logSection(sprintf('%s', $bucket->Name), sprintf('created at "%s"', $bucket->Name, $bucket->CreationDate));
  }

}
