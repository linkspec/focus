<?php
require 'config.php';

/*
$task = new task;
$taskid = $task->newTask("Test task 1");

print_r($taskid);
*/

// Google auth test

$user = new user();

$authed = $user->isGoogleAuthed();




?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" href="style/custom.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-alerts@1.2.2/bootstrap-alerts.js" integrity="sha256-vpYRP0KsE7s50c8YjQZvZFIO7XJ02mZCaQzYTOdMqus=" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/open-iconic/1.1.1/font/css/open-iconic.min.css" integrity="sha256-CfN2r6i/dqkUHVRqpBzO3w21SnIWalwGfj5ScBPVzmI=" crossorigin="anonymous" />
<script src="scripts/task.js"></script>
<script src="scripts/blocker.js"></script>
<script src="scripts/ui.js"></script>


<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">Focus</a>
  
  <?php
  if(!$authed)
  {
      $authurl = $user->generateGoogleOauthUrl();
      echo '<a href="' . $authurl . '"><span class="oi" data-glyph="account-login"></span></a>';
  }
  else
  {
      echo '<span class="oi" data-glyph="account-logout" id="logoutButton"></span>';
  }
  ?>


  
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Link</a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link disabled" href="#">Disabled</a>
      </li>
     
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Blockers
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown" id="mainBlockerDropDown"></div>
      </li>
      
      
      <ul class="pagination" id="pagination">
        <li class="page-item"><a class="page-link" href="#">Previous</a></li>
        <li class="page-item"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#">Next</a></li>
      </ul>
    </ul>
    
    <form class="form-inline my-2 my-lg-0" id="newTaskTextForm">
      <input class="form-control mr-sm-2" type="search" placeholder="New task" aria-label="New task" id="newTaskTextBox">
    </form>
    <form class="form-inline my-2 my-lg-0">
      <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" id="taskSearchBox">
    </form>
  </div>
</nav>

    
<div class="modal fade" id="blockerEditModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="blockerEditModalBody">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="editBlockersModalSave">Save changes</button>
      </div>

      <div class="modal-body" id="blockerEditModalSecondBody">
        
                <label class="form-check-label" for="addNewBlocker">Add a new blocker</label>
                <input type="text" id="addNewBlocker">

      </div>
    </div>
  </div>
</div>


<div class="container" id="errorDisplayArea">
  <!-- Content here -->
</div>


<div class="container" id="completeConfirmationModalContainer">
  <div class="modal fade" id="completeConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="completeConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="completeConfirmationModalLabel">Mark task complete</h5>
        </div>
        <div class="modal-body" id="completeConfirmationModalBody">
          ...
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="completeConfirmationModalNo">No</button>
          <button type="button" class="btn btn-primary" id="completeConfirmationModalYes" >Yes</button>
          
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container" id="taskArea">
  <!-- Content here -->
</div>
