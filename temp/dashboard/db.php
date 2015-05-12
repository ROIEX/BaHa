<?php

class DB
{
    private $_conn;
    private $_dbuser = 'root';
    private $_dbpass = 'root';
    private $_dbname = 'charts';
    private $_dbhost = 'localhost';
    private $_carrierId = 1;

    public function __construct() {
        if ($_SERVER['SERVER_NAME'] == 'trademarkassistant.com') {
            /*$this->_dbuser = 'trademar_dashusr';
            $this->_dbpass = 'v#XoCll7xm]k';
            $this->_dbname = 'trademar_dashboard';*/
            $this->_dbname = 'trademar_dvir';
            $this->_dbuser = 'trademar_dvirusr';
            $this->_dbpass = 'i}-8nRHBXn!1';

        }
        $this->_conn = new PDO('mysql:host=' . $this->_dbhost . ';dbname=' . $this->_dbname, $this->_dbuser, $this->_dbpass, array( PDO::ATTR_PERSISTENT => false));
    }

    public function getCurrentDriverActivity() {
        $sql = '
            SELECT current_driver_activity.*, drivers.name AS driver_name, driver_statuses.name AS driver_status
            FROM current_driver_activity
	            JOIN drivers ON current_driver_activity.driver_id = drivers.id AND current_driver_activity.carrier_id = drivers.carrier_id
                JOIN driver_statuses ON current_driver_activity.driver_status_id = driver_statuses.id
            WHERE `current_driver_activity`.`carrier_id` = ' . $this->_carrierId;

        $res = $this->_conn->query($sql);

        return $res;
    }

    public function getPayments() {
        $sql = 'SELECT *, DATE_FORMAT(`week_ending`, "%c/%e/%y") AS `week_ending_formatted` FROM payments WHERE `carrier_id` = ' . $this->_carrierId . ' ORDER BY `week_ending` ASC';

        $res = $this->_conn->query($sql);

        return $res;
    }

    public function getHosLogStatuses() {
        $sql = 'SELECT *, DATE_FORMAT(`date`, "%e %b") AS `date_formatted` FROM `hos_log_statuses` WHERE `carrier_id` = ' . $this->_carrierId . ' ORDER BY `date` ASC LIMIT 5';

        $data = array();
        $labels = array();
        $res = $this->_conn->query($sql);
        foreach ($res as $row) {
            $data['complete'][] = $row['complete'];
            $data['violations'][] = $row['violations'];
            $data['not_complete'][] = $row['not_complete'];
            $labels[] = $row['date_formatted'];
        }
        $dataOut = array();
        $dataOut[] = $data['complete'];
        $dataOut[] = $data['violations'];
        $dataOut[] = $data['not_complete'];

        return array('data' => $dataOut, 'labels' => $labels);
    }

    public function getDvirStatus() {
        // prepare sql statement to get data for past 5 days
        $sql = 'SELECT DATE_FORMAT(`date_time`, "%Y-%m-%d") AS `date_formatted`, (ISNULL(mechanics_signed_datetime) OR mechanics_signed_datetime = "0000-00-00") AS `not_completed`, Count(*) as `count`
                FROM `dvir_logs`
                WHERE `date_time` IS NOT NULL AND `date_time` > "0000-00-00" AND `carrier_id` = ' . $this->_carrierId . '
                GROUP BY DATE(`date_time`), (ISNULL(mechanics_signed_datetime) OR mechanics_signed_datetime = "0000-00-00")
                ORDER BY `date_time` DESC
                LIMIT 5';

        // read data
        $dbData = array();
        $res = $this->_conn->query($sql);
        foreach ($res as $row) {
            //$dbData[$row['date_formatted']] = array();

            if ($row['not_completed'] == 1) $dbData[$row['date_formatted']]['not_complete'] = (int)$row['count'];
            else $dbData[$row['date_formatted']]['complete'] = (int)$row['count'];
            //array('complete' => $row['complete'], 'not_complete' => $row['not_complete']);
        }

        // generate required dates (from today and 5 days before)
        $dateFrom = new DateTime();
        $curDate = clone $dateFrom;
        $dateOneDayPeriod = new DateInterval('P1D');
        $labels = array();
        $dataOut = array();

        $totals = array('complete' => 0, 'not_complete' => 0);
        for ($i = 0; $i < 5; $i++) {
            // generate current date id (ie 2015-01-01)
            $dateId = $curDate->format('Y-m-d');

            // look if data about current day is present in db and set it
            // otherwise set to 0 values
            if (isset($dbData[$dateId])) {
                $complete = (isset($dbData[$dateId]['complete']) ? $dbData[$dateId]['complete'] : 0);
                $notComplete = (isset($dbData[$dateId]['not_complete']) ? $dbData[$dateId]['not_complete'] : 0);

                $dataOut[0][] = $complete;
                $dataOut[1][] = 0; // violations set 0 to hide unneeded graph
                $dataOut[2][] = $notComplete;

                $totals['complete'] += $complete;
                $totals['not_complete'] += $notComplete;
            } else {
                // no information in DB found, we add 0 values
                $dataOut[0][] = 0;
                $dataOut[1][] = 0; // violations set 0 to hide unneeded graph
                $dataOut[2][] = 0;
            }

            $labels[] = $curDate->format('j M');

            // add one day for the date
            $curDate->sub($dateOneDayPeriod);
        }

        // reverse array to make today be on the right side of chart
        $dataOut[0] = array_reverse($dataOut[0]);
        $dataOut[1] = array_reverse($dataOut[1]);
        $dataOut[2] = array_reverse($dataOut[2]);
        $labels = array_reverse($labels);
        $totals['total'] = $totals['complete'] + $totals['not_complete'];

        return array('data' => $dataOut, 'labels' => $labels, 'totals' => $totals);
    }

    /*public function getDvirStatusTotals() {
        $sql = 'SELECT * FROM dvir_status_totals WHERE `carrier_id` = ' . $this->_carrierId;

        $res = $this->_conn->query($sql);
        $row = $res->fetch();

        return $row;
    }*/

    public function getDriverRating() {
        $sql = 'SELECT * FROM `driver_ratings` WHERE `carrier_id` = ' . $this->_carrierId .  ' ORDER BY `stars` ASC';

        $data = array();
        $res = $this->_conn->query($sql);
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $data['3weeks'][] = $row['3weeks'];
            $data['2weeks'][] = $row['2weeks'];
            $data['current_week'][] = $row['current_week'];
        }
        $dataOut = array();
        $dataOut[] = $data['3weeks'];
        $dataOut[] = $data['2weeks'];
        $dataOut[] = $data['current_week'];

        return $dataOut;
    }

    public function getOnTimePercentage() {
        $sql = 'SELECT * FROM `on_time_percentage` WHERE `carrier_id` = ' . $this->_carrierId;

        $res = $this->_conn->query($sql);
        $row = $res->fetch();

        $data = array(
            array('data' => (int)$row['unscheduled'], 'className' => 'chart-lightblue'),
            array('data' => (int)$row['ontime'], 'className' => 'chart-darkgray'),
            array('data' => (int)$row['delayed'], 'className' => 'chart-gray'),
            array('data' => (int)$row['tbd'], 'className' => 'chart-darkblue')
        );

        return $data;
    }
}