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
        $name = $_POST['name'];
        $cost = $_POST['cost'];
        $special_validity = $_POST['special_validity'];
        $amount = $_POST['amount'];
        $date = $_POST['date'];

        class Special {}
        $special_object = new Special();
        $special_validity_sql;

        $name_err = false;
        $cost_err = false;
        $amount_err = false;
        $date_err = false;

        $error = false;

        if ($name == '') {
            $name_err = 'Это поле обезятельное';
            $error = true;
        }
        if ($cost == 0) {
            $cost_err = 'Это поле обезятельное';
            $error = true;
        }
        if ($special_validity == 'none') {
            $special_validity_sql = null;
        }
        if ($special_validity == 'amount') {
            if ($amount == 0) {
               $amount_err = 'Это поле обезятельное';
               $error = true;
            } else {
                $special_object->type = 'amount';
                $special_object->amount = $amount;
                $special_validity_sql = json_encode($special_object);
            }
        }
        if ($special_validity == 'date') {
            if ($date == '') {
               $date_err = 'Это поле обезятельное';
               $error = true;
            } else {
                $special_object->type = 'date';
                $special_object->date = $date;
                $special_validity_sql = json_encode($special_object);
            }
        }


        if ($error == false) {
            mysqli_query($connection, "INSERT INTO `event_tickets`(`event_id`,`name`,`cost`,`special_validity`)
                VALUES('{$event['id']}','$name','$cost','$special_validity_sql')");

            header('Location: ../events/detail.php?id='.$event['id'].'&ticket_create=true');
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
                    <h2 class="h4">Create new ticket</h2>
                </div>
            </div>

            <form class="needs-validation" novalidate method="POST" action="">

                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="inputName">Name</label>
                        <!-- adding the class is-invalid to the input, shows the invalid feedback below -->
                        <input type="text" class="form-control <?php if ($name_err) {echo('is-invalid');} ?>" id="inputName" name="name" placeholder="" value="<?php echo($name); ?>">
                        <div class="invalid-feedback">
                            <?php if ($name_err) {
                                echo $name_err;
                            } ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="inputCost">Cost</label>
                        <input type="number" class="form-control <?php if ($cost_err) {echo('is-invalid');} ?>" id="inputCost" name="cost" placeholder="" value="<?php if ($cost) {echo($cost);} else {echo('0');} ?>">
                        <div class="invalid-feedback">
                            <?php if ($cost_err) {
                                echo $cost_err;
                            } ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="selectSpecialValidity">Special Validity</label>
                        <select class="form-control" id="selectSpecialValidity" name="special_validity">
                            <option value="none">None</option>
                            <option value="amount" <?php if ($_POST['special_validity'] == 'amount') {
                                echo "selected";
                            } ?>>Limited amount</option>
                            <option value="date" <?php if ($_POST['special_validity'] == 'date') {
                                echo "selected";
                            } ?>>Purchaseable till date</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="inputAmount">Maximum amount of tickets to be sold</label>
                        <input type="number" class="form-control <?php if ($amount_err) {echo('is-invalid');} ?>" id="inputAmount" name="amount" placeholder="" value="<?php if ($amount) {echo($amount);} else {echo('0');} ?>">
                        <div class="invalid-feedback">
                            <?php if ($amount_err) {
                                echo $amount_err;
                            } ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-4 mb-3">
                        <label for="inputValidTill">Tickets can be sold until</label>
                        <input type="text"
                               class="form-control <?php if ($date_err) {echo('is-invalid');} ?>"
                               id="inputValidTill"
                               name="date"
                               placeholder="yyyy-mm-dd HH:MM"
                               value="<?php echo($date); ?>">

                        <div class="invalid-feedback">
                            <?php if ($date_err) {
                                echo $date_err;
                            } ?>
                        </div>
                    </div>
                </div>

                <hr class="mb-4">
                <button class="btn btn-primary" name="submit" type="submit">Save ticket</button>
                <a href="events/detail.php?id=<?php echo $event['id']; ?>" class="btn btn-link">Cancel</a>
            </form>

        </main>
    </div>
</div>

</body>
</html>
