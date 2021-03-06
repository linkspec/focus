<?php
/**
* Provides the task class
*/

/**
* Manages individual tasks
*
*/
class task
{
    /**
    * @var int $taskid SQL id of the task
    */
    private $taskid;
    /**
    * @var string $name User defined name for the task
    */
    private $name;
    /**
    * @var string $description User defined description for the task
    */
    private $description;
    /**
    * @var string $createdate Unix timestamp when the task was created
    */
    private $createdate;
    /**
    * @var string $lastupdatedate Unix timestamp when the task was last updated
    */
    private $lastupdatedate;
    /**
    * @var string $deadLine Unix timestamp when the task should be completed by
    */
    private $deadLine;
    /**
    * @var int $leadtime Time in seconds that will be required to complete this task (Any parent tasks should factor this in if set)
    */
    private $leadtime;
     /**
    * @var int $owner The google id that owns this task
    */
    private $owner;
    /**
    * @var int $priority Task priority: 1 = High, 2 = Normal, 3 = Low
    */
    private $priority;
    /**
    * @var int Status of the task
    * * 1 - New task
    * * 2 - Completed
    * * 3 - Deleted
    */
    private $status;

    /**
     * @var int $timeRequired The number of minutes required to complete this task
     */
    private $timeRequired;

    /**
    * Sets the task id for this object
    * @param int SQL id this task object represents
    * @return bool True if successul, false if unsuccessful
    */
    public function setid($id)
    {

        if (empty($this->taskid)) {
            
            $this->taskid = $id;

            // Fetch the tasks info from the database
            $this->fetchProperties();

            // Check this task is owned by this owner
            $user = new user();
            $googleAuth_id = $user->getGoogleAuthId();
            if(!$googleAuth_id == $this->owner){
                // Set the task id
                $this->taskid = $id;
                return true;
            }
            else
            {
                // User does not own this task
                return false;
            }

            
        } else {
            // Object already has a task
            return false;
        }
    }

    /**
    * Creates a new task entry in the database
    * @param string Name of the task
    * @return int|bool id of the task, or false if failed
    */
    public function newTask($name)
    {

        $returnArray = array();
        // Check the object does not already have a task associated with it
        if (empty($this->taskid)) {
            $db = newMysqliObject();
           
            // Capture the current time
            $currTime = time();

            // Fetch the current users google sub id
            $user = new user();
            
            $googleAuth_id = $user->getGoogleAuthId();
           
            if(!$googleAuth_id){
                // User not logged in, do not proceed
                $returnArray['status']=false;
                $returnArray['result']="User is not logged in";
                return $returnArray;
            }
            
            // Create the new task in the database
            $stmtCreateTask = $db->prepare("INSERT INTO " . dbPrefix . "tasks (`name`,`createdate`,`lastupdatedate`,`status`,`priority`,`owner`) VALUES (?,?,?,'1','2',?)");
            $stmtCreateTask->bind_param("ssss", $name, $currTime, $currTime, $googleAuth_id);
            $stmtCreateTask->execute();


            // Check we create a task
            if($stmtCreateTask->error)
            {
                $returnArray['status']=false;
                $returnArray['result']=$stmtCreateTask->error;
                return $returnArray;
            }

            // Set the object properties
            $this->setid($stmtCreateTask->insert_id);
            $this->createdate = $currTime;
            $this->lastupdatedate = $currTime;
            $this->status = '1';

            $returnArray['status']=true;
            $returnArray['result']=$stmtCreateTask->insert_id;
            return $returnArray;
           
        } else {
            $returnArray['status']=false;
                $returnArray['result']="No task ID set";
                return $returnArray;
        }
    }


