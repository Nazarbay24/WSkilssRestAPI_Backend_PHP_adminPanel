<?php
    require_once('../db.php');

    if (!$_SESSION['logged']) {
        header('Location: /?not_logged=true');
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
        position: absolute;
        z-index: 10;
        top: 200px;
        left: 30%;
        padding: 20px 45px;
        font-size: 28px;
        box-shadow: 0 0 15px rgba(0,0,0,.4);
        user-select: none;
    }
</style>
    
        <?php
            if ($_GET['not_access'] == true) {
                echo '<div id="message" style="background: #f1f1f1;">У вас нет доступа к этому событию!</div>';
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
                    <li class="nav-item"><a class="nav-link active" href="events/">Manage Events</a></li>
                </ul>
            </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Events</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group mr-2">
                        <a href="events/create.php" class="btn btn-sm btn-outline-secondary">Create new event</a>
                    </div>
                </div>
            </div>

            <div class="row events">
                <?php
                    $result = mysqli_query($connection, "SELECT * FROM `events` WHERE `organizer_id` = '{$_SESSION['logged']['id']}' ORDER BY `date` ASC");
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $organizer_id = $row['organizer_id'];
                        $name = $row['name'];
                        $date = $row['date'];

                        $reg_count = 0;

                        $tickets = mysqli_query($connection, "SELECT * FROM `event_tickets` WHERE `event_id` = '$id'");
                        while ($row_tickets = mysqli_fetch_assoc($tickets)) {
                            $registrations = mysqli_query($connection, "SELECT * FROM `registrations` WHERE `ticket_id` = '{$row_tickets['id']}'");
                            $reg_count = $reg_count + mysqli_num_rows($registrations);
                        }

                        echo '
                        <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <a href="events/detail.php?id='.$id.'" class="btn text-left event">
                                <div class="card-body">
                                    <h5 class="card-title">'.$name.'</h5>
                                    <p class="card-subtitle">'.$date.'</p>
                                    <hr>
                                    <p class="card-text">'.$reg_count.' registrations</p>
                                </div>
                            </a>
                        </div>
                        </div>
                        ';
                    }
                ?>
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
