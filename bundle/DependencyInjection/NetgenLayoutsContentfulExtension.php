<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsContentfulBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

final class NetgenLayoutsContentfulExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('netgen_layouts.contentful.entry_slug_type', $config['entry_slug_type']);

        $locator = new FileLocator(__DIR__ . '/../Resources/config');

        $loader = new DelegatingLoader(
            new LoaderResolver(
                [
                    new GlobFileLoader($container, $locator),
                    new YamlFileLoader($container, $locator),
                ]
            )
        );

        $loader->load('services/**/*.yml', 'glob');
        $loader->load('browser/services.yml');
        $loader->load('default_settings.yml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $prependConfigs = [
            'block_definitions.yml' => 'netgen_layouts',
            'value_types.yml' => 'netgen_layouts',
            'query_types.yml' => 'netgen_layouts',
            'view/item_view.yml' => 'netgen_layouts',
            'view/rule_target_view.yml' => 'netgen_layouts',
            'view/rule_condition_view.yml' => 'netgen_layouts',
            'view/block_view.yml' => 'netgen_layouts',
            'browser/item_types.yml' => 'netgen_content_browser',
            'framework/doctrine.yml' => 'doctrine',
        ];

        foreach ($prependConfigs as $configFile => $prependConfig) {
            $configFile = __DIR__ . '/../Resources/config/' . $configFile;
            $config = Yaml::parse((string) file_get_contents($configFile));
            $container->prependExtensionConfig($prependConfig, $config);
            $container->addResource(new FileResource($configFile));
        }
    }

    /**
     * @return \Symfony\Component\Config\Definition\ConfigurationInterface
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this);
    }
}
