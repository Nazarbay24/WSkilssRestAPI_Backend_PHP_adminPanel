<?php
    require_once('../db.php');

    if (!$_SESSION['logged']) {
        header('Location: /?not_logged=true');
    }

    $result = mysqli_query($connection, "SELECT * FROM `events` WHERE `id` = '{$_GET['id']}'");
    $event = mysqli_fetch_assoc($result);

    if ($event['organizer_id'] !== $_SESSION['logged']['id']) {
        header('Location: /?not_access=true');
    }


    if (isset($_POST['submit'])) {
        $type = $_POST['type'];
        $title = $_POST['title'];
        $speaker = $_POST['speaker'];
        $room_id = $_POST['room'];
        $cost = $_POST['cost'];
        $start = $_POST['start'];
        $end = $_POST['end'];
        $description = $_POST['description'];



        $title_err = false;
        $speaker_err = false;
        $start_err = false;
        $end_err = false;
        $description_err = false;
        $booked_err = false; //занятость комнаты

        $error = false;


        $booked_result = mysqli_query($connection, "SELECT * FROM `sessions` 
            WHERE (`room_id` = '$room_id') 
            AND (`start` = '$start' 
            OR `end` = '$end' 
            OR `start` BETWEEN '$start' AND '$end' 
            OR `end` BETWEEN '$start' AND '$end' 
            OR `start` < '$start' AND `end` > '$end')"
        );

        if (mysqli_num_rows($booked_result) > 0) {
            $booked_err = 'Номер уже забронирован за это время';
            $error = true;
        }

        if ($cost <= 0 || $cost == '') {
            $cost = null;
        }
        if ($title == '') {
            $title_err = 'Это поле обезятельное';
            $error = true;
        }
        if ($speaker == '') {
            $speaker_err = 'Это поле обезятельное';
            $error = true;
        }
        if ($start == '') {
            $start_err = 'Это поле обезятельное';
            $error = true;
        }
        if ($end == '') {
            $end_err = 'Это поле обезятельное';
            $error = true;
        }
        if ($description == '') {
            $description_err = 'Это поле обезятельное';
            $error = true;
        }



        if ($error == false) {
            mysqli_query($connection, "INSERT INTO `sessions`(`room_id`,`title`,`description`,`speaker`,`start`,`end`,`type`,`cost`)
                VALUES('$room_id','$title','$description','$speaker','$start','$end','$type','$cost')");

            header('Location: ../events/detail.php?id='.$event['id'].'&session_create=true#sessions');
        }
    }
?>



<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Event Backend</title>

    <base href="../">
    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!-- Custom styles -->
    <link href="assets/css/custom.css" rel="stylesheet">
</head>

<body>
<nav class="navbar navbar-dark fixed-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="events/">Event Platform</a>
    <span class="navbar-organizer w-100"><?php echo $_SESSION['logged']['name']; ?></span>
    <ul class="navbar-nav px-3">
        <li class="nav-item text-nowrap">
            <a class="nav-link" id="logout" href="../logout.php">Sign out</a>
        </li>
    </ul>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="events/">Manage Events</a></li>
                </ul>

                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span><?php echo $event['name']; ?></span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="events/detail.php?id=<?php echo $event['id']; ?>">Overview</a></li>
                </ul>

                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Reports</span>
                </h6>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item"><a class="nav-link" href="reports/?id=<?php echo $event['id']; ?>">Room capacity</a></li>
                </ul>
            </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="border-bottom mb-3 pt-3 pb-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h1 class="h2"><?php echo $event['name']; ?></h1>
                </div>
                <span class="h6"><?php echo $event['date']; ?></span>
            </div>

            <div class="mb-3 pt-3 pb-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h2 class="h4">Create new session</h2>
                </div>
            </div>

            <form class="needs-validation" method="POST" novalidate action="">

                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="selectType">Type</label>
                        <select class="form-control" id="selectType" name="type">
                            <option value="talk" <?php if ($type=='talk') {
                                echo "selected";
                            } ?>>Talk</option>
                            <option value="workshop" <?php if ($type=='workshop') {
                                echo "selected";
                            } ?>>Workshop</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="inputTitle">Title</label>
                        <!-- adding the class is-invalid to the input, shows the invalid feedback below -->
                        <input type="text" class="form-control <?php if ($title_err) {echo('is-invalid');} ?>" id="inputTitle" name="title" placeholder="" value="<?php echo($title); ?>">
                        <div class="invalid-feedback">
                            <?php if ($title_err) {
                                echo $title_err;
                            } ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="inputSpeaker">Speaker</label>
                        <input type="text" class="form-control <?php if ($speaker_err) {echo('is-invalid');} ?>" id="inputSpeaker" name="speaker" placeholder="" value="<?php echo($speaker); ?>">
                        <div class="invalid-feedback">
                        <?php if ($speaker_err) {
                                echo $speaker_err;
                            } ?>
                        </div>
                    </div>
                    
                </div>

                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="selectRoom">Room</label>
                        <select class="form-control" id="selectRoom" name="room">
                            <?php
                                $rooms_result = mysqli_query($connection, "SELECT * FROM `rooms` WHERE `channel_id` IN (SELECT `id` FROM `channels` WHERE `event_id` = '{$event['id']}')");

                                while ($row = mysqli_fetch_assoc($rooms_result)) {
                                    $id = $row['id'];
                                    $name = $row['name'];
                                    $channel_id = $row['channel_id'];
                                    
                                    $channel_result = mysqli_query($connection, "SELECT * FROM `channels` WHERE `id` = '$channel_id'");
                                    $channel = mysqli_fetch_assoc($channel_result);

                                    
                                    echo '
                                        <option value="'.$id.'">'.$name.' / '.$channel['name'].'</option>
                                    ';
                                }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="inputCost">Cost</label>
                        <input type="number" class="form-control" id="inputCost" name="cost" placeholder="" value="0">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-6 mb-3">
                        <label for="inputStart">Start</label>
                        <input type="text"
                               class="form-control <?php if ($start_err || $booked_err) {echo('is-invalid');} ?>"
                               id="inputStart"
                               name="start"
                               placeholder="yyyy-mm-dd HH:MM"
                               value="<?php echo($start); ?>">
                        <div class="invalid-feedback">
                            <?php
                            if ($start_err) {
                                echo $start_err;
                            } 
                            if ($booked_err && !$start_err && !$end_err) {
                                echo $booked_err;
                            } 
                            ?>
                        </div>
                    </div>
                    


                    <div class="col-12 col-lg-6 mb-3">
                        <label for="inputEnd">End</label>
                        <input type="text"
                               class="form-control <?php if ($end_err || $booked_err) {echo('is-invalid');} ?>"
                               id="inputEnd"
                               name="end"
                               placeholder="yyyy-mm-dd HH:MM"
                               value="<?php echo($end); ?>">
                        <div class="invalid-feedback">
                            <?php if ($end_err) {
                                echo $end_err;
                            } ?>
                        </div>
                    </div>
                    
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="textareaDescription">Description</label>
                        <textarea class="form-control <?php if ($description_err) {echo('is-invalid');} ?>" id="textareaDescription" name="description" placeholder="" rows="5"><?php echo($description); ?></textarea>
                        <div class="invalid-feedback">
                            <?php if ($description_err) {
                                echo $description_err;
                            } ?>
                        </div>
                    </div>
                    
                </div>

                <hr class="mb-4">
                <button class="btn btn-primary" name="submit" type="submit">Save session</button>
                <a href="events/detail.php?id=<?php echo $event['id']; ?>" class="btn btn-link">Cancel</a>
            </form>

        </main>
    </div>
</div>

</body>
</html>
