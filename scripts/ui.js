// Run the below on first load

// Build the HTML for the blocker list
var blockerHTML = "";
var taskObjectArray = [];
var blockerArray = [];
// Build the page for the first time
reBuildPage();

function reBuildPage()
{
    fetch('api/blocker/?action=blockerList') // Fetch the blocker list
        .then((resp) => resp.json())
            .then(function(blockerList) {
                blockerHTML =  '<form class="px-5">' + 
                '<div class="form-group">';
                mainMenublockerHTML = blockerHTML;
                blockerList.forEach(blocker => {
                    blockerHTML = blockerHTML + '<div class="form-row">' + 
                    '<label class="form-check-label blockerItemText blockerId' + blocker.id + '" for="dropdownCheck">' + blocker.name + '</label>' +
                    '<input type="checkbox" class="form-check-input  blockerCheckBox blockerid' + blocker.id + '">' +
                    '</div>';     
                    
                    mainMenublockerHTML = mainMenublockerHTML + '<div class="form-row" id="mainMenublocker' + blocker.id + '">' + 
                    '<label class="form-check-label mainMenublockerItemText blockerId' + blocker.id + '" for="dropdownCheck">' + blocker.name + '</label>' +
                    '<input type="checkbox" class="form-check-input  mainMenublockerCheckBox blockerid' + blocker.id + '">' +
                    '</div>';   

                    // Add the blocker to the edit blockers modal
                    addLineToBlockerEditModal(blocker.id, blocker.name);

                })
                blockerHTML = blockerHTML + '</div>' + 
                    '</form>';
                
                // Change the class for use in the main menu
                //mainMenublockerCheckBoxReplaced = blockerHTML.replace(/blockerCheckBox/g, "mainMenublockerCheckBox");
                //mainMenublockerHTML = mainMenublockerCheckBoxReplaced.replace(/blockerItemText/g, "mainMenublockerItemText");
                // Create the form for the main blocker dropdown
                newBlockerForm = '<form id="addBlockerForm">' + 
                '<div class="form-group">' +
                '<div class="form-row">' + 
                '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#blockerEditModal">Edit blockers</button>'+
                '</div>' +
                '</div>' + 
                '</form>';
                document.getElementById("mainBlockerDropDown").innerHTML = mainMenublockerHTML + newBlockerForm;
                
            }).then(()=>{
                fetchTasks().then(()=>{
                    
                    // Bind any handlers needed
                    bindHandlers();
                    
                });
            });

}



function removeAllTasks()
{
     document.getElementById("taskArea").innerHTML = "";
}

function bindClickHandlerToAll()
{
    $(".taskName").unbind('click');
    // Handle users clicking on a task name
    $(".taskName").click(function(clickEvent){
        
        // Unbind clicking on this event id
        $("#" + clickEvent.target.id).unbind('click');
        
        // Call the appropraite object to handle the action
        taskObjectArray[clickEvent.target.id.replace("nameCol", "")].taskNameClicked();       
    });
}


function bindHandlers()
{

    bindClickHandlerToAll();

       // Handle save being clicked on the blocker edit modal
       $("#editBlockersModalSave").off();
       $("#editBlockersModalSave").click(function(){
        editBlockersModalSave();
       });

       // Handle user clicking delete on a blocker in the blocker edit modal
       $(".requestBlockerDelete").off();
       $(".requestBlockerDelete").click(function(event){
        requestBlockerDelete(event);
       });

       //Handle user clicking confirm on a blocker delete in the blocker edit modal
       $(".confirmBlockerDelete").off();
       $(".confirmBlockerDelete").click(function(event){
        confirmBlockerDelete(event);
       });

       //Handle user clicking confirm on a blocker delete in the blocker edit modal
       $(".cancelBlockerDelete").off();
       $(".cancelBlockerDelete").click(function(event){
        cancelBlockerDelete(event);
       });
       

        //Bind to the search bar
         $("#taskSearchBox").off();
         $("#taskSearchBox").keyup(function(){
            taskSearch(document.getElementById("taskSearchBox").value);
        });

        //Bind to the new blocker text box
        $("#addBlocker").off();
        // Block the normal submit routine
        $( "#addBlockerForm" ).submit(function( event ) {
            event.preventDefault();
          });
        $("#addBlocker").keyup(function(keyEvent){
            addBlocker(document.getElementById("addBlocker").value, keyEvent.which);
        });

        // Bind handlers for user selecting a blocker on a task
        $(".blockerCheckBox").off();
        $(".blockerCheckBox").change(function(clickEvent){
            // Call the function to replace the text with the form
            blockerCheckBoxChange(clickEvent);
        })


        // Bind the to the new task search box
        $("#newTaskTextBox").off();
        // Block the normal submit routine
        $( "#newTaskTextForm" ).submit(function( event ) {
            event.preventDefault();
          });
        $("#newTaskTextBox").keyup(function(keyEvent){
            newTaskBoxTextChange(document.getElementById("newTaskTextBox").value, keyEvent.which);
        });

       

        //Handle logout requests
        $("#logoutButton").off();
        $("#logoutButton").click(function(){
            // Request log out of the application
            fetch('/api/logout/').then(response => {
                // Remove all tasks from the page
                removeAllTasks();
            });
        });

        // Trigger an update when the main menu blocker menu is changed
        $(".mainMenublockerCheckBox").off();
        $(".mainMenublockerCheckBox").change(function(clickEvent){
            // Call the function to replace the text with the form
            mainMenuBlockerChange(clickEvent);
        })
        
       // Handle 'complete' clicks
       $(".taskCompleteButton").unbind('click');
       $(".taskCompleteButton").click(function(clickEvent){
           // Call the function to open the confirmation box
           //console.log(clickEvent.target.id.replace("complete", ""));
           taskObjectArray[clickEvent.target.id.replace("complete", "")].completeClicked();       
           //mainMenuBlockerChange(clickEvent);
       })
   

      
}

