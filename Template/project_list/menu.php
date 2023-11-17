<?php if ($this->user->hasAccess('ProjectGanttController', 'show')) : ?>
    <li>
        <?= $this->url->icon(
            'sliders',
            t('Projects Gantt chart'),
            'ProjectGanttController',
            'show',
            array('plugin' => \Kanboard\Plugin\Gantt\Plugin::$name)
        ) ?>
    </li>
<?php endif ?>
