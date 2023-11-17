<?php

namespace Kanboard\Plugin\Gantt\Formatter;

use Kanboard\Formatter\BaseFormatter;
use Kanboard\Core\Filter\FormatterInterface;

/**
 * Gantt chart formatter for projects
 *
 * @package  formatter
 * @author   Frederic Guillot
 */
class ProjectGanttFormatter extends BaseFormatter implements FormatterInterface
{
    /**
     * Format projects to be displayed in the Gantt chart
     *
     * @access public
     * @return array
     */
    public function format()
    {
        $projects = $this->query->findAll();
        $colors = $this->colorModel->getDefaultColors();
        $bars = array();

        foreach ($projects as $project) {
            $start = empty($project['start_date']) ? time() : strtotime($project['start_date']);
            $end = empty($project['end_date']) ? $start : strtotime($project['end_date']);
            $color = next($colors) ?: reset($colors);

            $bars[] = array(
                'type' => 'project',
                'id' => $project['id'],
                'name' => $project['name'],
                'start' => date('Y-n-j', $start),
                'end' => date('Y-n-j', $end),
                'custom_class' => strtolower("color-{$color['name']}"),
            );
        }

        return $bars;
    }
}
