<?php if ($this->user->hasProjectAccess('TaskGanttController', 'show', $project['id'])) : ?>
    <li>
        <?= $this->url->icon(
            'sliders',
            t('Gantt'),
            'TaskGanttController',
            'show',
            array(
                'project_id' => $project['id'],
                'plugin' => \Kanboard\Plugin\Gantt\Plugin::$name,
            )
        ) ?>
    </li>
<?php endif ?>
