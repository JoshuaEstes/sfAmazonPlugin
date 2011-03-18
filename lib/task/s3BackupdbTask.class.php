<?php

class s3BackupdbTask extends sfBaseTask
{

  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('bucket', sfCommandArgument::REQUIRED, 'S3 bucket name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace = 's3';
    $this->name = 'backup-db';
    $this->briefDescription = 'Creates a database dump of database and stores it in a S3 bucket';
    $this->detailedDescription = <<<EOF
The [s3:backup-db|INFO] task backups your database to a S3 bucket
Call it with:

  [php symfony s3:backup-db S3_BUCKET|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    if (!function_exists('exec'))
    {
      throw new sfException('You must be able to run the exec() php function.');
    }

    $amazon = new sfAmazon();
    $s3 = $amazon->getS3();
    if (!$s3->if_bucket_exists($arguments['bucket']))
    {
      throw new sfException(sprintf('The bucket "%s" does not exist', $arguments['bucket']));
    }

    $backupFiles = array();

    $configHandler = new sfDatabaseConfigHandler();
    $databases = $configHandler->getConfiguration(array($this->configuration->getRootDir() . '/config/databases.yml'));
    foreach ($databases as $databaseName => $conn)
    {
      $backupFiles[] = $backupFilename = $databaseName . '-' . date('Y-m-d') . '.gz';
      $user = $conn['param']['username'];
      $pass = $conn['param']['password'];
      $dsn = $conn['param']['dsn'];
      $host = preg_match("/host=(\w*);/", $dsn, $match);
      $host = $match[1];
      $dbname = preg_match("/dbname=(\w*)/", $dsn, $match);
      $dbname = $match[1];
      exec(sprintf('mysqldump --host=%s --user=%s --password=%s --quick --add-drop-table -v --databases %s | gzip -c > %s', $host, $user, $pass, $dbname, $backupFilename));

      $s3->batch()->create_object($arguments['bucket'], $backupFilename, array('fileUpload'=>$backupFilename));
    }

    $response = $s3->batch()->send();

    if ($response->areOk())
    {
      $this->log('pushed to s2 bucket');
      foreach ($backupFiles as $file)
      {
        unlink($file);
      }
    }
  }

}
