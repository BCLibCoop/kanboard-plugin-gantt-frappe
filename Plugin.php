<?php

namespace Kanboard\Plugin\Gantt;

use Kanboard\Core\Security\Role;
use Kanboard\Core\Translator;

class Plugin extends \Kanboard\Core\Plugin\Base
{
    public static $name = 'Gantt';

    public function initialize()
    {
        $this->route->addRoute('gantt/:project_id', 'TaskGanttController', 'show', self::$name);
        $this->route->addRoute('gantt/:project_id/sort/:sorting', 'TaskGanttController', 'show', self::$name);
        $this->route->addRoute('gantt/:project_id/search/:search', 'TaskGanttController', 'show', self::$name);
        $this->route->addRoute('projects/gantt', 'ProjectGanttController', 'show', self::$name);

        $this->projectAccessMap->add('ProjectGanttController', 'save', Role::PROJECT_MANAGER);
        $this->projectAccessMap->add('TaskGanttController', 'save', Role::PROJECT_MEMBER);

        $this->template->hook->attach('template:project-header:view-switcher', self::$name . ':project_header/views');
        $this->template->hook->attach('template:project:dropdown', self::$name . ':project/dropdown');
        $this->template->hook->attach('template:project-list:menu:after', self::$name . ':project_list/menu');
        $this->template->hook->attach('template:config:sidebar', self::$name . ':config/sidebar');

        $this->hook->on('template:layout:js', array('template' => $this->assetPath('frappe-gantt.js')));
        $this->hook->on('template:layout:css', array('template' => $this->assetPath('frappe-gantt.css')));

        $this->hook->on('template:layout:js', array('template' => $this->assetPath('gantt.js')));
        $this->hook->on('template:layout:css', array('template' => $this->assetPath('gantt.css')));

        $this->container['projectGanttFormatter'] = $this->container->factory(function ($c) {
            return new Formatter\ProjectGanttFormatter($c);
        });

        $this->container['taskGanttFormatter'] = $this->container->factory(function ($c) {
            return new Formatter\TaskGanttFormatter($c);
        });
    }

    private function assetPath($asset_filename)
    {
        return implode(
            DIRECTORY_SEPARATOR,
            array(
                basename(PLUGINS_DIR),
                self::$name,
                'Assets',
                $asset_filename
            )
        );
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__ . '/Locale');
    }

    public function getPluginName()
    {
        return self::$name;
    }

    public function getPluginDescription()
    {
        return t('Gantt Frappe for Kanboard');
    }

    public function getPluginAuthor()
    {
        return 'Frédéric Guillot, Nassim Ourami, BC Libraries Coop';
    }

    public function getPluginVersion()
    {
        return '1.2.0';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/BCLibCoop/kanboard-plugin-gantt-frappe';
    }

    public function getCompatibleVersion()
    {
        return '>1.2.3';
    }
}
