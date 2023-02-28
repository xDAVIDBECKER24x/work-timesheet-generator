<?php

$start_work_time = "9:00";
$start_lunch_time = "11:00";
$hours_per_day = 8;
$hours_per_lunch = 1;
$employeer_name = "David_Carollo";
$time_disparity = 30; //Random values in work arrive 
$holydays_file = 'holydays_sp_br.csv';

//Create arrays to associate
$data_week = ["Mon" => 0, "Tue" => 1, "Wed" => 2, "Thu" => 3, "Fri" => 4, "Sat" => 5, "Sun" => 6];
$data_week_name = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
$data_week_number = [0, 1, 2, 3, 4,  5, 6];

//Get current date
$current_year = date('Y', strtotime('Y'));
$current_month = date('m', strtotime('m'));
$days_month_count = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);

//Get first day of current month
$current_date = mktime(0, 0, 0, $current_month, 1, $current_year);
$date = date(DATE_RFC1123, mktime(0, 0, 0, $current_month, 1, $current_year));
$date = trim(str_replace(",", "", $date));

// echo $date . PHP_EOL;

$data_date = explode(' ', $date);

$file = "Work_Timesheet_" . $employeer_name . "_" . $data_date[2] . "_" . $data_date[3] . ".csv";
$fl = fopen($file, 'w')  or die("Can't access file " . $file);

// var_dump($data_date);

$day = $data_date[1];
$day_week = $data_date[0];


$idx_day_week = intval($data_week[$day_week]);
$day_worked = 0;

fwrite($fl, "Day,Start Work,Lunch Time,End Lunch,End Work,Total Hours" . PHP_EOL);

$holydays = map_holidays($holydays_file);
$holydays_days = get_current_month_holydays_days($holydays);
var_dump($holydays_days);


//Loop trough days in current month skipping weekend days
for ($i = 0; $i < $days_month_count; $i++) {

    $generated_work_time = generate_time($start_work_time, $hours_per_day + $hours_per_lunch, $time_disparity);
    $generated_lunch_time = generate_time($start_lunch_time, $hours_per_lunch, $time_disparity);

    //Check Holydays
    if (array_key_exists($i + 1, $holydays_days)) {
        echo "Day : " . ($i + 1) . "- Week Day :" . $data_week_name[$idx_day_week] . " - " . $idx_day_week .  PHP_EOL;
        fwrite($fl, ($i + 1) . "," . $holydays_days[$i + 1] . "," . $holydays_days[$i + 1] . "," . $holydays_days[$i + 1] . "," . $holydays_days[$i + 1] . "," . 0 . PHP_EOL);
        $idx_day_week++;
    } else {
        if ($idx_day_week > 4) {
            if ($idx_day_week > 5) {
                echo "Day : " . ($i + 1) . "- Week Day :" . $data_week_name[$idx_day_week] . " - " . $idx_day_week .  PHP_EOL;
                fwrite($fl, ($i + 1) . ",,,,," . PHP_EOL);
                $idx_day_week = 0;
            } else {
                echo "Day : " . ($i + 1) . "- Week Day :" . $data_week_name[$idx_day_week] . " - " . $idx_day_week .  PHP_EOL;
                fwrite($fl, ($i + 1) . ",,,,," . PHP_EOL);
                $idx_day_week++;
            }
        } else {
            echo "Day : " . ($i + 1) . "- Week Day :" . $data_week_name[$idx_day_week] . " - " . $idx_day_week .  PHP_EOL;

            fwrite($fl, ($i + 1) . "," . $generated_work_time[0] . "," . $generated_lunch_time[0] . "," . $generated_lunch_time[1] . "," . $generated_work_time[1] . "," . $hours_per_day . PHP_EOL);
            $idx_day_week++;
            $day_worked++;
        }
    }
}

echo "Days Worked : " . $day_worked . PHP_EOL;
fwrite($fl, "Total: ,,,,," . ($day_worked * 8) . PHP_EOL);
fclose($fl);

//Fucntion to generate random times betwwen range
function generate_time($time, $range, $disparity)
{
    $limiter = strpos($time, ":");
    $hour_time_start = intval(substr($time, 0, $limiter + 1));
    $minutes_time_start = intval(substr($time, $limiter + 1, 2));

    $rnd =  random_int($minutes_time_start, ($minutes_time_start + $disparity));
    if ($rnd < 10) {
        $rnd = "0" . $rnd;
    } else {
        $rnd = strval($rnd);
    }

    $hour_time_end = $hour_time_start + $range;

    $time_start = strval($hour_time_start . ":" . $rnd);
    $time_end = strval($hour_time_end . ":" . $rnd);

    return [$time_start, $time_end];
}

function map_holidays($holydays_file)
{
    $rows   = array_map('str_getcsv', file($holydays_file));
    $header = array_shift($rows);
    $holydays    = array();
    foreach ($rows as $row) {
        $holydays[]  = array_combine($header, $row);
    }

    return $holydays;
}

function get_current_month_holydays_days($holydays)
{
    $current_month = date('m', strtotime('m'));
    $month_holydays = [];
    foreach ($holydays as $hol) {
        if ($current_month == $hol['month']) {
            $month_holydays[$hol['day']] = $hol['description'];
        }
    }
    return $month_holydays;
}
