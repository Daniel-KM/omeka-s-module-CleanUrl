<?php
namespace CleanUrl;

/**
 * @var Module $this
 * @var \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Api\Manager $api
 */
$services = $serviceLocator;
$settings = $services->get('Omeka\Settings');
$config = @require dirname(dirname(__DIR__)) . '/config/module.config.php';
$connection = $services->get('Omeka\Connection');
$entityManager = $services->get('Omeka\EntityManager');
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');
$space = strtolower(__NAMESPACE__);

if (version_compare($oldVersion, '3.14', '<')) {
    $settings->set('clean_url_identifier_property',
        (int) $settings->get('clean_url_identifier_property'));

    $settings->set('clean_url_item_allowed',
        unserialize($settings->get('clean_url_item_allowed')));
    $settings->set('clean_url_media_allowed',
        unserialize($settings->get('clean_url_media_allowed')));

    $this->cacheItemSetsRegex($serviceLocator);
}

if (version_compare($oldVersion, '3.15.3', '<')) {
    foreach ($config[strtolower(__NAMESPACE__)]['config'] as $name => $value) {
        $oldName = str_replace('cleanurl_', 'clean_url_', $name);
        $settings->set($name, $settings->get($oldName, $value));
        $settings->delete($oldName);
    }
}

if (version_compare($oldVersion, '3.15.5', '<')) {
    $settings->set('cleanurl_use_admin',
        $config[strtolower(__NAMESPACE__)]['config']['cleanurl_use_admin']);
}

if (version_compare($oldVersion, '3.15.13', '<')) {
    $t = $services->get('MvcTranslator');
    $messenger = new \Omeka\Mvc\Controller\Plugin\Messenger;

    if (!$this->preInstallCopyConfigFiles()) {
        $message = $t->translate('Unable to copy config files "config/clean_url.config.php" and/or "config/clean_url.dynamic.php" in the config directory of Omeka.'); // @translate
        $messenger->addWarning($message);
        $logger = $services->get('Omeka\Logger');
        $logger->err('The file "clean_url.dynamic.php" and/or "config/clean_url.dynamic.php" in the config directory of Omeka is not writeable.'); // @translate
    }

    $messenger->addWarning($t->translate('Check the new config file "config/clean_url.config.php" and remove the old one in the config directory of Omeka.')); // @translate

    $settings->set('cleanurl_site_skip_main', false);
    $settings->set('cleanurl_site_slug', 's/');
    $settings->set('cleanurl_page_slug', 'page/');

    $settings->set('cleanurl_item_default', $settings->get('cleanurl_item_default') . '_item');
    $routes = [];
    foreach ($settings->get('cleanurl_item_allowed') as $route) {
        $routes[] = $route . '_item';
    }
    $settings->set('cleanurl_item_allowed', $routes);

    $settings->set('cleanurl_media_default', $settings->get('cleanurl_media_default') . '_media');
    $routes = [];
    foreach ($settings->get('cleanurl_media_allowed') as $route) {
        $routes[] = $route . '_media';
    }
    $settings->set('cleanurl_media_allowed', $routes);

    $this->cacheCleanData();
    $this->cacheItemSetsRegex();
}
