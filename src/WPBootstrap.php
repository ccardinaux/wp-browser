<?php

namespace Codeception\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class WPBootstrap extends Bootstrap
{

    public function getDescription()
    {
        return "Sets up a WordPress CodeCeption testing environment.";
    }

    public function createGlobalConfig()
    {
        $basicConfig = [
            'actor' => $this->actorSuffix,
            'paths' => [
                'tests' => 'tests',
                'log' => $this->logDir,
                'data' => $this->dataDir,
                'support' => $this->helperDir,
                //'envs' => $this->envsDir,
            ],
            'settings' => [
                'bootstrap' => '_bootstrap.php',
                'colors' => (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'),
                'memory_limit' => '1024M'
            ],
            'extensions' => [
                'enabled' => ['Codeception\Extension\RunFailed']
            ],
            'modules' => [
                'config' => [
                    'Db' => [
                        'dsn' => 'mysql:host=localhost;dbname=wordpress-tests',
                        'user' => 'root',
                        'password' => 'root',
                        'dump' => 'tests/_data/dump.sql'
                    ],
                    'WPBrowser' => [
                        'url' => 'http://wp.local',
                        'adminUsername' => 'admin',
                        'adminPassword' => 'admin',
                        'adminUrl' => '/wp-admin'
                    ],
                    'WPDb' => [
                        'dsn' => 'mysql:host=localhost;dbname=wordpress-tests',
                        'user' => 'root',
                        'password' => 'root',
                        'dump' => 'tests/_data/dump.sql',
                        'populate' => true,
                        'cleanup' => true,
                        'url' => 'http://wp.local',
                        'tablePrefix' => 'wp_',
                        'checkExistence' => true,
                        'update' => true
                    ],
                    'WPLoader' => [
                        'wpRootFolder' => '~/www/wordpress',
                        'dbName' => 'wordpress-tests',
                        'dbHost' => 'localhost',
                        'dbUser' => 'root',
                        'dbPassword' => 'root',
                        'wpDebug' => true,
                        'dbCharset' => 'utf8',
                        'dbCollate' => '',
                        'tablePrefix' => 'wp_',
                        'domain' => 'wp.local',
                        'adminEmail' => 'admin@wp.local',
                        'title' => 'WP Tests',
                        'phpBinary' => 'php',
                        'language' => ''
                    ],
                    'WPWebDriver' => [
                        'url' => 'http://wp.local',
                        'browser' => 'phantomjs',
                        'port' => 4444,
                        'restart' => true,
                        'wait' => 2,
                        'adminUsername' => 'admin',
                        'adminPassword' => 'admin',
                        'adminUrl' => '/wp-admin'
                    ]
                ]
            ]
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace) {
            $str = "namespace: {$this->namespace}\n" . $str;
        }
        file_put_contents('codeception.yml', $str);
    }

    protected function createFunctionalSuite($actor = 'Functional')
    {
        $suiteConfig = $this->getFunctionalSuiteConfig($actor);

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for WordPress functional tests.\n";
        $str .= "# Emulate web requests and make application process them.\n";
        $str .= Yaml::dump($suiteConfig, 2);
        $this->createSuite('functional', $actor, $str);
    }

    protected function createAcceptanceSuite($actor = 'Acceptance')
    {
        $suiteConfig = $this->getAcceptanceSuiteConfig($actor);

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for WordPress acceptance tests.\n";
        $str .= "# perform tests in browser using WPBrowser or WPWebDriver modules.\n";

        $str .= Yaml::dump($suiteConfig, 5);
        $this->createSuite('acceptance', $actor, $str);
    }

    /**
     * @param $actor
     *
     * @return array
     */
    protected function getFunctionalSuiteConfig($actor)
    {
        $suiteConfig = array(
            'class_name' => $actor . $this->actorSuffix,
            'modules' => array(
                'enabled' => array(
                    'Filesystem',
                    'WPDb',
                    'WPLoader',
                    "\\{$this->namespace}Helper\\$actor"
                )
            )
        );

        return $suiteConfig;
    }

    /**
     * @param $actor
     *
     * @return array
     */
    protected function getAcceptanceSuiteConfig($actor)
    {
        $suiteConfig = array(
            'class_name' => $actor . $this->actorSuffix,
            'modules' => array(
                'enabled' => array('WPBrowser', 'WPDb', "\\{$this->namespace}Helper\\$actor"),
            )
        );

        return $suiteConfig;
    }
}
