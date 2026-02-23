<?php

namespace Kanboard\Plugin\ColorByPriority\Subscriber;

use Kanboard\Event\GenericEvent;
use Kanboard\Model\TaskModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * PriorityColorSubscriber
 * * Automatically updates task colors based on their priority level 
 * * whenever a task is created or updated.
 */
class PriorityColorSubscriber implements EventSubscriberInterface
{
    /**
     * Map defining which color corresponds to each priority level.
     */
    const PRIORITY_MAP = [
        1 => 'yellow',
        2 => 'orange',
        3 => 'red',
        0 => 'green',
    ];
    
    /**
     * Default color for any priority level 4 or higher.
     */
    const HIGH_PRIORITY_COLOR = 'red'; 

    /**
     * Bind the subscriber to specific Kanboard task events.
     * * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskModel::EVENT_CREATE => 'updateColor',
            TaskModel::EVENT_UPDATE => 'updateColor',
        ];
    }

    /**
     * Logic to evaluate and apply the correct color to a task.
     * * @param GenericEvent $event
     */
    public function updateColor($event)
    {
        // 1. Extract the task ID from the event payload.
        $taskId = isset($event['task_id']) ? $event['task_id'] : (isset($event['id']) ? $event['id'] : null);

        if (empty($taskId)) {
            return;
        }

        // 2. Fetch fresh task data from the database to ensure accuracy.
        global $container;
        $task = $container['taskFinderModel']->getById($taskId);

        if (empty($task)) {
            return;
        }

        // 3. Compare current task priority against the defined color map.
        $priority = (int) $task['priority'];
        $currentColor = $task['color_id'];
        $targetColor = '';

        if (isset(self::PRIORITY_MAP[$priority])) {
            $targetColor = self::PRIORITY_MAP[$priority];
        } elseif ($priority >= 4) {
            $targetColor = self::HIGH_PRIORITY_COLOR;
        } else {
            // Exit if the priority level is not handled by this subscriber.
            return; 
        }

        // 4. Prevent redundant updates if the task already has the correct color.
        if ($targetColor === $currentColor) {
            return;
        }

        // 5. Execute the update while preventing an infinite event loop.
        // Temporarily detach the subscriber before performing the update.
        $container['dispatcher']->removeSubscriber($this);
        
        $container['taskModificationModel']->update([
            'id' => $taskId,
            'color_id' => $targetColor
        ], false);
        
        // Re-attach the subscriber to listen for subsequent events.
        $container['dispatcher']->addSubscriber($this);
    }
}