<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    setcookie("user_id", $user_id, time() + (86400 * 30), "/");
    $month = $_POST['month'];
    $year = $_POST['year'];
    
    $excel_file = "../TourFiles/Schedules/"."$year-$month.xls";
    
    $spreadsheet = IOFactory::load($excel_file);
    $worksheet = $spreadsheet->getActiveSheet();
    
    $calendar = Calendar::create("Personal Calendar");
    
    for ($row = 3; $row <= 33; $row++) {
        $day = $worksheet->getCell("B$row")->getValue();
        if (empty($day)) continue;
        
        for ($col = 3; $col <= 120; $col++) {

            $cell_value = $worksheet->getCell(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . "$row")->getValue();

            /*
            echo(strtoupper(trim($cell_value)));
            echo(strtoupper(trim($user_id)));
            echo("\n <br><br>");
            echo($col);
            echo("\n");*/

            if (strpos(strtoupper(trim($cell_value)), strtoupper(trim($user_id))) !== false){
                $col_name = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $time_type = $worksheet->getCell("{$col_name}2")->getValue();
                $parts = explode(' ', $time_type);
                if (count($parts) < 2) continue;
                
                $time_str = $parts[0];
                $event_type = $parts[1];
                
               // $event_time = DateTime::createFromFormat('Y-m-d H:i', "$year-$month-$day $time_str");
                $event_time = new DateTime("$year-$month-$day $time_str", new DateTimeZone('Europe/Prague'));

                
                $event = Event::create()
                    ->name($event_type)
                    ->startsAt($event_time)
                    ->endsAt((clone $event_time)->modify('+2 hours'))
                    ->description("Personal ID: $user_id");
                
                $calendar->event($event);
            }
        }
    }
    
    $ics_filename = "calendar_{$user_id}_{$month}_{$year}.ics";
    file_put_contents($ics_filename, $calendar->get());
    
    header('Content-Type: text/calendar');
    header('Content-Disposition: attachment; filename="' . $ics_filename . '"');
    readfile($ics_filename);
    unlink($ics_filename);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar Generator</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="navigation-buttons">
    <a href="add_shift.php" class="nav-button">Add Shift</a>
    <a href="get_calendar.php" class="nav-button">Get shift calendar</a>
    <a href="generate_report.php" class="nav-button">Generate Report</a>
</div>

    <div class="container">
        <h1>Generate Shift Calendar</h1>
        <form method="POST">
            <label for="user_id">Personal ID:</label>
            <input type="text" id="user_id" name="user_id" value="<?php echo isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : ''; ?>" required><br>
            
            <label for="month">Month:</label>
            <select id="month" name="month">
                <option value="01">January</option>
                <option value="02">February</option>
                <option value="03">March</option>
                <option value="04">April</option>
                <option value="05">May</option>
                <option value="06">June</option>
                <option value="07">July</option>
                <option value="08">August</option>
                <option value="09">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select><br>
            
            <label for="year">Year:</label>
            <select id="year" name="year">
                <?php
                    $currentYear = date('Y');
                    echo "<option value='".($currentYear - 1)."'>".($currentYear - 1)."</option>";
                    echo "<option value='$currentYear' selected>$currentYear</option>";
                    echo "<option value='".($currentYear + 1)."'>".($currentYear + 1)."</option>";
                ?>
            </select><br>
            
            <button type="submit">Generate Calendar</button>
        </form>
    </div>
</body>
</html>
