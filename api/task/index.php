<?php

require '../../config.php';

/**
 * Handles task related requests
 */

 // Process the requested task action
if(isset($_GET['action']))
{
    // Create a new task
    // action = newtask
    // taskname = <name of task>
    if($_GET['action'] == 'newtask')
    {
        $task = new task();
        if($task->newTask($_GET['taskname']))
        {
            echo "Task " . $_GET['taskname'] . " created";
        }
        else
        {
            echo "There was an error creating this task";
        }
        
    }

    // Fetch a list of existing tasks for the logged in user
    if($_GET['action'] == 'getTaskIds')
    {
        $task = new task();
        $result = $task->listTasks('30', '1', 'id', 'DESC');
        $jsonEncodedResult = json_encode($result);
        print_r($jsonEncodedResult);
    }

     // Fetch a list of existing tasks for the logged in user
     if($_GET['action'] == 'taskInfo')
     {
         $task = new task();
         $task->setid($_GET['taskid']);
         $result['name'] = $task->name();
         $result['blockers'] = $task->getTaskBlockers();
         $result['description'] = $task->getTaskDescription();
         $result['notes'] = $task->getTaskNotes();

         print_r(json_encode($result));
     }



     // Change the name of a task
     if($_GET['action'] == 'newTaskName')
     {
        $task = new task();
        $task->setid($_GET['taskid']);
        if(!$task->changeName($_GET['newName']))
        {
            echo json_encode("error");
        }
        else
        {
            echo json_encode($_GET['newName']);
           
        }        
     }


     // Change the name of a task
     if($_GET['action'] == 'newTask')
     {
        $task = new task();
        $createTask = $task->newTask($_GET['newTaskName']);
        if($createTask['status'] == false)
        {
            echo json_encode($createTask['result']);
        }
        else
        {
            echo json_encode($createTask);
        }
     }

     // Mark a task as complete
     if($_GET['action'] == 'complete')
     {
        $task = new task();
        $task->setid($_GET['id']);
        $task->markComplete();
     }

     // Update the notes entry for the task
     if($_GET['action'] == 'updateNote')
     {
        $task = new task();
        $task->setid($_GET['taskid']);
        $result['status'] = $task->updateNote($_GET['newNote']);
        echo json_encode($result);
     }
     
     // Update the notes entry for the task
     if($_GET['action'] == 'updateDescription')
     {
        $task = new task();
        $task->setid($_GET['taskid']);
        $result['status'] = $task->updateDescription($_GET['newDescription']);
        echo json_encode($result);
     }
     

}
else
{
    echo "No action was requested";
}

 ?>