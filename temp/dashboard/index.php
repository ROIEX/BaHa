<?php
    include("../include/initializer.php");
    require_once 'db.php';

    $db = new DB();
    $hosLogStatusesData = $db->getHosLogStatuses();
    $dvirStatus = $db->getDvirStatus();
    $driverRating = $db->getDriverRating();
    $onTimePercentage = $db->getOnTimePercentage();

$shortHeader = "Y";
include(INC_PATH."/templates/header.php");
?>

<link href="<?=SCRIPT_PATH_ROOT?>dashboard/css/bootstrap.css" rel="stylesheet" type="text/css" />
<link href="<?=SCRIPT_PATH_ROOT?>dashboard/css/chartist.min.css" rel="stylesheet" type="text/css" />
<link href="<?=SCRIPT_PATH_ROOT?>dashboard/css/style.css" rel="stylesheet" type="text/css" />
<script>
    var hos_log_statuses_data = <?php echo json_encode($hosLogStatusesData['data']); ?>;
    var hos_log_statuses_labels = <?php echo json_encode($hosLogStatusesData['labels']); ?>;
    var dvir_status_data = <?php echo json_encode($dvirStatus['data']); ?>;
    var dvir_status_labels = <?php echo json_encode($dvirStatus['labels']); ?>;
    var driver_rating = <?php echo json_encode($driverRating); ?>;
    var on_time_percentage_data = <?php echo json_encode($onTimePercentage); ?>;
</script>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">HoS Log Status</h3>
                </div>
                <div class="panel-body">
                    <div id="hos-log-status" class="ct-chart ct-golden-section"></div>
                    <div class="legend">
                        <div class="row">
                            <div class="col-md-4">
                                <span class="a"></span> Complete
                            </div>
                            <div class="col-md-4">
                                <span class="b"></span> Violations
                            </div>
                            <div class="col-md-4">
                                <span class="c"></span> Not complete
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">On-time Percentage</h3>
                </div>
                <div class="panel-body">
                    <div id="on-time-percentage" class="ct-chart ct-golden-section"></div>
                    <div class="legend">
                        <div class="row">
                            <div class="col-md-3">
                                <span class="lightblue"></span> Unscheduled
                            </div>
                            <div class="col-md-3">
                                <span class="darkgray"></span> On-time
                            </div>
                            <div class="col-md-3">
                                <span class="gray"></span> Delayed
                            </div>
                            <div class="col-md-3">
                                <span class="darkblue"></span> TBD
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default dvir-status">
                <div class="panel-heading">
                    <h3 class="panel-title">DVIR Status</h3>
                </div>
                <div class="panel-body">
                    <div id="dvir-status" class="ct-chart ct-golden-section"></div>
                    <div class="legend">
                        <div class="row">
                            <div class="col-md-4">
                                <span class="a"></span> Complete
                            </div>
                            <div class="col-md-4">
                                <span class="c"></span> Not complete
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h6>Total defects reported</h6>
                            <h4><span class="label label-warning"><?php echo $dvirStatus['totals']['total']; ?></span></h4>
                        </div>
                        <div class="col-md-4 text-center">
                            <h6>Defects corrected</h6>
                            <h4><span class="label label-success"><?php echo $dvirStatus['totals']['complete']; ?></span></h4>
                        </div>
                        <div class="col-md-4 text-center">
                            <h6>Defects outstanding</h6>
                            <h4><span class="label label-danger"><?php echo $dvirStatus['totals']['not_complete']; ?></span></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default driver-ratings">
                <div class="panel-heading">
                    <h3 class="panel-title">Driver Ratings</h3>
                </div>
                <div class="panel-body">
                    <div id="driver-ratings" class="ct-chart ct-golden-section"></div>
                    <div class="legend">
                        <div class="row">
                            <div class="col-md-4">
                                <span class="a"></span> 3 weeks ago
                            </div>
                            <div class="col-md-4">
                                <span class="b"></span> 2 weeks ago
                            </div>
                            <div class="col-md-4">
                                <span class="c"></span> Current week
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Current Driver Activity</h3>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Driver</th>
                            <th>Status</th>
                            <th>BOL #</th>
                            <th>Weeks Hours</th>
                            <th>Miles</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php
                            $currentDriverActivity = $db->getCurrentDriverActivity();

                            foreach ($currentDriverActivity as $row) {
                                echo '<tr>
                                      <td>' . $row['driver_name'] . '</td>
                                      <td>' . $row['driver_status'] . '</td>
                                      <td>' . $row['bol_no'] . '</td>
                                      <td>' . $row['weeks_hours'] . '</td>
                                      <td>' . $row['miles'] . '</td>
                                      </tr>';
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Payments</h3>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Week Ending</th>
                            <th>Revenue Earned</th>
                            <th>Paid</th>
                            <th>Invoice Outstanding</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php
                        $payments = $db->getPayments();

                        foreach ($payments as $row) {
                            echo '<tr>
                                  <td>' . $row['week_ending_formatted'] . '</td>
                                  <td>$' . number_format($row['revenue_earned'], 0) . '</td>
                                  <td>$' . number_format($row['paid'], 0) . '</td>
                                  <td>' . $row['invoices_outstanding'] . ' / ' . $row['invoices_total'] . '</td>
                                  </tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?=SCRIPT_PATH_ROOT?>dashboard/js/jquery-2.1.3.min.js"></script>
<script src="<?=SCRIPT_PATH_ROOT?>dashboard/js/bootstrap.js"></script>
<script src="<?=SCRIPT_PATH_ROOT?>dashboard/js/chartist.js"></script>
<script src="<?=SCRIPT_PATH_ROOT?>dashboard/js/main.js"></script>