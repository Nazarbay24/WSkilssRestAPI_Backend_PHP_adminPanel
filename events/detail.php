<?php
    require_once('../db.php');

    if (!$_SESSION['logged']) {
        header('Location: /?not_logged=true');
    }
    
    $result = mysqli_query($connection, "SELECT * FROM `events` WHERE `id` = '{$_GET['id']}'");
    $event = mysqli_fetch_assoc($result);

    if ($event['organizer_id'] !== $_SESSION['logged']['id']) {
        header('Location: index.php?not_access=true');
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
    <style type="text/css">
    #message {
        transition: opacity 1.5s;
        position: fixed;
        z-index: 30;
        top: 100px;
        left: 30%;
        padding: 20px 45px;
        font-size: 28px;
        box-shadow: 0 0 15px rgba(0,0,0,.4);
        user-select: none;
    }
</style>

<?php
    if ($_GET['success'] == true) {
        echo '<div id="message" style="background: #9dff85;">Событие успешно создано!</div>';
    }
    if ($_GET['update'] == true) {
        echo '<div id="message" style="background: #9dff85;">Событие успешно обновлено!</div>';
    }
    if ($_GET['ticket_create'] == true) {
        echo '<div id="message" style="background: #9dff85;">Билет успешно создан!</div>';
    }
    if ($_GET['session_create'] == true) {
        echo '<div id="message" style="background: #9dff85;">Сессия успешно создана!</div>';
    }
    if ($_GET['session_update'] == true) {
        echo '<div id="message" style="background: #9dff85;">Сессия успешно обновлено!</div>';
    }
    if ($_GET['channel_create'] == true) {
        echo '<div id="message" style="background: #9dff85;">Канал успешно создан!</div>';
    }
    if ($_GET['room_create'] == true) {
        echo '<div id="message" style="background: #9dff85;">Комната успешно создана!</div>';
    }
