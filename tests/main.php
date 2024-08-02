<?php
use Payarc\PayarcSdkPhp\Payarc;
require_once 'vendor/autoload.php';

$payarc = new Payarc('Bearer token here', 'prod');

 echo $payarc->charges->create();