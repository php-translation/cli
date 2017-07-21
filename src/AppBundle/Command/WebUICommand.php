<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebUICommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('webui')
            ->setDescription('Runs a local web server with web UI')
        ;
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $routerFile = $this->getContainer()->getParameter('kernel.cache_dir').'/translation_router.php';
        $this->createRouter($routerFile);

        $command = $this->getApplication()->find('server:run');
        $arguments = array(
            'command' => 'server:run',
            '--router' => $routerFile,
        );

        $commandInput = new ArrayInput($arguments);

        return $command->run($commandInput, $output);
    }

    /**
     * Check if there is a custom router.
     */
    private function createRouter($file)
    {
        if (file_exists($file)) {
            return;
        }

        $pharName = __FILE__;
        $content = <<<ROUTER
<?php

require_once "$pharName";

ROUTER;

        file_put_contents($file, $content);
    }
}
