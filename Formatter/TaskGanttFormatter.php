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
            $taskFormated =  $this->formatTask($task);

            // Subtasks processed first as it mutates $taskFormated['dependencies']
            foreach ($this->subtaskModel->getAll($task['id']) as $subTask) {
                $subTaskFormatted = $this->formatSubTask($subTask, $taskFormated, $task);

                if (!in_array($subTaskFormatted['id'], self::$ids)) {
                    $bars[] = $subTaskFormatted;
                    self::$ids[] = $subTaskFormatted['id'];
                }
            }

            if (!in_array($taskFormated['id'], self::$ids)) {
                $bars[] = $taskFormated;
                self::$ids[] = $taskFormated['id'];
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
            'id' => "task-{$task['id']}",
            'project_id' => $task['project_id'],
            'title' => $task['title'],
            'start' => array(
                (int) date('Y', $start),
                (int) date('n', $start),
                (int) date('j', $start),
            ),
            'end' => array(
                (int) date('Y', $end),
                (int) date('n', $end),
                (int) date('j', $end),
            ),
            'dependencies' => $this->getLinksId($task['id']),
            'column_title' => $task['column_name'],
            'assignee' => $task['assignee_name'] ?: $task['assignee_username'],
            'progress' => $this->taskModel->getProgress($task, $this->columns[$task['project_id']]),
            'link' => $this->helper->url->href('TaskViewController', 'show', array('task_id' => $task['id'])),
            'color' => $this->colorModel->getColorProperties($task['color_id']),
            'not_defined' => empty($task['date_due']) || empty($task['date_started']),
            'date_started_not_defined' => empty($task['date_started']),
            'date_due_not_defined' => empty($task['date_due']),
        );
    }

    private function formatSubTask(array $subTask, array &$taskFormated, array $task)
    {
        $taskFormated['dependencies'][] = "subtask-{$subTask['id']}";
        $start = $taskFormated['start'];
        $end = $taskFormated['end'];

        if (!empty($subTask['due_date']) && $subTask['due_date'] != 0) {
            $end = array(
                (int) date('Y', $subTask['due_date']),
                (int) date('n', $subTask['due_date']),
                (int) date('j', $subTask['due_date']),
                );

            $start = array(
                (int) date('Y', $subTask['due_date']),
                (int) date('n', $subTask['due_date']),
                (int) date('j', $subTask['due_date']) - 3,
                );
        }

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
            'id' => "subtask-{$subTask['id']}",
            'task' => $task,
            'title' => $subTask['title'],
            'start' => $start,
            'end' => $end,
            'dependencies' => [],
            'column_title' => $taskFormated['column_title'],
            'assignee' => $taskFormated['assignee'],
            'progress' => $progress,
            'link' => $taskFormated['link'],
            'color' => $taskFormated['color'],
            'not_defined' => $taskFormated['not_defined'],
            'date_started_not_defined' => $taskFormated['date_started_not_defined'],
            'date_due_not_defined' => $taskFormated['date_due_not_defined'],
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
