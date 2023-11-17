<li <?= $this->app->checkMenuSelection('ConfigController', 'show', \Kanboard\Plugin\Gantt\Plugin::$name) ?>>
    <?= $this->url->link(
        t('Gantt settings'),
        'ConfigController',
        'show',
        array('plugin' => \Kanboard\Plugin\Gantt\Plugin::$name)
    ) ?>
</li>
