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
        // Check the object does not already have a task accosiated with it
        if (empty($this->taskid)) {
            $db = newMysqliObject();
           
            // Capture the current time
            $currTime = time();

            // Fetch the current users google sub id
            $user = new user();
            
            $googleAuth_id = $user->getGoogleAuthId();

            if(!$googleAuth_id){
                // User not logged in, do not proceed
                
                return false;
            }
            
            // Create the new task in the database
            $stmtCreateTask = $db->prepare("INSERT INTO " . dbPrefix . "tasks (`name`,`createdate`,`lastupdatedate`,`status`,`priority`,`owner`) VALUES (?,?,?,'1','2',?)");
            $stmtCreateTask->bind_param("ssss", $name, $currTime, $currTime, $googleAuth_id);
            $stmtCreateTask->execute();
            $db->close();

            // Set the object properties
            $this->setid($stmtCreateTask->insert_id);
            $this->createdate = $currTime;
            $this->lastupdatedate = $currTime;
            $this->status = '1';

            return $stmtCreateTask->insert_id;
        } else {
            return false;
        }
    }


    /**
    * Fetches the data for this object from the database
    * return bool Returns true on success, false on failure
    */
    private function fetchProperties()
    {
        $db = newMysqliObject();

        $stmtFetchProperties = $db->prepare("SELECT `name`,`description`,`createdate`,`lastupdatedate`,`deadline`,`leadtime`,`status`,`priority`,`owner` FROM " . dbPrefix . "tasks WHERE id = ? LIMIT 1");
        $stmtFetchProperties->bind_param("i", $this->taskid);
        $stmtFetchProperties->execute();
        $stmtFetchProperties->bind_result($name, $description, $createdate, $lastupdatedate, $deadline, $leadtime, $status, $priority, $owner);
        while ($stmtFetchProperties->fetch()) {
            $this->name = $name;
            $this->description = $description;
            $this->createdate = $createdate;
            $this->lastupdatedate = $lastupdatedate;
            $this->deadLine = $deadline;
            $this->leadtime = $leadtime;
            $this->status = $status;
            $this->priority = $priority;
            $this->owner = $owner;
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

}
