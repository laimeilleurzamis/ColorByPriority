Color By Priority
This Kanboard plugin automatically assigns colors to tasks based on their priority level, enhancing visual management of the board.

Features
Automatic Coloring: Task colors are automatically updated when a task is created or modified.

Color Code: The plugin applies the following rules defined in the code:

Priority 0: Green

Priority 1: Blue

Priority 2: Orange

Priority 3 and above: Red

Installation
Download the plugin archive or clone this repository.

Create a folder named ColorByPriority in the plugins/ directory of your Kanboard installation.

Transfer the files into this folder.

Technical Information
The plugin uses an event subscriber (PriorityColorSubscriber) that listens to TaskModel::EVENT_CREATE and TaskModel::EVENT_UPDATE events.

It includes a safety mechanism to prevent infinite loops (recursion) when updating the task: the subscriber temporarily removes itself before applying the color change via taskModificationModel, and then re-adds itself.

Author
Author: laimeilleurzamis

Version: 1.1.0
