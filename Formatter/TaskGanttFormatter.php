<?php

namespace Kanboard\Plugin\Gantt\Formatter;

use Kanboard\Formatter\BaseFormatter;
use Kanboard\Core\Filter\FormatterInterface;
use Pimple\Container;

/**
 * Task Gantt Formatter
 *
 * @package formatter
 * @author  Frederic Guillot
 */
class TaskGanttFormatter extends BaseFormatter implements FormatterInterface
{
    /**
     * Local cache for project columns
     *
     * @access private
     * @var array
     */
    private $columns = array();

    private $links = [];

    private $status = [];

    private static $ids = [];

    /**
     * Constructor
     *
     * @access public
     * @param  \Pimple\Container   $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->status  = array_flip($this->subtaskModel->getStatusList());
    }

    /**
     * Apply formatter
     *
     * @access public
     * @return array
     */
    public function format()
    {
        $bars = array();

        foreach ($this->query->findAll() as $task) {
            $taskFormatted =  $this->formatTask($task);

            // Subtasks processed first as it mutates $taskFormatted['dependencies']
            foreach ($this->subtaskModel->getAll($task['id']) as $subTask) {
                $subTaskFormatted = $this->formatSubTask($subTask, $taskFormatted, $task);

                if (!in_array($subTaskFormatted['id'], self::$ids)) {
                    $bars[] = $subTaskFormatted;
                    self::$ids[] = $subTaskFormatted['id'];
                }
            }

            if (!in_array($taskFormatted['id'], self::$ids)) {
                $bars[] = $taskFormatted;
                self::$ids[] = $taskFormatted['id'];
            }
        }

        return $bars;
    }

    /**
     * Format a single task
     *
     * @access private
     * @param  array  $task
     * @return array
     */
    private function formatTask(array $task)
    {
        if (! isset($this->columns[$task['project_id']])) {
            $this->columns[$task['project_id']] = $this->columnModel->getList($task['project_id']);
        }

        $start = $task['date_started'] ?: time();
        $end = $task['date_due'] ?: $start;

        return array(
            'type' => 'task',
            'original_id' => $task['id'],
            'id' => "task-{$task['id']}",
            'project_id' => $task['project_id'],
            'name' => $task['title'],
            'start' => date('Y-n-j', $start),
            'end' => date('Y-n-j', $end),
            'dependencies' => $this->getLinksId($task['id']),
            // Never use `0` so we get a bit of the colour
            'progress' => $this->taskModel->getProgress($task, $this->columns[$task['project_id']]) ?: 1,
            'custom_class' => strtolower('color-' . $this->colorModel->getColorProperties($task['color_id'])['name']),
        );
    }

    private function formatSubTask(array $subTask, array &$taskFormatted, array $task)
    {
        $taskFormatted['dependencies'][] = "subtask-{$subTask['id']}";

        switch ($this->status[$subTask['status_name']]) {
            case 2:
                $progress = 100;
                break;
            case 1:
                $progress = 50;
                break;
            case 0:
            default:
                $progress = 1;
        }

        return array(
            'type' => 'subtask',
            'original_id' => $subTask['id'],
            'id' => "subtask-{$subTask['id']}",
            'task' => $task,
            'name' => $subTask['title'],
            'start' => $taskFormatted['start'],
            'end' => $taskFormatted['end'],
            'dependencies' => [],
            'progress' => $progress,
            'custom_class' => $taskFormatted['custom_class'],
        );
    }

    /**
     * @todo must be in TaskLinkModel
     *
     * @param integer $id
     *
     * @return array
     */
    private function getLinksId(int $id): array
    {
        $links = $this->taskLinkModel->getAll($id);

        $result = [];

        foreach ($links as $link) {
            $uiid  = "{$link['task_id']}:{$id}";
            $uiid2 = "{$id}:{$link['task_id']}";

            if (
                $link['task_id'] != $id
                && !in_array($uiid, $this->links)
                && !in_array($uiid2, $this->links)
            ) {
                $this->links[] = $uiid2;
                $this->links[] = $uiid;
                continue;
            }

            $result[] = "task-{$link['task_id']}";
        }

        return $result;
    }
}
