<?php

namespace Kanboard\Plugin\Gantt\Controller;

use Kanboard\Plugin\Gantt\Plugin;
use Kanboard\Filter\ProjectTypeFilter;
use Kanboard\Filter\ProjectStatusFilter;
use Kanboard\Filter\ProjectIdsFilter;
use Kanboard\Model\ProjectModel;

/**
 * Projects Gantt Controller
 *
 * @package Kanboard\Controller
 * @author  Frederic Guillot
 * @property \Kanboard\Plugin\Gantt\Formatter\ProjectGanttFormatter $projectGanttFormatter
 */
class ProjectGanttController extends \Kanboard\Controller\BaseController
{
    /**
     * Show Gantt chart for all projects
     */
    public function show()
    {
        $project_ids = $this->projectPermissionModel->getActiveProjectIds($this->userSession->getId());
        $filter = $this->projectQuery
            ->withFilter(new ProjectTypeFilter(ProjectModel::TYPE_TEAM))
            ->withFilter(new ProjectStatusFilter(ProjectModel::ACTIVE))
            ->withFilter(new ProjectIdsFilter($project_ids));

        $filter->getQuery()->asc(ProjectModel::TABLE . '.start_date');

        $this->response->html($this->helper->layout->app(Plugin::$name . ':project_gantt/show', array(
            'projects' => $filter->format($this->projectGanttFormatter),
            'title' => t('Gantt chart for all projects'),
        )));
    }

    /**
     * Save new project start date and end date
     */
    public function save()
    {
        $values = $this->request->getJson();

        $result = $this->projectModel->update(array(
            'id' => $values['id'],
            'start_date' => strtotime($values['date_started']),
            'end_date' => strtotime($values['date_due']),
        ));

        if (!$result) {
            $this->response->json(array('message' => 'Unable to save project'), 400);
        } else {
            $this->response->json(array('message' => 'OK'), 201);
        }
    }
}
