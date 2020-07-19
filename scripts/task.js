class Task {
    constructor(id) {
        this.id = id;
        this.description;
        this.notes;
        this.blockers = [];
        this.promises = [];
    }

    alertId()
    {
        alert("This objects ID is " + this.id);
    }

    getName()
    {
        return this.name;
    }

    // Checks if the task is already present on the page. If not, it creates it, then in either case, updates it
    addTask()
    {
       
        // Verify the row for this task exists, creating it if not
        this.verifyTaskRow().then(()=>{
            // Now update the rows information
            this.fetchTaskInfo().then(()=>{
                // Row is up to date, display it if hidden
                this.showRow();
                // Update the rows based on the current blockers
                Promise.all(this.promises).then(() => {
                    this.blockersUpdated();  
                  });
                
                //this.addClickHandler();
            })
        });;
       

    }

    taskNameClicked()
    {
        var parent = this;
        // Edit the modal with this tasks information
        //Set title to name of task
        document.getElementById("taskEditModalTitle").innerHTML = this.name;
        document.getElementById("taskEditModalDescription").innerHTML = this.description;
        document.getElementById("taskEditModalNotes").innerHTML = this.notes;

        // Open the task modal    
        $('#taskEditModal').modal('show')

        // Add a handler for the close/save buttons being pressed
        $("#editTaskModalSave").click(function(){
            
            // Save the updated form fields
            console.log(parent);
            parent.updateNote(document.getElementById("taskEditModalNotes").value);
            parent.updateDescription(document.getElementById("taskEditModalDescription").value);

        });
        
    }

    // When the task name has been changed, fetch the new name, save to database and update the name field
    saveTaskName(originalDiv, newTaskName)
    {
    
        originalDiv.innerHTML = "Saving";

        // Request the name change
        fetch('api/task/?action=newTaskName&newName=' + newTaskName.value + '&taskid=' + this.id)
        .then((resp) => resp.json())
        .then(function(newTaskNameReturn) {
            if(newTaskNameReturn=='error')
            {
                displayError("danger", "There was an error changing this tasks name, please try again");
            }
            else
            {
            originalDiv.innerHTML = newTaskNameReturn;
            }
        });
    }

    // Checks if the task area is already present and creates it if not
    verifyTaskRow()
    {
        var parent = this;
        return new Promise(function(resolve, reject)
        {
            if(!document.getElementById("taskCollapseArea" + parent.id))
            {
                parent.createTaskRow().then(()=>{
                    resolve();
                });;
            }
            resolve();
        });
    }

    // Function called when the blockers on the main menu have changed
    blockersUpdated()
    {
        self = this;
        var blocked = false;
        blockerArray.forEach(function(blockerState, index){
        
            // Ensure that the blocker state of the task is set
            if(!self.blockers[index]) { self.blockers[index] = false; }
            
            // If the task blocker is true and the main blocker is false, the task is blocked
            if((self.blockers[index]==true) && (blockerState == false))    
            {
                blocked = true;
            }
            
        });
        
        // Show or hide the task depending on blocked state
        if(blocked == true)
        {
            self.hideRow();
        }
        else
        {
            self.showRow();
        }
    }

    // Create a row on the page for the task
    createTaskRow()
    {
        var parent = this;
       
        return new Promise(function(resolve, reject)
            {
            var newCollapseArea = document.createElement('div');
            newCollapseArea.id = "taskCollapseArea"+parent.id;
            newCollapseArea.className = "collapse taskCollapseArea";
            document.getElementById("taskArea").appendChild(newCollapseArea);

            var newRow = document.createElement('div');
            newRow.id = "taskRow"+parent.id;
            newRow.className = "row";
            document.getElementById("taskCollapseArea"+parent.id).appendChild(newRow);
            
            // Create the drag button for the task
            var newDragCol = document.createElement('div');
            newDragCol.id = "dragCol"+parent.id;
            newDragCol.className = "col-sm dragTask";
            newDragCol.innerHTML = '<span class="oi" data-glyph="list"></span>';
            document.getElementById("taskRow"+parent.id).appendChild(newDragCol);

            // Create a name column for the task
            var newNameCol = document.createElement('div');
            newNameCol.id = "nameCol"+parent.id;
            newNameCol.className = "col-sm taskName";
            document.getElementById("taskRow"+parent.id).appendChild(newNameCol);

            var newNameCol2 = document.createElement('div');
            newNameCol2.id = "nameCol2"+parent.id;
            newNameCol2.className = "col-sm";
            newNameCol2.innerHTML = "test";
            document.getElementById("taskRow"+parent.id).appendChild(newNameCol2);

            // Create the blocker column
            var newBlockerCol = document.createElement('div');
            newBlockerCol.id = "blocker"+parent.id;
            newBlockerCol.className = "col-sm blocker";
            
            // Build the HTML into a variable (Mostly for readability)
            var blockerDropDownMenuHTML = '<a class="btn btn-primary dropdown-toggle" href="#" role="button" id="blockerDropDownMenu' ;
            blockerDropDownMenuHTML = blockerDropDownMenuHTML + parent.id ;
            blockerDropDownMenuHTML = blockerDropDownMenuHTML +  '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Blockers</a>';
            blockerDropDownMenuHTML = blockerDropDownMenuHTML + '<div class="dropdown-menu" aria-labelledby="blockerDropDownMenu' + parent.id + '">';
            newBlockerCol.innerHTML = blockerDropDownMenuHTML + blockerHTML + '</div>';
            $(newBlockerCol.querySelectorAll(".blockerCheckBox")).addClass("taskid" + parent.id );
            document.getElementById("taskRow"+parent.id).appendChild(newBlockerCol);
            
            var newCompleteCol = document.createElement('div');
            newCompleteCol.id = "complete"+parent.id;
            newCompleteCol.innerHTML = '<span class="oi" data-glyph="check" id="complete'+parent.id+'"></span>';
            newCompleteCol.className = "col-sm taskCompleteButton"; 
            document.getElementById("taskRow"+parent.id).appendChild(newCompleteCol);


            bindHandlers();

            parent.showRow();
            resolve();
        });
        
    }

    // Shows the row
    showRow()
    {
        var parent = this;
        return new Promise(function(resolve, reject)
            {
                // Display the tasks row
                $("#taskCollapseArea"+parent.id).collapse('show');  
                resolve();
            });
    }
   
    completeClicked(test)
    {
        // Show the confirmation box for this id
        displayConfirmation(this.id)
    }

    // Hides the row
   hideRow()
   {
       var parent = this;
       return new Promise(function(resolve, reject)
           {
               // Hide the tasks row
               $("#taskCollapseArea"+parent.id).collapse('hide');  
               resolve();
           });
   }

   // Hides the row
   destroyRow()
   {
       var parent = this;
       return new Promise(function(resolve, reject)
           {
               // Destroy the tasks row
               if(document.getElementById("taskCollapseArea"+parent.id))
               {
                $( "#taskCollapseArea"+parent.id ).fadeOut( "slow", function() {
                    document.getElementById("taskCollapseArea"+parent.id).remove();
                  });
               
               }
               resolve();
           });
   }

    fetchTaskInfo()
    {
        var self = this;
        return new Promise(function(resolve, reject)
            {
                // Fetch the data and update the relevant divs
                fetch('api/task/?action=taskInfo&taskid=' + self.id) // Call the fetch function passing the url of the API as a parameter
                .then((resp) => resp.json())
                .then(function(info) {

                    console.log(info);
                    var element = document.getElementById("nameCol"+self.id);
                    element.innerHTML = info.name;
                    self.name = info.name;
                    self.description = info.description;
                    self.notes = info.notes;
                    
                    // Check if any blockers are defined
                    if ( info.blockers !== null) {
                        
                        // Loop through the blockers that were returned for this task
                        info.blockers.forEach(function(blocker){
                           self.promises.push = self.updateBlockerCheckBoxes(blocker);
                            
                        });
                                          

                    }

                })
                    resolve(); 
                
               
            });
    }

    
    updateBlockerCheckBoxes(blocker)
    {
        var self = this;
        return new Promise(function(resolve, reject)
            {
                // Find the appropriate checkbox (By task and blocker classes)
                let blockerCheckBox = [].slice.call(document.getElementsByClassName('blockerid' + blocker.blockerid + ' taskid' + self.id))
                //Now check its tickbox
                if(blockerCheckBox[0])
                {
                    blockerCheckBox[0].checked = true;
                    self.blockers[blocker.blockerid] = true;
                }
                else
                {
                    self.blockers[blocker.blockerid] = false;
                }
               resolve(); 
            });
    }

    
    // Creates or updates the note section attached to this task
    updateNote(note)
    {
        console.log(this.id);
        fetch('api/task/?action=updateNote&newNote=' + note + '&taskid=' + this.id) // Call the fetch function passing the url of the API as a parameter
        .then((resp) => resp.json())
        .then(function(info) {
            if(info.status != true)
            {
                displayError("danger", "There was an error saving this tasks new note, please try again");
            }

        });
    }


    // Creates or updates the description section attached to this task
    updateDescription(description)
    {
        console.log("Description");
        fetch('api/task/?action=updateDescription&newDescription=' + description + '&taskid=' + this.id) // Call the fetch function passing the url of the API as a parameter
        .then((resp) => resp.json())
        .then(function(info) {
            if(info.status != true)
            {
                displayError("danger", "There was an error saving this tasks new description, please try again");
            }

        });
    }



}