// Fired when a user clicks on the text body of a blocker on the main menu
function mainMenuBlockerClick(event)
{
    event.target.className.split(" ").forEach(className => {
        // Get the id of the blocker to be edited
        if(className.includes("blockerId")){
            blockerid = className.replace("blockerId","");

            // Replace the blocker line with a text field
            blockerLineToEdit =  document.getElementById("mainMenublocker" + blockerid);
            originalHTML = blockerLineToEdit.innerHTML;

            // Create edit text box
            editBox = '<label for="addBlocker">Add a new blocker</label>' +
            '<input type="text" class="form-control" id="editBlockerNameBox' + blockerid + '" placeholder="">';
            blockerLineToEdit.innerHTML = editBox;

            
            // Register a new handler for submission
            //Bind to the new blocker text box
            $("#editBlockerNameBox"+ blockerid).off();
            // Block the normal submit routine
            $( "#editBlockerNameBox"+ blockerid ).submit(function( event ) {
                event.preventDefault();
            });

            $("#editBlockerNameBox"+ blockerid).keyup(function(keyEvent){
                if ( keyEvent.which == 13 ) {
                
                    // Save the new value to the database
                    saveBlockerNameChange(blockerid, document.getElementById('editBlockerNameBox' + blockerid));
        
                    // Unbind the listners now its been used
                    $("#editBlockerNameBox" + blockerid).off();
                }

               /* $(document).keyup(function escapeHandler(keyEvent){
                    if ( keyEvent.which == 27 ) {
                        blockerLineToEdit.innerHTML = originalHTML;
                    }
                });*/
            });
        }
    });
}

// User submitted a change of blocker name
function saveBlockerNameChange(id, name, blockerLineToEdit)
{
    fetch('api/blocker/?action=blockerRename&id=' + id + '&name=' + name.value)
        .then(response => {
            return response.json()
        }).then(data => {
            reBuildPage();
        });

}

// Fetch all if this users tasks
function fetchTasks()
{
    return new Promise(function(resolve, reject)
    {
        // Fetch a list of tasks
        fetch('api/task/?action=getTaskIds')
        .then(response => {
            return response.json()
        })
        .then(data => {

            // First loop through looking to any tasks that are no longer required
            let htmlCollection = document.getElementsByClassName("taskCollapseArea");
            let taskAreaId = [].slice.call(htmlCollection)
            taskAreaId.forEach(element => {
               
                if(!data.includes(parseInt(element.id.replace("taskCollapseArea", ""))))
                {
                    // The task is no longer required, remove the div
                    element.remove;
                }
               
            });

            // Loop through the returned IDs
            data.forEach(taskId => {
            
                // Create a task object and request it be shown
                taskObjectArray[taskId] = new Task(taskId);
                taskObjectArray[taskId].addTask(taskId);

            // Fire the function to fetch/polpulate the name
            //fetchTaskInfo(taskId);
            })
            //console.log(taskObjectArray);
        });
        resolve();
    });
   
}



