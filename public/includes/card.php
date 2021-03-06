<?php

/**
 * Completely renders a task to a card, including accompanying modals and neccesary javascript.
 */
function renderTask($task) { ?>
    <div class="task card mb-5" id="<?= $task['taskid'] ?>">

        <!-- Displays the task's title -->
        <div class="card-header"><?= $task['task_name'] ?><span class="text-muted"> - <?php echo date_format(date_create($task['when_due']), 'm-d-Y'); ?></span></div>

        <?php

        // Urgency is calculated as (time required to complete)/(time remaining until due date)
        $urgency = 0;

        $tc_splits = explode(":", $task['time_to_complete']);

        // If a task is marked as completed, leave urgency as zero.
        if ($task['completed'] == 0) {
            $to_time = strtotime(date("Y-m-d H:i:s"));
            $from_time = strtotime($task['when_due']);
            $time_remaining = round(($from_time - $to_time) / 60, 2) . " minutes";

            if ($time_remaining < 0) {
                $time_remaining = 0;
            }

            $minutes_to_complete = (intval($tc_splits[0]) * 24 * 60) + (intval($tc_splits[1]) * 60) + intval($tc_splits[2]);
            $urgency = $minutes_to_complete / $time_remaining;
        }
            
        $urgency_as_percentage = round($urgency * 100);
        $urgency_percentage_text = "0% (Completed)";

        // Clamp the percentage used for the progress bar to be between 0-100.
        if($urgency_as_percentage > 100) {
            $urgency_as_percentage = 100;
        }

        if ($urgency_as_percentage < 0) {
            $urgency_as_percentage = 0;
        }

        if ($task['completed'] == 0) {
            $urgency_percentage_text = $urgency_as_percentage . "%";
        }

        $urgency_bar_class = "";

        // Update the progress bar class/color based on how urgent the task is.
        if (in_array($urgency_as_percentage, range(0, 25))) {
            $urgency_bar_class = "success";
        } elseif (in_array($urgency_as_percentage, range(26, 50))) {
            $urgency_bar_class = "info";
        } elseif (in_array($urgency_as_percentage, range(51, 75))) {
            $urgency_bar_class = "warning";
        } else {
            $urgency_bar_class = "danger";
        }

        ?>
        <div class="card-block">
            <div class="row no-gutters">
                <div class="col">
                    <!-- Text for the card -->
                    <div class="row col">
                        <p><?= $task['notes'] ?></p>
                    </div>

                    <div class="row">
                        <div class="col">
                        <!-- Detail toggle -->
                            <a data-toggle="collapse" href="#taskDetail<?= $task['taskid'] ?>" aria-expanded="false" aria-controls="taskDetail<?= $task['taskid'] ?>">
                                Details <i class="fa fa-chevron-down" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>

                    <div id="taskDetail<?= $task['taskid'] ?>" class="collapse" role="tabpanel">
                        <div class="card">
                            <div class="card-block">
                                <div class="row">
                                    <!-- Display the time information for the card -->
                                    <div class="col">
                                        <p class="card-due-on-header mb-0">
                                            Due:
                                        </p>
                                        <p class="card-due-on text-muted">
                                            <?php echo date_format(date_create($task['when_due']), 'g:ia \o\n l, F jS Y'); ?>
                                        </p>
                                    </div>
                                    <div class="col">
                                        <p class="card-complete-time-header mb-0">
                                            Estimated time to complete:
                                        </p>
                                        <p class="card-complete-time text-muted">
                                            <?php 
                                            echo intval($tc_splits[0]) . " days, " . intval($tc_splits[1]) . " hours, and " . intval($tc_splits[2]) . " minutes";
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <!-- Show the textual and graphical location of the task -->
                                        <p class="card-due-on-header mb-0">
                                            Location:
                                        </p>
                                        <p class="card-due-on text-muted">
                                            <?= $task['location'] ?>
                                        </p>
                                        <iframe
                                            width="100%"
                                            height="450"
                                            frameborder="0" style="border:0"
                                            src="https://www.google.com/maps/embed/v1/place?key=AIzaSyCDspdIFVXRGVm6UJxgbWcoCFliWXk-6Ss&q=<?php echo str_replace(' ', '+', $task['location']); ?>" allowfullscreen>
                                        </iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row justify-content-between no-gutters">
                        <div class="col-12 col-sm mb-3 mb-sm-0 text-center text-sm-left">
                            <?php if ($task['completed'] > 0) { ?>
                                <a href="#" taskid="<?= $task['taskid'] ?>" class="btn btn-primary">
                                    <i class="fa fa-check mr-2" aria-hidden="true"></i>Completed
                                </a>
                            <?php } else { ?>
                                <a href="#" taskid="<?= $task['taskid'] ?>" id="markcomplete" class="btn btn-primary">
                                    Mark as complete
                                </a>
                            <?php } ?>
                        </div>
                        <div class="col-12 col-sm-auto mb-3 mb-sm-0 text-center">
                            <a href="#" class="btn btn-secondary" data-toggle="modal" data-target="#editModal<?= $task['taskid'] ?>">
                                <i class="fa fa-pencil mr-2" aria-hidden="true"></i>Edit
                            </a>
                        </div>
                        <div class="col-12 col-sm-auto text-center">
                            <a href="#" class="btn btn-danger ml-sm-2" data-toggle="modal" data-target="#deleteModal<?= $task['taskid'] ?>">
                                <i class="fa fa-times mr-2" aria-hidden="true"></i>Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <p class="text-center text-<?= $urgency_bar_class ?> mb-0">Urgency level:</p>
            <h4 class="text-center text-<?= $urgency_bar_class ?>"><?= $urgency_percentage_text ?></h4>
            <div class="progress">
                <div class="progress-bar progress-bar-striped bg-<?= $urgency_bar_class ?>" role="progressbar" style="width: <?= $urgency_as_percentage ?>%" aria-valuenow="<?= $urgency_as_percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <!-- Task deletion modal -->
    <div class="modal fade" id="deleteModal<?= $task['taskid'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="deletetask">
                <input type="hidden" id="taskid" value="<?= $task['taskid'] ?>" />
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delte this task?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Yes, I'm Sure</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Task edit modal -->
    <div class="modal fade" id="editModal<?= $task['taskid'] ?>" tabindex="-1" role="dialog" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="edittask">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Edit task:</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="taskTitle">Title</label>
                            <input type="text" class="form-control" id="taskTitle" aria-describedby="titleHelp" value="<?= $task['task_name'] ?>">
                            <small id="titleHelp" class="form-text text-muted">The title for your task.</small>
                        </div>
                        <div class="form-group">
                            <label for="taskDueDate">Due date</label>
                            <input class="flatpickr flatpickr-input active form-control" id="taskEditDueDate<?= $task['taskid'] ?>" type="text" value="<?= $task['when_due'] ?>" data-id="datetime" readonly="readonly">
                        </div>
                        <div class="form-group">
                            <label for="taskEstDays">Est. days to complete:</label>
                            <input type="number" class="form-control" id="taskEstDays" min="0" value="<?= intval($tc_splits['0']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="taskEstHours">Est. hours to complete:</label>
                            <input type="number" class="form-control" id="taskEstHours" min="0" value="<?= intval($tc_splits['1']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="taskEstMinutes">Est. minutes to complete:</label>
                            <input type="number" class="form-control" id="taskEstMinutes" min="0" value="<?= intval($tc_splits['2']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="taskLocation">Location</label>
                            <input type="text" class="form-control" id="taskLocation" value="<?= $task['location'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="taskNotes">Notes</label>
                            <textarea class="form-control" id="taskNotes" rows="3"><?= $task['notes'] ?></textarea>
                        </div>
                        <input type="hidden" id="taskid" value="<?= $task['taskid'] ?>" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        (function ($) {
            $('#taskEditDueDate<?= $task['taskid'] ?>').flatpickr({
                enableTime: true
            });
        })(jQuery);
    </script>
<?php } ?>