?>

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
                    <li class="nav-item"><a class="nav-link active" href="<?php echo $_SERVER['REQUEST_URI'] ?>">Overview</a></li>
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
            <div class="border-bottom mb-3 pt-3 pb-2 event-title">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h1 class="h2"><?php echo $event['name']; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <a href="events/edit.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit event</a>
                        </div>
                    </div>
                </div>
                <span class="h6"><?php echo $event['date']; ?></span>
            </div>

            <!-- Tickets -->
            <div id="tickets" class="mb-3 pt-3 pb-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h2 class="h4">Tickets</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <a href="tickets/create.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                Create new ticket
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row tickets">
                <?php
                    $tickets_result = mysqli_query($connection, "SELECT * FROM `event_tickets` WHERE `event_id` = '{$event['id']}'");

                    while ($row = mysqli_fetch_assoc($tickets_result)) {
                        $name = $row['name'];
                        $cost = $row['cost'];
                        $special = $row['special_validity'];
                        $special = json_decode($special);

                        $special_value = '&nbsp';
                        
                        if ($special->type == 'date') {
                            $special_value = 'Available until '.$special->date;
                        } elseif ($special->type == 'amount') {
                            $special_value = $special->amount.' tickets available';
                        }

                        echo '
                        <div class="col-md-4">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">'.$name.'</h5>
                                    <p class="card-text">$'.$cost.'</p>
                                    <p class="card-text">'.$special_value.'</p>
                                </div>
                            </div>
                        </div>
                        ';
                    }
                ?>
            </div>

            <!-- Sessions -->
            <div id="sessions" class="mb-3 pt-3 pb-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h2 class="h4">Sessions</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <a href="sessions/create.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                Create new session
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive sessions">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Time</th>
                        <th>Type</th>
                        <th class="w-100">Title</th>
                        <th>Speaker</th>
                        <th>Channel</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                        $sessions_result = mysqli_query($connection, "SELECT * FROM `sessions` WHERE `room_id` IN (
                            SELECT `id` FROM `rooms` WHERE `channel_id` IN (
                            SELECT `id` FROM `channels` WHERE `event_id` = '{$event['id']}')) ORDER BY `start`");

                        while ($row = mysqli_fetch_assoc($sessions_result)) {
                            $title = $row['title'];
                            $room_id = $row['room_id'];
                            $speaker = $row['speaker'];
                            $start = date_parse($row['start']);
                            $end = date_parse($row['end']);
                            $type = $row['type'];
                            $cost = $row['cost'];

                            if ($start['hour'] < 10) {
                                $start['hour'] = '0'.$start['hour'];
                            }
                            if ($start['minute'] < 10) {
                                $start['minute'] = '0'.$start['minute'];
                            }
                            if ($end['hour'] < 10) {
                                $end['hour'] = '0'.$end['hour'];
                            }
                            if ($end['minute'] < 10) {
                                $end['minute'] = '0'.$end['minute'];
                            }



                            $time = $start['hour'].':'.$start['minute'].' - '.$end['hour'].':'.$end['minute'];

                            $room_result = mysqli_query($connection, "SELECT * FROM `rooms` WHERE `id` = '$room_id'");
                            $room = mysqli_fetch_assoc($room_result);
                            $channel_result = mysqli_query($connection, "SELECT * FROM `channels` WHERE `id` = '{$room['channel_id']}'");
                            $channel = mysqli_fetch_assoc($channel_result);

                            
                            echo '
                                <tr>
                                <td class="text-nowrap">'.$time.'</td>
                                <td>'.$type.'</td>
                                <td><a href="sessions/edit.php?id='.$row['id'].'&event_id='.$event['id'].'">'.$title.'</a></td>
                                <td class="text-nowrap">'.$speaker.'</td>
                                <td class="text-nowrap">'.$channel['name'].' / '.$room['name'].'</td>
                                </tr>
                            ';
                        }
                    ?>
                    </tbody>
                </table>
            </div>

            <!-- Channels -->
            <div id="channels" class="mb-3 pt-3 pb-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h2 class="h4">Channels</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <a href="channels/create.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                Create new channel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row channels">
                <?php
                    $channels_result = mysqli_query($connection, "SELECT * FROM `channels` WHERE `event_id` = '{$event['id']}'");

                    while ($row = mysqli_fetch_assoc($channels_result)) {
                        $id = $row['id'];
                        $name = $row['name'];

                        $rooms_res = mysqli_query($connection, "SELECT * FROM `rooms` WHERE `channel_id` = '$id'");
                        $sessions_res = mysqli_query($connection, "SELECT * FROM `sessions` WHERE `room_id` IN (SELECT `id` FROM `rooms` WHERE `channel_id` = '$id')");
                        
                        $rooms_count = mysqli_num_rows($rooms_res);
                        $sessions_count = mysqli_num_rows($sessions_res);

                        if ($rooms_count > 1) {
                            $rooms_count = $rooms_count.' rooms';
                        } else {
                            $rooms_count = $rooms_count.' room';
                        }

                        if ($sessions_count > 1) {
                            $sessions_count = $sessions_count.' sessions';
                        } else {
                            $sessions_count = $sessions_count.' session';
                        }

                        echo '
                        <div class="col-md-4">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">'.$name.'</h5>
                                    <p class="card-text">'.$sessions_count.', '.$rooms_count.'</p>
                                </div>
                            </div>
                        </div>
                        ';
                    }
                ?>
            </div>

            <!-- Rooms -->
            <div id="rooms" class="mb-3 pt-3 pb-2">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                    <h2 class="h4">Rooms</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <a href="rooms/create.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                Create new room
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive rooms">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Capacity</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php
                        $rooms_result = mysqli_query($connection, "SELECT * FROM `rooms` WHERE `channel_id` IN (SELECT `id` FROM `channels` WHERE `event_id` = '{$event['id']}')");

                        while ($row = mysqli_fetch_assoc($rooms_result)) {
                            $name = $row['name'];
                            $capacity = $row['capacity'];

                            echo '
                            <tr>
                                <td>'.$name.'</td>
                                <td>'.$capacity.'</td>
                            </tr>
                            ';
                        }
                    ?>

                    </tbody>
                </table>
            </div>

        </main>
    </div>
</div>

<script type="text/javascript">
    
    function ss() {
        let message = document.getElementById('message');
        message.style.opacity = 0;

        function dd() {
            message.style.display = 'none';
        }
        setTimeout(dd, 1500);
    }
    if (document.getElementById('message')) {
        setTimeout(ss, 2000);
    }
    
</script>

</body>
</html>
