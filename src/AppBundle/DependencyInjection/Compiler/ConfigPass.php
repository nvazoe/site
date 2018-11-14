<?php
namespace App\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container ) {

        $config = $container->getParameter('easyadmin.config');

        // use menu to use ROLE_ADMIN role by default if not set
        foreach($config['design']['menu'] as $k => $v) {
            if (!isset($v['role'])) {
                $config['design']['menu'][$k]['role'] = 'ROLE_ADMIN';
            }
        }

        // update entities to use ROLE_ADMIN role by default if not set
        foreach ($config['entities'] as $k => $v) {
            //echo '<pre>'; die(var_dump($v)); echo '</pre>';
            if (!isset($v['role'])) {
                $config['entities'][$k]['role'] = 'ROLE_ADMIN';
            }
        }

        // update views to use entities role by default if not set
        foreach ($config['entities'] as $k => $v) {
            $views = ['new', 'edit', 'show', 'list', 'delete'];
            foreach ($views as $view) {
                if (!isset($v[$view]['role'])) {
                    $config['entities'][$k][$view]['role'] = $v['role'];
                }
            }
        }

        $container->setParameter('easyadmin.config', $config);
    }
}