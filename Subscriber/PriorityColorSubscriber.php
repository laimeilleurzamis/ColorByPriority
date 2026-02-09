<?php

namespace Kanboard\Plugin\ColorByPriority\Subscriber;

use Kanboard\Event\GenericEvent;
use Kanboard\Model\TaskModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PriorityColorSubscriber implements EventSubscriberInterface
{
    const PRIORITY_MAP = [
        1 => 'blue',
        2 => 'orange',
        3 => 'red',
        0 => 'green',
    ];
    
    const HIGH_PRIORITY_COLOR = 'red'; 

    public static function getSubscribedEvents()
    {
        return [
            TaskModel::EVENT_CREATE => 'updateColor',
            TaskModel::EVENT_UPDATE => 'updateColor',
        ];
    }

    public function updateColor($event)
    {
        // 1. Récupération de l'ID de la tâche de manière sécurisée
        $taskId = isset($event['task_id']) ? $event['task_id'] : (isset($event['id']) ? $event['id'] : null);

        if (empty($taskId)) {
            return;
        }

        // 2. On récupère la tâche fraîche depuis la base de données
        // Cela garantit qu'on a bien la priorité ACTUELLE et la couleur ACTUELLE
        global $container;
        $task = $container['taskFinderModel']->getById($taskId);

        if (empty($task)) {
            return;
        }

        // 3. Détermination de la couleur cible selon la priorité de la tâche
        $priority = (int) $task['priority'];
        $currentColor = $task['color_id'];
        $targetColor = '';

        if (isset(self::PRIORITY_MAP[$priority])) {
            $targetColor = self::PRIORITY_MAP[$priority];
        } elseif ($priority >= 4) {
            $targetColor = self::HIGH_PRIORITY_COLOR;
        } else {
            // Si la priorité n'est pas gérée, on ne touche à rien
            return; 
        }

        // 4. Si la couleur est déjà la bonne, on arrête (évite la boucle infinie)
        if ($targetColor === $currentColor) {
            return;
        }

        // 5. Mise à jour forcée
        // On désactive temporairement l'écouteur pour ne pas se rappeler soi-même
        $container['dispatcher']->removeSubscriber($this);
        
        $container['taskModificationModel']->update([
            'id' => $taskId,
            'color_id' => $targetColor
        ], false); // false = pas d'événement, mais on a quand même désactivé le subscriber par sécurité
        
        // On réactive l'écouteur pour les prochaines fois
        $container['dispatcher']->addSubscriber($this);
    }
}