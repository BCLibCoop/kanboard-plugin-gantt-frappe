<section id="main">
    <?= $this->projectHeader->render($project, 'TaskGanttController', 'show', false, 'Gantt') ?>
    <div class="plugin-header">
        <div class="menu-inline">
            <ul>
                <li <?= $sorting === 'board' ? 'class="active"' : '' ?>>
                    <?= $this->url->icon('sort-numeric-asc', t('Sort by position'), 'TaskGanttController', 'show', array('project_id' => $project['id'], 'sorting' => 'board', 'plugin' => 'Gantt')) ?>
                </li>
                <li <?= $sorting === 'date' ? 'class="active"' : '' ?>>
                    <?= $this->url->icon('sort-amount-asc', t('Sort by date'), 'TaskGanttController', 'show', array('project_id' => $project['id'], 'sorting' => 'date', 'plugin' => 'Gantt')) ?>
                </li>
                <li>
                    <?= $this->modal->large('plus', t('Add task'), 'TaskCreationController', 'show', array('project_id' => $project['id'])) ?>
                </li>
            </ul>
        </div>
        <div class="views-switcher-component menu-inline">
            <ul class="views">
                <li class="active">
                    <a href="#">Day</a>
                </li>
                <li>
                    <a href="#">Week</a>
                </li>
                <li>
                    <a href="#">Month</a>
                </li>
                <li>
                    <a href="#">Quarter</a>
                </li>
                <li>
                    <a href="#">Year</a>
                </li>
            </ul>
        </div>
    </div>

    <?php if (!empty($tasks)) : ?>
        <?php foreach ($tasks as $task) : ?>
            <div id="dropdown-task-id-<?= $task['id'] ?>" style="display: none;">
                <?php $task['id'] = $task['original_id']; ?>
                <?php if ($task['type'] === 'task') : ?>
                    <?= $this->render('task/dropdown', array('task' => $task, 'redirect' => 'board')) ?>
                <?php elseif ($task['type'] === 'subtask') : ?>
                    <?= $this->render('subtask/menu', array('task' => $task['task'], 'subtask' => $task)) ?>
                <?php endif ?>
            </div>
        <?php endforeach ?>
        <p class="alert alert-info"><?= t('Moving or resizing a task will change the start and due date of the task.') ?></p>
        <svg id="gantt-chart"></svg>
        <script>
            var ganttSaveController = <?= json_encode($this->url->to('TaskGanttController', 'save', array('project_id' => $project['id'], 'plugin' => 'Gantt'))) ?>;
            var ganttTasks = <?= json_encode($tasks) ?>;
        </script>
    <?php else : ?>
        <p class="alert"><?= t('There is no task in your project.') ?></p>
    <?php endif ?>
</section>