    /**
    * Fetches the data for this object from the database
    * return bool Returns true on success, false on failure
    */
    private function fetchProperties()
    {
        $db = newMysqliObject();

        $stmtFetchProperties = $db->prepare("SELECT `name`,`description`,`notes`,`createdate`,`lastupdatedate`,`deadline`,`leadtime`,`status`,`priority`,`owner`,`timerequired` FROM " . dbPrefix . "tasks WHERE id = ? LIMIT 1");
        $stmtFetchProperties->bind_param("i", $this->taskid);
        $stmtFetchProperties->execute();
        $stmtFetchProperties->bind_result($name, $description, $notes, $createdate, $lastupdatedate, $deadline, $leadtime, $status, $priority, $owner, $time);
        while ($stmtFetchProperties->fetch()) {
            $this->name = $name;
            $this->description = $description;
            $this->notes = $notes;
            $this->createdate = $createdate;
            $this->lastupdatedate = $lastupdatedate;
            $this->deadLine = $deadline;
            $this->leadtime = $leadtime;
            $this->status = $status;
            $this->priority = $priority;
            $this->owner = $owner;
            $this->timeRequired = $time;
        }

        $db->close();
        return true;
    }

    /**
     * Change the name of a task
     */
    public function changeName($newName)
    {
        $db = newMysqliObject();

        $stmtChangeName = $db->prepare("UPDATE " . dbPrefix . "tasks SET `name` = ? WHERE `id` = ?");
        $stmtChangeName->bind_param("si", $newName, $this->taskid);
        $stmtChangeName->execute();
        if($stmtChangeName->sqlstate=='00000')
        {
            return true;
        }
        else
        {
            return false;
        }

    }

    /**
     * List the IDs of this users tasks, based on criteria
     */
    public function listTasks($itemsPerPage, $page, $orderby='id', $order='DESC')
    {

        $returnArray = array();
        $db = newMysqliObject();

        // First calculate how many results to fetch to show the correct page. We will require enough to disregard $count*pages
        $totalResultsRequired = ($itemsPerPage * $page) + $itemsPerPage;
       // print_r($totalResultsRequired);
        // Fetch the current user
        $user = new user();
        $googleAuth_id = $user->getGoogleAuthId();

        $orderCommand = $orderby . " " . $order;

        // Fetch the required pages
        $stmtFetchTasks = $db->prepare("SELECT `id` FROM " . dbPrefix . "tasks WHERE owner = ? AND status = 1 ORDER BY ? LIMIT ?");
        
        $stmtFetchTasks->bind_param("isi",$googleAuth_id,$orderCommand,$totalResultsRequired);
        $stmtFetchTasks->execute();
        $stmtFetchTasks->bind_result($id);

        // Calculate the tasks to actually return
        $i = '0';
        $currentPage = '1';
        while($stmtFetchTasks->fetch())
        {   
            // Check if we are on a new page
            if($i > $itemsPerPage)
            {
                $currentPage++;
                $i='0';
            }
            //print_r($currentPage);
            
            // Check if we are on the page we are interested in
            if($currentPage == $page)
            {
                $returnArray[] = $id;
            }
            $i++;
        }
        
        return $returnArray;
        
    }

    /**
     *  Returns the name of the task
     */
    public function name(){
        return($this->name);
    }

    /**
     * Returns an array of blockers for this user/task
     */
    public function getTaskBlockers()
    {
        
        // User owns blocker, remove the task
        $db = newMysqliObject();
        
        $taskid = $this->taskid;
        $stmtFetchBlockers = $db->prepare("SELECT `id`,`blocker_definition_id` FROM " . dbPrefix . "task_blocker_map WHERE `taskid` = ?");
        $stmtFetchBlockers->bind_param("i", $taskid);
        $stmtFetchBlockers->execute();
        $stmtFetchBlockers->bind_result($id, $blocker_definition_id);
        $returnArray = array();
        while ($stmtFetchBlockers->fetch()) {
            $returnArray[] = array('id' => $id, 'blockerid' => $blocker_definition_id);
        }
        return $returnArray;
    }

    /**
     * Marks this task as complete
     */
    public function markComplete()
    {
        
        // User owns blocker, remove the task
        $db = newMysqliObject();
        
        $taskid = $this->taskid;
        $stmtSetTaskStatus = $db->prepare("UPDATE " . dbPrefix . "tasks SET `status` = 2 WHERE `id` = ?");
        $stmtSetTaskStatus->bind_param("i", $taskid);
        $stmtSetTaskStatus->execute();
        return $true;
    }

