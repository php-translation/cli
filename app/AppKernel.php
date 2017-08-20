<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    private $projectDir;

    public function registerBundles()
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\WebServerBundle\WebServerBundle(),
            new \Translation\Bundle\TranslationBundle(),
            new \AppBundle\AppBundle(),

            new \Translation\PlatformAdapter\Loco\Bridge\Symfony\TranslationAdapterLocoBundle(),
            new \Translation\PlatformAdapter\Flysystem\Bridge\Symfony\TranslationAdapterFlysystemBundle(),
            new \Translation\PlatformAdapter\PhraseApp\Bridge\Symfony\TranslationAdapterPhraseAppBundle(),
            new \Http\HttplugBundle\HttplugBundle(),
        ];

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/php-translation/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/php-translation/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
        $config = getcwd().'/translation.yml';
        if (file_exists($config)) {
            $loader->load($config);
        }
    }

    /**
     * Gets the application root dir (path of the project's LICENSE file).
     *
     * @return string The project root dir
     */
    public function getProjectDir()
    {
        if (null === $this->projectDir) {
            $r = new \ReflectionObject($this);
            $dir = $rootDir = dirname($r->getFileName());
            while (!file_exists($dir.'/LICENSE')) {
                if ($dir === dirname($dir)) {
                    return $this->projectDir = parent::getProjectDir();
                }
                $dir = dirname($dir);
            }
            $this->projectDir = $dir;
        }

        return $this->projectDir;
    }
}
