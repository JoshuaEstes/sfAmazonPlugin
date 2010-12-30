<?php

include_once dirname(__FILE__).'/../bootstrap/bootstrap.php';
include_once dirname(__FILE__).'/../../lib/vendor/amazon/sdk.class.php'; 

$t = new lime_test();

$aws_access_key = sfConfig::get('app_sf_amazon_plugin_access_key');
$aws_secret_key = sfConfig::get('app_sf_amazon_plugin_secret_key');

$t->info($aws_access_key);
$t->info($aws_secret_key);

if (!$aws_access_key || !$aws_secret_key)
{
  $t->fail('Set your keys');
  exit;
}

$ec2 = new AmazonEC2($aws_access_key, $aws_secret_key);