// This function enables the user to click on a task name and edit it
function taskNameClicked(clickEvent)
{
    
    // Replace the name field with a text input, containing
    var targetid = clickEvent.target.id.replace("nameCol","");
    var targetElement = document.getElementById(clickEvent.target.id)
    var originalTaskName = targetElement.innerHTML;
    targetElement.innerHTML = '<input type="text" value="' + originalTaskName + '" id="taskNameEdit' + targetid + '"></input>';
    
    // Register handlers for this box being submitted or clicked off of
    $("#taskNameEdit" + targetid).keyup(function(keyEvent){
        if ( keyEvent.which == 13 ) {
            
            // Save the new value to the database
            saveTaskName(targetid, targetElement, document.getElementById('taskNameEdit' + targetid));

            // Unbind the listners now its been used
            $("#taskNameEdit" + targetid).off();
         }
         if ( keyEvent.which == 27 ) {
            targetElement.innerHTML = originalTaskName;
            
            // Unbind the listners now its been used
            $("#taskNameEdit" + targetid).off();
           
           
         }
    });

    $(document).keyup(function escapeHandler(keyEvent){
        if ( keyEvent.which == 27 ) {
            // Set the HTML back to original without change
            targetElement.innerHTML = originalTaskName;
            
            // Rebind the click handler                
            $(targetElement).unbind('click');
            // Handle users clicking on task names
            $(targetElement).click(function(clickEvent){
                
                // Unbind clicking on this event id
                // Get the clicked tasks id
                $("#" + clickEvent.target.id).unbind('click');
                
                // Call the function to replace the text with the form
                taskNameClicked(clickEvent);
                
                                });
            bindClickHandlerToAll();
         }

    });
}



function displayError(type, message)
{
    // Generate an ID for the alert
    var d = new Date();
    var alertId = d.getTime();

    var newAlert = document.createElement('div');
    newAlert.id = alertId;
    newAlert.className = "alert alert-" + type + " alert-dismissible fade show";
    var button = '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
    newAlert.innerHTML = message + button;
    document.getElementById("errorDisplayArea").appendChild(newAlert);
    $("#" + alertId).attr("role", "alert")
    $('.alert').alert()
}

// Display a confirmation modal before marking a task as complete
function displayConfirmation(taskid)
{
   
    // Edit the modal with this tasks details
    var taskName = taskObjectArray[taskid].getName();
    document.getElementById("completeConfirmationModalBody").innerHTML = taskName;

    // Show the modal
    $('#completeConfirmationModal').modal('show');
    
    // Bind handlers for user response
    $('#completeConfirmationModalYes').off();
    $('#completeConfirmationModalYes').click(function(clickEvent){
        fetch('api/task/?action=complete&id=' + taskid)
        taskObjectArray[taskid].destroyRow();
        $('#completeConfirmationModal').modal('hide');
    });

    $('#completeConfirmationModalNo').off();
    $('#completeConfirmationModalNo').click(function(clickEvent){
        $('#completeConfirmationModal').modal('hide');
    });

    bindClickHandlerToAll();
 
    
    
    
}

function taskSearch(searchValue)
{
    // Get all tasks and loop through them
    tasks = document.getElementsByClassName("taskName")
    for (i = 0; i < tasks.length; i++) {
        // Check if this task matches the search
        id = tasks[i].id.replace("nameCol","");
        if(!tasks[i].innerHTML.includes(searchValue))
        {
            // Not found, hide this task
            $("#taskCollapseArea"+id).collapse('hide');
        }
        else
        {
            // Found, make sure task is visible
            $("#taskCollapseArea"+id).collapse('show');
        }
      }
    
}


function newTaskBoxTextChange(taskName, key)
{
    
    if(key == 13)
    {
        // Create the new task
        
        fetch('api/task/?action=newTask&newTaskName=' + taskName)
        .then((resp) => resp.json())
        .then(function(newTaskReturn) {
            
            if(newTaskReturn=='error')
            {
                displayError("danger", "There was an error creating this task, please try again");
            }
            else
            {
                var newTask = new Task(newTaskReturn.result);
                newTask.addTask();
                $("#newTaskTextBox").val("");
            }
        });

        
    }
}


function addBlocker(blockerName, key)
{
    if(key == 13)
    {
        // Create the new task

        fetch('api/blocker/?action=newBlocker&newBlockerName=' + blockerName)
        .then((resp) => resp.json())
        .then(function(newTaskReturn) {
            if(newTaskReturn=='error')
            {
                displayError("danger", "There was an error adding this blocker, please try again");
            }
            reBuildPage();
        });

        
    }
}

function blockerCheckBoxChange(clickEvent)
{
    // Extract the blocker and task ids from the clicked elements class designations
    clickEvent.target.className.split(" ").forEach(function(classItem){
        if(classItem.includes("blockerid"))
        {
            blockerid = classItem.replace("blockerid", "");
        }

        if(classItem.includes("taskid"))
        {
            taskid = classItem.replace("taskid", "");
        }
    });

    // Check if we are checking or unchecking the box
    if (clickEvent.target.checked)
    {
        fetch('api/blocker/?action=addBlockerToTask&blockerid=' + blockerid + '&taskid=' + taskid);
    }
    else
    {
        fetch('api/blocker/?action=removeBlockerFromTask&blockerid=' + blockerid + '&taskid=' + taskid);
    }
}

