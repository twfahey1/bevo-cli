<?php

namespace Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;


class AccessTokenManager {

  private $accessTokenLocation;

  public function __construct() 
  {
    $this->accessTokenLocation = '.bevo/access-token.txt';
  }
  
  /**
   * Retrieve saved access token.
   */
  public function getAccessToken() {
    $contents = file_get_contents($this->accessTokenLocation);
    return $contents;
  }

  /**
   * Save access token to a file.
   */
  public function saveAccessToken($token) {
    $filesystem = new Filesystem();
    $filesystem->dumpFile($this->accessTokenLocation, $token);
  }
}