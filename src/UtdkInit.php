<?php

namespace Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use ZipArchive;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class UtdkInit extends Command {

  /**
   * The UTDK Scaffold URL.
   */
  public $utdkScaffoldZipUrl = 'https://github.austin.utexas.edu/eis1-wcs/utdk_scaffold/archive/master.zip';

  /**
   * The path to the extracted zip archive.
   */
  public $pathToExtractedZip = 'utdk_scaffold-master';

  /**
   * The location of the downloaded zip archive.
   */
  public $archiveDestPath = 'utdk_scaffold-master.zip';

  protected function configure()
  {
    $this-> setName('utdk-init')
      ->setDescription('Creates a new UTDK Project.')
      ->setHelp('This command allows the user to scaffold a UTDK project.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $output->writeln([
      '====**** Creating UTDK project... ****====',
      '==========================================',
      '',
    ]);
    $pathToExtractTo = 'testing';
    $downloadUrl = $this->utdkScaffoldZipUrl;
    $archiveDestPath = $this->archiveDestPath;
    $accessTokenManager = new AccessTokenManager();
    $token = $accessTokenManager->getAccessToken();
    // Disable temporary SSL verification (IMPROVE THAT).
    $arrContextOptions = [
      "ssl" => [
        "verify_peer" => FALSE,
        "verify_peer_name" => FALSE,
      ],
      "http" => [
        "method" => "GET",
        "header" => "Accept-language: en\r\n" .
        "Cookie: foo=bar\r\n" .
        "Authorization: token $token",
      ],
    ];
    // Create ProgressBar.
    $progress = new ProgressBar($output);

    // Create Stream Context with Callback to update ProgressBar.
    $context = stream_context_create($arrContextOptions, [
      'notification' => function ($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) use ($output, $progress) {
        switch ($notification_code) {
          case STREAM_NOTIFY_RESOLVE:
          case STREAM_NOTIFY_AUTH_REQUIRED:
          case STREAM_NOTIFY_COMPLETED:
          case STREAM_NOTIFY_FAILURE:
          case STREAM_NOTIFY_AUTH_RESULT:
            // Ignore.
            break;

          case STREAM_NOTIFY_REDIRECTED:
              $output->writeln("Redirect To : ", $message);
            break;

          case STREAM_NOTIFY_CONNECT:
              $output->writeln("Connected...");
            break;

          case STREAM_NOTIFY_FILE_SIZE_IS:
              /** @var $progress ProgressBar */
              $output->writeln("Downloading...");
              $progress->start($bytes_max);
            break;

          case STREAM_NOTIFY_PROGRESS:
              $progress->setProgress($bytes_transferred);
            break;
        }
      }
      ]
    );

    // Download file.
    $section = $output->section();
    $section->writeln('Downloading the file...');
    $streamContent = file_get_contents($downloadUrl, FALSE, $context);
    $progress->finish();

    // Save File.
    file_put_contents($archiveDestPath, $streamContent);
    $section->overwrite('Uncompressing the file...');
    $zip = new ZipArchive();
    if ($zip->open($archiveDestPath) === TRUE) {
      $zip->extractTo($pathToExtractTo);
      $zip->close();
    }

    $section->overwrite('Initializing...');
    $process = new Process("cd " . $pathToExtractTo . '/' . $this->pathToExtractedZip . ' && composer install && composer run-script base-scaffold');
    $process->run();

    // Executes after the command finishes.
    if (!$process->isSuccessful()) {
      throw new ProcessFailedException($process);
    }

    // Initialize local environment.
    $check_for_docksal = new Process("fin version");
    $check_for_docksal->run();

    if (!$process->isSuccessful()) {
      $output->writeln("NO DOCKSAL FOUND! To setup local environment, you'll need Docksal available as the `fin` command.");
      return;
    }
    $initialize_docksal = new Process("cd " . $pathToExtractTo . '/' . $this->pathToExtractedZip . ' && composer run-script dev-scaffold');
    $initialize_docksal->run();
    $output->writeln("Local environment initialized...");
    $output->writeln([
      '====**** ALL SET... ****====',
      '==========================================',
      'Run `fin init && fin init-site to install the UTDK locally.',
      '',
    ]);

  }

}
