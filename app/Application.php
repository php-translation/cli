<?php

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Bundle\WebServerBundle\WebServerBundle;
use Translation\Bundle\TranslationBundle;

/**
 * Application.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Application extends BaseApplication
{
    private $kernel;
    private $commandsRegistered = false;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        parent::__construct('Translation', '@package_version@');

        $this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The environment name', $kernel->getEnvironment()));
        $this->getDefinition()->addOption(new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode'));
    }

    /**
     * Gets the Kernel associated with this Console.
     *
     * @return KernelInterface A KernelInterface instance
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->kernel->boot();

        $container = $this->kernel->getContainer();

        foreach ($this->all() as $command) {
            if ($command instanceof ContainerAwareInterface) {
                $command->setContainer($container);
            }
        }

        $this->setDispatcher($container->get('event_dispatcher'));

        return parent::doRun($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    public function find($name)
    {
        $this->registerCommands();

        return parent::find($name);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        $this->registerCommands();

        return parent::get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function all($namespace = null)
    {
        $this->registerCommands();

        return parent::all($namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function getLongVersion()
    {
        return parent::getLongVersion().sprintf(' (kernel: <comment>%s</>, env: <comment>%s</>, debug: <comment>%s</>)', $this->kernel->getName(), $this->kernel->getEnvironment(), $this->kernel->isDebug() ? 'true' : 'false');
    }

    public function add(Command $command)
    {
        $this->registerCommands();

        $name = $command->getName();
        if (substr($name, 0, 12) === 'translation:') {
            $command->setName(substr($name, 12));
        }

        if (substr($name, 0, 7) === 'server:') {
            $definition = $command->getDefinition();
            if ($definition->hasOption('router')) {
                $option = $definition->getOption('router');
                $option->setDefault('web/router.php');
            }
        }


        return parent::add($command);
    }

    protected function registerCommands()
    {
        if ($this->commandsRegistered) {
            return;
        }

        $this->commandsRegistered = true;

        $this->kernel->boot();

        $container = $this->kernel->getContainer();

        foreach ($this->kernel->getBundles() as $bundle) {
            if ($bundle instanceof WebServerBundle || $bundle instanceof TranslationBundle) {
                $bundle->registerCommands($this);
            }
        }

        if ($container->hasParameter('console.command.ids')) {
            foreach ($container->getParameter('console.command.ids') as $id) {
                $this->add($container->get($id));
            }
        }
    }
}