function mainMenuBlockerChange(clickEvent)
{
    // Go through each blocker and save the current status to an array
    let blockerMenuByClass = [].slice.call(document.getElementsByClassName('mainMenublockerCheckBox'))
    blockerMenuByClass.forEach(function(blockerMenuitem){
        blockerMenuitem.className.split(" ").forEach(function(blockerMenuitemClass){
            if(blockerMenuitemClass.includes("blockerid"))
            {
                blockerArray[blockerMenuitemClass.replace("blockerid", "")] = document.getElementsByClassName('mainMenublockerCheckBox ' + blockerMenuitemClass)[0].checked;
            }
        });
    });
    // Have each task update based on the new status
    taskObjectArray.forEach(function(task){
        task.blockersUpdated();
    });
}



/*
function createTaskRow(taskId, callback)
{
    var newCollapseArea = document.createElement('div');
    newCollapseArea.id = "taskCollapseArea"+taskId;
    newCollapseArea.className = "collapse taskCollapseArea";
    document.getElementById("taskArea").appendChild(newCollapseArea);

    var newRow = document.createElement('div');
    newRow.id = "taskRow"+taskId;
    newRow.className = "row";
    document.getElementById("taskCollapseArea"+taskId).appendChild(newRow);
    

    // Create a name column for the task
    var newNameCol = document.createElement('div');
    newNameCol.id = "nameCol"+taskId;
    newNameCol.className = "col-sm taskName";
    document.getElementById("taskRow"+taskId).appendChild(newNameCol);

    var newNameCol2 = document.createElement('div');
    newNameCol2.id = "nameCol2"+taskId;
    newNameCol2.className = "col-sm";
    newNameCol2.innerHTML = "test";
    document.getElementById("taskRow"+taskId).appendChild(newNameCol2);

    // Create the blocker column
    var newBlockerCol = document.createElement('div');
    newBlockerCol.id = "blocker"+taskId;
    newBlockerCol.className = "col-sm blocker";
    
    // Build the HTML into a variable (Mostly for readability)
    var blockerDropDownMenuHTML = '<a class="btn btn-primary dropdown-toggle" href="#" role="button" id="blockerDropDownMenu' ;
    blockerDropDownMenuHTML = blockerDropDownMenuHTML + taskId ;
    blockerDropDownMenuHTML = blockerDropDownMenuHTML +  '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Blockers</a>';
    blockerDropDownMenuHTML = blockerDropDownMenuHTML + '<div class="dropdown-menu" aria-labelledby="blockerDropDownMenu' + taskId + '">';
    newBlockerCol.innerHTML = blockerDropDownMenuHTML + blockerHTML + '</div>';
    $(newBlockerCol.querySelectorAll(".blockerCheckBox")).addClass("taskid" + taskId );
    document.getElementById("taskRow"+taskId).appendChild(newBlockerCol);
    
    bindHandlers();

    callback (taskId);
}
*/

/*
function showRow(taskId)
{
    $("#taskCollapseArea"+taskId).collapse('show');
}
*/

/*
// Fetch the name of the task
function fetchTaskInfo(taskId)
{
    fetch('api/task/?action=taskInfo&taskid=' + taskId) // Call the fetch function passing the url of the API as a parameter
    .then((resp) => resp.json())
    .then(function(info) {
        var element = document.getElementById("nameCol"+taskId);
        element.innerHTML = info.name;
        
        // Check if any blockers are defined
        if ( info.blockers !== null) {
            // Loop through the blockers that were returned for this task
            info.blockers.forEach(function(classItem){
                
                // Find the appropriate checkbox (By task and blocker classes)
                let blockerCheckBox = [].slice.call(document.getElementsByClassName('blockerid' + classItem.blockerid + ' taskid' + taskId))
                //Now check its tickbox
                blockerCheckBox[0].checked = true;
                
                // Check if this blocker is UNSELECTED on the main menu. If so, we hide this task as currently blocked
                let mainMenuCheck = [].slice.call(document.getElementsByClassName('mainMenublockerCheckBox' + ' blockerid' + classItem.blockerid));
                //Now check its tickbox
                if(mainMenuCheck[0].checked == false){
                    removeTask(taskId);
                }
            });
            
        }
    })
}
*/

/*
// Removes a task from the page
function removeTask(taskid)
{
    document.getElementById("taskCollapseArea"+taskid).remove();
}
*/

/*
function saveTaskName(id, originalDiv, newTaskName)
{
    originalDiv.innerHTML = "Saving";

    // Request the name change
    fetch('api/task/?action=newTaskName&newName=' + newTaskName.value + '&taskid=' + id)
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
 */