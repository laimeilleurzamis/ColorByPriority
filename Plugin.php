<?php

namespace Kanboard\Plugin\ColorByPriority;

use Kanboard\Core\Plugin\Base;
use Kanboard\Plugin\ColorByPriority\Subscriber\PriorityColorSubscriber;

class Plugin extends Base
{
    public function initialize()
    {
        $this->container['priorityColorSubscriber'] = function ($container) {
            return new PriorityColorSubscriber($container);
        };

        $this->dispatcher->addSubscriber($this->container['priorityColorSubscriber']);
    }

    public function getPluginName()
    {
        return 'ColorByPriority';
    }

    public function getPluginDescription()
    {
        return 'Assign automatically a color to tasks based on their priority level, enhancing visual management of tasks.';
    }

    public function getPluginAuthor()
    {
        return 'laimeilleurzamis';
    }

    public function getPluginVersion()
    {
        return '1.1.0';
    }

    public function getPluginHomepage()
    {
        return '';
    }
}