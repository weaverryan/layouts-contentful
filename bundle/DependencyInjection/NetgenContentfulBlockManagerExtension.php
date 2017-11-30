<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

final class NetgenContentfulBlockManagerExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services/controllers.yml');
        $loader->load('services/block_definitions.yml');
        $loader->load('services/items.yml');
        $loader->load('services/layout_resolver.yml');
        $loader->load('services/services.yml');
        $loader->load('services/templating.yml');
        $loader->load('services/query_types.yml');
        $loader->load('browser/services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $prependConfigs = array(
            'block_definitions.yml' => 'netgen_block_manager',
            'value_types.yml' => 'netgen_block_manager',
            'query_types.yml' => 'netgen_block_manager',
            'view/item_view.yml' => 'netgen_block_manager',
            'view/rule_target_view.yml' => 'netgen_block_manager',
            'view/rule_condition_view.yml' => 'netgen_block_manager',
            'view/block_view.yml' => 'netgen_block_manager',
            'browser/item_types.yml' => 'netgen_content_browser',
        );

        foreach ($prependConfigs as $configFile => $prependConfig) {
            $configFile = __DIR__ . '/../Resources/config/' . $configFile;
            $config = Yaml::parse(file_get_contents($configFile));
            $container->prependExtensionConfig($prependConfig, $config);
            $container->addResource(new FileResource($configFile));
        }
    }
}
