<div class="page-header">
    <h2><?= t('Gantt settings') ?></h2>
</div>

<form method="post" autocomplete="off" action="<?= $this->url->href('ConfigController', 'save', array('plugin' => \Kanboard\Plugin\Gantt\Plugin::$name)) ?>">

    <?= $this->form->csrf() ?>

    <fieldset>
        <legend><?= t('Gantt chart') ?></legend>
        <?= $this->form->radios(
            'gantt_task_sort',
            array(
                'board' => t('Sort tasks by position'),
                'date' => t('Sort tasks by date'),
            ),
            $values
        ) ?>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?= t('Save') ?></button>
    </div>
</form>
