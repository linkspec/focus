<?php
require '../../config.php';

/**
 * Handles blocker related requests
 */

if(isset($_GET['action']))
{
    // Create a new task
    // action = newtask
    // taskname = <name of task>
    switch($_GET['action'])
    {
        case 'newBlocker':
            $blocker = new blocker();
            $blocker->newBlocker($_GET['newBlockerName']);
            $jsonEncodedResult = json_encode(array('error' => false));
            print_r($jsonEncodedResult);
        break;

        case 'blockerList':
            $blocker = new blocker();
            $jsonEncodedResult = json_encode($blocker->getBlockers());
            print_r($jsonEncodedResult);
        break;

        // Attach a blocker to a task
        case 'addBlockerToTask':
            // Check the blocker belongs to the user
            $blocker = new blocker();
            $blocker->attachToTask($_GET['blockerid'], $_GET['taskid']);
        break;

        // Remove a blocker from a task
        case 'removeBlockerFromTask':
            // Check the blocker belongs to the user
            $blocker = new blocker();
            $blocker->removeFromTask($_GET['blockerid'], $_GET['taskid']);
        break;
        // Renames a blocker
        case 'blockerRename':
            // Check the blocker belongs to the user
            $blocker = new blocker();
            $blocker->renameBlocker($_GET['id'], $_GET['name']);
            $jsonEncodedResult = json_encode(array('udpated' => true));
            print_r($jsonEncodedResult);
        break;
        
        // Delete a blocker
        case 'blockerDelete':
            // Check the blocker belongs to the user
            $blocker = new blocker();
            $blocker->deleteBlocker($_GET['id']);
            $jsonEncodedResult = json_encode(array('udpated' => true));
            print_r($jsonEncodedResult);
        break;


    }
}


?>