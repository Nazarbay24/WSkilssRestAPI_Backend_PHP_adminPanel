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
    <!--   <link rel="stylesheet" type="text/css" href="reports/Chart.css">   -->
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
                    <li class="nav-item"><a class="nav-link active" href="<?php echo $_SERVER['REQUEST_URI'] ?>">Room capacity</a></li>
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
                    <h2 class="h4">Room Capacity</h2>
                </div>
            </div>

            <!-- TODO create chart here -->
            <?php
                $sessions_result = mysqli_query($connection, "SELECT * FROM `sessions` WHERE `room_id` IN (
                    SELECT `id` FROM `rooms` WHERE `channel_id` IN (
                    SELECT `id` FROM `channels` WHERE `event_id` = '{$event['id']}')) ORDER BY `start`");

                $sessions = array();
                $capacity = array();
                $attendes = array();

                while ($row = mysqli_fetch_assoc($sessions_result)) {
                    $id = $row['id'];
                    $title = $row['title'];
                    $room_id = $row['room_id'];

                    $room_result = mysqli_query($connection, "SELECT * FROM `rooms` WHERE `id` = '$room_id'");
                    $registrations_result = mysqli_query($connection, "SELECT * FROM `session_registrations` WHERE `session_id` = '$id'");

                    $sessions[] = $title;
                    $capacity[] = mysqli_fetch_assoc($room_result)['capacity'];
                    $attendes[] = mysqli_num_rows($registrations_result);
                }

                $sessions_json = json_encode($sessions);
                $capacity_json = json_encode($capacity);
                $attendes_json = json_encode($attendes);
            ?>


            <script type="text/javascript" src="reports/Chart.bundle.js"></script>

            <div id="chart_con">
                <canvas id="myChart"></canvas>
            </div>
            
            <style type="text/css">
                #chart_con {
                    width: 100%;
                    height: 400px;
                }
            </style>

            <script>
            var ctx = document.getElementById('myChart').getContext('2d');

            var sessions = JSON.parse('<?php echo $sessions_json; ?>');
            var capacity = JSON.parse('<?php echo $capacity_json; ?>');
            var attendes = JSON.parse('<?php echo $attendes_json; ?>');

            var color = [];

            for(let i=0; i < sessions.length; i++) {
                color.push('rgba(99, 132, 0, 0.6)');
            }

            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    datasets: [{
                        data: attendes,
                        label: 'Attendes',
                        backgroundColor: color,

                        // This binds the dataset to the left y axis
                        
                    }, {
                        data: capacity,
                        label: 'Capacity',
                        backgroundColor: 'rgba(0, 99, 132, 0.6)',

                        // This binds the dataset to the right y axis
                        
                    }],
                    labels: sessions
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                  scales: {
                    yAxes: [{
                      scaleLabel: {
                        display: true,
                        labelString: 'Capacity'
                      },
                      gridLines: {
                        display: false,
                      }
                    }],
                    xAxes: [{
                        gridLines: {
                        lineWidth: 3
                      }
                    }]
                  }
                }
            });


            var att_data = myChart.data.datasets[0];
            var cap_data = myChart.data.datasets[1];

            for(let i=0; i<att_data.data.length; i++) {
                if (att_data.data[i] > cap_data.data[i]) {

                    att_data.backgroundColor[i] = "rgba(242, 90, 90, 0.6)";
                }
            }
            myChart.update();

            </script>

        </main>
    </div>
</div>


</body>
</html>
