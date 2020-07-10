<?php
/**
* Provides the blocker class
*/

/**
* Manages individual blockers
*
*/
class blocker
{



    /**
     * Creates a new blocker
     */
    public function newBlocker($blockerName)
    {
        // Check the user is logged in
        $user = new user();
        if(!$user->isGoogleAuthed())
        {
            return false;
        }
        
        $userid = $user->getUserId();

        // Add the new blocker to the database
        $db = newMysqliObject();

        $stmtInsertNewName = $db->prepare("INSERT INTO blocker_definitions (`ownerid`,`name`) VALUES (?,?)");
        $stmtInsertNewName->bind_param("ss",$userid,$blockerName);
        $stmtInsertNewName->execute();




    }

    /**
     * Fetches an array of the users blockers
     */
    public function getBlockers()
    {
       
        $returnArray=array();
        // Check the user is logged in
        $user = new user();
        if(!$user->isGoogleAuthed())
        {
            return false;
        }
        
        $userid = $user->getUserId();


        // Add the new blocker to the database
        $db = newMysqliObject();
        $stmtFetchBlockers = $db->prepare("SELECT `id`,`name` FROM " . dbPrefix . "blocker_definitions WHERE `ownerid` = ?");
        $stmtFetchBlockers->bind_param("i", $userid);
        $stmtFetchBlockers->execute();
        $stmtFetchBlockers->bind_result($id, $name);
        while ($stmtFetchBlockers->fetch()) {
            $returnArray[] = array('id' => $id, 'name' => $name);
        }
        return $returnArray;
    }


    /**
     * Checks that the current user owns this blocker
     */
    public function userOwnsBlocker($blockerid)
    {
        $db = newMysqliObject();
        $user = new user();
        $userid = $user->getUserId();

        $stmtCheckBlockerOwner = $db->prepare("SELECT `id` FROM blocker_definitions WHERE `ownerid` = ? AND `id` = ?");
        $stmtCheckBlockerOwner->bind_param("ii", $userid, $blockerid);
        $stmtCheckBlockerOwner->execute();
        $stmtCheckBlockerOwner->store_result();

        // Test if we got a result
        ($stmtCheckBlockerOwner->num_rows == '1') ? $result = true :  $result = false;
        return $result;
    }

    /**
     * Attaches this blocker to a task
     */
    public function attachToTask($blockerid, $taskid)
    {
        // Check this user owns this blocker
        if(!$this->userOwnsBlocker($blockerid)) { return false; }

        

        // User owns blocker, map to the task
        $db = newMysqliObject();
        $user = new user();
        $userid = $user->getUserId();
        $stmtInsertBlockerMapping = $db->prepare("INSERT INTO task_blocker_map (`taskid`, `blocker_definition_id`) VALUES (?,?)");
        $stmtInsertBlockerMapping->bind_param("ii", $taskid, $blockerid);
        $stmtInsertBlockerMapping->execute();
    }

    /**
     * Removes the blocker from a task
     */
    public function removeFromTask($blockerid, $taskid)
    {
        // Check this user owns this blocker
        if(!$this->userOwnsBlocker($blockerid)) { return false; }

        // User owns blocker, remove the task
        $db = newMysqliObject();
        $user = new user();
        $userid = $user->getUserId();
        $stmtDeleteBlockerMapping = $db->prepare("DELETE FROM task_blocker_map WHERE `taskid` = ? AND `blocker_definition_id` = ?");
        $stmtDeleteBlockerMapping->bind_param("ii", $taskid, $blockerid);
        $stmtDeleteBlockerMapping->execute();
    }

    /**
     * Renames the blocker
     */
    public function renameBlocker($id, $name)
    {
        $db = newMysqliObject();

        $stmtUpdateBlockerName = $db->prepare("UPDATE blocker_definitions SET `name` = ? WHERE id = ?");
        $stmtUpdateBlockerName->bind_param("ss",$name,$id);
        $stmtUpdateBlockerName->execute();
    }

    /**
     * Renames the blocker
     */
    public function deleteBlocker($blockerid)
    {
        // Check the user owns this blocker
        if(!$this->userOwnsBlocker($blockerid)) { return false; }

        $db = newMysqliObject();

        // Delete the blocker defenition
        $stmtDeleteBlocker = $db->prepare("DELETE FROM blocker_definitions WHERE id = ?");
        $stmtDeleteBlocker->bind_param("s",$blockerid);
        $stmtDeleteBlocker->execute();

        // Delete any mappings of the blocker
        $stmtDeleteBlockerMaps = $db->prepare("DELETE FROM task_blocker_map WHERE blocker_definition_id = ?");
        $stmtDeleteBlockerMaps->bind_param("s",$blockerid);
        $stmtDeleteBlockerMaps->execute();

    }

   


}