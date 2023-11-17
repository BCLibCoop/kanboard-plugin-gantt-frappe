<?php if ($this->user->hasProjectAccess('TaskGanttController', 'show', $project['id'])) : ?>
    <li <?= $this->app->checkMenuSelection('TaskGanttController') ?>>
        <?= $this->url->icon(
            'sliders',
            t('Gantt'),
            'TaskGanttController',
            'show',
            array(
                'project_id' => $project['id'],
                'search' => $filters['search'],
                'plugin' => \Kanboard\Plugin\Gantt\Plugin::$name,
            ),
            false,
            'view-gantt',
            t('Keyboard shortcut: "%s"', 'v g')
        ) ?>
    </li>
<?php endif ?>
