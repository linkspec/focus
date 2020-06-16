//--------------------------------------------------------------------------
// This section contains functions that relate to blockers globally
//--------------------------------------------------------------------------

// Executed when the user clicks save on the 'blockers edit' modal
function editBlockersModalSave()
{
    // Fetch the input boxes in the edit blocker modal
    let htmlCollection = document.getElementsByClassName("blockerModalRow");
            let blockerModals = [].slice.call(htmlCollection)
            blockerModals.forEach(element => {
                // Get the blocker id and the value
                id = element.id.replace("blockerModalRow","")
                value = document.getElementById("blockerEditModal" + id).value
                
                // Save the changes to the database
                fetch('api/blocker/?action=blockerRename&id=' + id + '&name=' + value)
                .then(function() {
                

                    // Close the edit modal
                    $('#blockerEditModal').modal('hide')

                    // Run a rebuild to update everything
                    reBuildPage();
                });
            });
}


// Adds a line to the blocker modal if it does not already exist
function addLineToBlockerEditModal(id, name)
{
    if(!document.getElementById("blockerModalRow" + id))
    {
            var newModalRow = document.createElement('div');
            newModalRow.id = "blockerModalRow" + id;
            newModalRow.className = "blockerModalRow";
            document.getElementById("blockerEditModalBody").appendChild(newModalRow);
    }
    
    var newHTML = '<input type="text" id="blockerEditModal' + id + '" value="' + name + '">'
    newHTML = newHTML + ' - <div id="blockerDeleteTextBox' + id + '"><a href="#" class="requestBlockerDelete" id="requestBlockerDelete' + id + '">Delete</a></div>';

    document.getElementById("blockerModalRow" + id).innerHTML = newHTML;
}

// Fired when the user clicks the 'Delete' option on the blocker edit modal
function requestBlockerDelete(event)
{
    // Replace the delete text with a yes/no option
    //document.getElementById("blockerDeleteTextBox" + event.target.id).innerHTML = "someshit#";
    id = event.target.id.replace("requestBlockerDelete", "");
    newHTML = '<a href="#" class="confirmBlockerDelete" id="confirmBlockerDelete' + id + '">Yes</a>'+
                '/' + 
            '<a href="#" class="cancelBlockerDelete" id="cancelBlockerDelete' + id + '">No</a>';
    document.getElementById("blockerDeleteTextBox" + id).innerHTML = newHTML;
    
    // Bind handlers for the new elements
    bindHandlers();
}

function confirmBlockerDelete(event)
{
     // Save the changes to the database
     fetch('api/blocker/?action=blockerDelete&id=' + id)
     .then(function() {
     
         // Close the edit modal
         $('#blockerEditModal').modal('hide')

         // Run a rebuild to update everything
         reBuildPage();
     });
}

function cancelBlockerDelete(event)
{
    id = event.target.id.replace("cancelBlockerDelete", "");
    newHTML = '<a href="#" class="requestBlockerDelete" id="requestBlockerDelete' + id + '">Delete</a>';
    document.getElementById("blockerDeleteTextBox" + id).innerHTML = newHTML;

    // Bind handlers for the new elements
    bindHandlers();
}

//--------------------------------------------------------------------------
// The class is for individual blockers
//--------------------------------------------------------------------------

class Blocker {
    constructor(id) {
        this.id = id;
        this.blockers = [];
        this.promises = [];
    }

    alertId()
    {
        alert("This objects ID is " + this.id);
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
                    console.log("All promises finished");
                    this.blockersUpdated();  
                  });
                
                //this.addClickHandler();
            })
        });;
       

    }

    taskNameClicked()
    {
        //alert("#nameCol"+clickEvent);
        // Replace the name field with a text input, containing
        //var targetid = clickEvent.target.id.replace("nameCol","");
        self = this;
        var targetElement = document.getElementById("nameCol"+this.id)
        var originalTaskName = targetElement.innerHTML;
        targetElement.innerHTML = '<input type="text" value="' + originalTaskName + '" id="taskNameEdit' + this.id + '"></input>';
        
        // Register handlers for this box being submitted or clicked off of
        $("#taskNameEdit" + this.id).keyup(function(keyEvent){
            if ( keyEvent.which == 13 ) {
                
                // Save the new value to the database
                self.saveTaskName(targetElement, document.getElementById('taskNameEdit' + self.id));

                // Unbind the listners now its been used
                $("#taskNameEdit" + this.id).off();
            }
            if ( keyEvent.which == 27 ) {
                targetElement.innerHTML = originalTaskName;
                
                // Unbind the listners now its been used
                $("#taskNameEdit" + self.id).off();
            
            
            }
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
            newDragCol.innerHTML = '...';
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
                document.getElementById("taskCollapseArea"+parent.id).remove();
               }
               resolve();
           });
   }

    fetchTaskInfo()
    {
        var self = this;
        return new Promise(function(resolve, reject)
            {
                console.log("Starting to process fetchTaskInfo");
                // Fetch the data and update the relevant divs
                fetch('api/task/?action=taskInfo&taskid=' + self.id) // Call the fetch function passing the url of the API as a parameter
                .then((resp) => resp.json())
                .then(function(info) {
                    console.log("Starting to process reply");
                    var element = document.getElementById("nameCol"+self.id);
                    element.innerHTML = info.name;
                    
                    // Check if any blockers are defined
                    if ( info.blockers !== null) {
                        
                        // Loop through the blockers that were returned for this task
                        info.blockers.forEach(function(blocker){
                        console.log(blocker);
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
                console.log("Starting to process " + blocker.blockerid)
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
                console.log("Finished processing " + blocker.blockerid)
               resolve(); 
            });
    }

    

    sampleFunction()
    {
        var self = this;
        return new Promise(function(resolve, reject)
            {
                // Function actions go here

               resolve(); 
            });
    }



}