    /**
     * * Updates the note on this task
     */
    public function updateNote($newNote)
    {
        $db = newMysqliObject();
        
        $taskid = $this->taskid;
        $stmtSetNote = $db->prepare("UPDATE " . dbPrefix . "tasks SET `notes` = ? WHERE `id` = ?");
        $stmtSetNote->bind_param("si", $newNote, $taskid);
        $stmtSetNote->execute();
        return true;
    }

    /**
     * * Updates the description on this task
     */
    public function updateDescription($newDescription)
    {
        $db = newMysqliObject();
        
        $taskid = $this->taskid;
        $stmtSetDescription = $db->prepare("UPDATE " . dbPrefix . "tasks SET `description` = ? WHERE `id` = ?");
        $stmtSetDescription->bind_param("si", $newDescription, $taskid);
        $stmtSetDescription->execute();
        return true;
    }

    /**
     * * Get the tasks description
     */
    public function getTaskDescription()
    {
        return $this->description;
    }

    /**
     * * Get the tasks notes
     */
    public function getTaskNotes()
    {
        return $this->notes;
    }

    /**
     * Add one task as a requirement of another
     */
    public function addTaskToTask($requiredTaskId)
    {
        // Check we are not mapping a task to iteslf
        if($this->taskid == $requiredTaskId) { return false; }

        // Create a database connection
        $db = newMysqliObject();
        
        // Make sure we have the current user
        $user = new user();
        $userid = $user->getUserId();

        // Check if this is already mapped
        $stmtCheckAlreadyMapped = $db->prepare("SELECT `id` FROM task_task_map WHERE `ownerid` = ? AND `owningtask` = ? AND `requiredtask` = ?");
        $stmtCheckAlreadyMapped->bind_param("iii", $userid, $this->taskid, $requiredTaskId);
        $stmtCheckAlreadyMapped->execute();
        $stmtCheckAlreadyMapped->store_result();

        // Test if we got a result
        if(!$stmtCheckAlreadyMapped->num_rows == '0') 
        {
            // Already mapped, return false
            return false;
        }

        $stmtInsertMapping = $db->prepare("INSERT INTO task_task_map (`ownerid`, `owningtask`, `requiredtask`) VALUES (?,?,?)");
        $stmtInsertMapping->bind_param("iii", $userid, $this->taskid, $requiredTaskId);
        $stmtInsertMapping->execute();
        return true;

    }

    /**
     * Add one task as a requirement of another
     */
    public function removeTaskFromTask($requiredTaskId)
    {

        // Create a database connection
        $db = newMysqliObject();
        
        // Make sure we have the current user
        $user = new user();
        $userid = $user->getUserId();


        $stmtRemoveMapping = $db->prepare("DELETE FROM task_task_map WHERE `ownerid` = ? AND `owningtask` = ? AND `requiredtask` = ?");
        $stmtRemoveMapping->bind_param("iii", $userid, $this->taskid, $requiredTaskId);
        $stmtRemoveMapping->execute();
        return true;

    }

    /**
     * Get the required sub tasks of this task
     */
    public function getrequiredTasks()
    {
   

        // Create a database connection
        $db = newMysqliObject();
        
        // Make sure we have the current user
        $user = new user();
        $userid = $user->getUserId();

        // Check if this is already mapped
        $stmtQueryMapped = $db->prepare("SELECT `requiredtask` FROM task_task_map WHERE `ownerid` = ? AND `owningtask` = ?");
        $stmtQueryMapped->bind_param("ii", $userid, $this->taskid);
        $stmtQueryMapped->execute();
        $stmtQueryMapped->bind_result($id);
        $returnArray = array();
        while ($stmtQueryMapped->fetch()) {
            $returnArray[] = array('requiredtaskid' => $id);
        }
        return $returnArray;


    }

    /**
     * Get the number of minutes required to execute this task
     */
    public function getTimeRequired()
    {
   
        return $this->timeRequired;

    }

    /**
     * Update the required time on this task
     */
    public function updateRequiredTime($requiredTime)
    {
        $db = newMysqliObject();
        
        $taskid = $this->taskid;
        $stmtSetRequiredTime = $db->prepare("UPDATE " . dbPrefix . "tasks SET `timerequired` = ? WHERE `id` = ?");
        $stmtSetRequiredTime->bind_param("si", $requiredTime, $taskid);
        $stmtSetRequiredTime->execute();
        return true;
    }

    
}
