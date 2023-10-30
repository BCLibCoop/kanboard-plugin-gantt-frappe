<section id="main">
    <div class="page-header">
        <ul>
            <?php if ($this->user->hasAccess('ProjectCreationController', 'create')) : ?>
                <li>
                    <?= $this->modal->medium('plus', t('New project'), 'ProjectCreationController', 'create') ?>
                </li>
            <?php endif ?>
            <?php if ($this->app->config('disable_private_project', 0) == 0) : ?>
                <li>
                    <?= $this->modal->medium('lock', t('New private project'), 'ProjectCreationController', 'createPrivate') ?>
                </li>
            <?php endif ?>
            <li>
                <?= $this->url->icon('folder', t('Projects list'), 'ProjectListController', 'show') ?>
            </li>
            <?php if ($this->user->hasAccess('ProjectUserOverviewController', 'managers')) : ?>
                <li>
                    <?= $this->url->icon('user', t('Users overview'), 'ProjectUserOverviewController', 'managers') ?>
                </li>
            <?php endif ?>
        </ul>
    </div>
    <section>
        <?php if (empty($projects)) : ?>
            <p class="alert"><?= t('No project') ?></p>
        <?php else : ?>
            <svg id="gantt-chart"></svg>
            <script>
                var ganttSaveController = <?= json_encode($this->url->to('TaskGanttController', 'save', array('project_id' => $project['id'], 'plugin' => 'Gantt'))) ?>;
                var ganttTasks = <?= json_encode($projects) ?>;
            </script>
        <?php endif ?>
    </section>
</section>
