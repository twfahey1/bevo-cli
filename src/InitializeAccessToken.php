<?php

namespace Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

define('UTDK_SCAFFOLD_ZIP_URL', 'https://github.austin.utexas.edu/eis1-wcs/utdk_scaffold/archive/master.zip');
define ('UTDK_ACCESS_TOKEN_CREATION_PAGE', 'https://github.austin.utexas.edu/settings/tokens');

class InitializeAccessToken extends Command
{

    /**
     * The users GitHub username.
     *
     * @var string
     */
    protected $github_username;

    /**
     * The users GitHub password.
     *
     * @var string
     */
    protected $github_password;

    /**
     * The URL for UT Github tokens.
     */
    public $utdkAccessTokenCreationPage = 'https://github.austin.utexas.edu/settings/tokens';

    protected function configure()
    {
      $this-> setName('init')
        ->setDescription('Creates a new UTDK Project.')
        ->setHelp('This command allows the user to scaffold a UTDK project.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $output->writeln([
        '====**** UTDK Initialization ****====',
        '==========================================',
        '',
        'You will need an access token with "READ" permissions.',
        'Generate here: ' . $this->utdkAccessTokenCreationPage,
       
      ]);
      $helper = $this->getHelper('question');
      $accessTokenManager = new AccessTokenManager();
      $question = new Question('What is the access token?');
      $question->setHidden(true);
      $question->setHiddenFallback(false);

      $access_token = $helper->ask($input, $output, $question);
      $section = $output->section();

      $accessTokenManager->saveAccessToken($access_token);
      $output->writeln('Access token saved!');

    }
}
