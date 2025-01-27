<?php
// Include the PhpSpreadsheet library
require_once 'vendor/autoload.php';
require_once 'get_rate.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Set output format control variable
$is_output_csv = false;

// Set the cookies on form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = strtoupper($_POST['id'] ?? '');
    if (!empty($id)) {
        setcookie('last_used_id', $id, time() + (86400 * 30), "/"); // Expires in 30 days
    }

    $month = $_POST['month'] ?? '';
    $year  = $_POST['year'] ?? '';

    if (empty($id) || empty($month) || empty($year)) {
        echo "<p class='error'>Please select an ID, month, and year.</p>";
    } else {
        $csvFilename = "../TourFiles/$id-$year-$month.csv";

        if (!file_exists($csvFilename)) {
            echo "<p class='error'>A file with this ID for this month does not exist.</p>";
            exit;
        }

        $reported_values = array_fill(0, 31, "");
        if (($handle = fopen($csvFilename, "r")) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $shift_type = $row[0] ?? '';
                $date       = $row[1] ?? '';
                $time       = $row[2] ?? '';
                $ppl        = $row[3] ?? 0;
                $bonus      = $row[4] ?? 'false';
                $fewer      = $row[5] ?? 'false';

                $day = (int) date("d", strtotime($date));
                $rate = get_rate($shift_type, $ppl, $bonus, $fewer);
                $reported_values[$day - 1] .= "$time;$ppl;$rate;";
            }
            fclose($handle);
        }

        $csv_report = implode("\n", $reported_values);

        if ($is_output_csv) {
            // Output as CSV file
            $csv_file = "../TourFiles/csv_report.csv";
            file_put_contents($csv_file, $csv_report);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="csv_report.csv"');
            readfile($csv_file);
            exit;
        } else {
            // Output as Excel file
            $templateFile = 'report_template.xlsx';
            $outputFile = "../TourFiles/$id-$year-$month.xlsx";

            if (!file_exists($templateFile)) {
                echo "<p class='error'>The template file does not exist, please contact the administrator.</p>";
                exit;
            }

            // Copy the template file to the new file
            if (!copy($templateFile, $outputFile)) {
                echo "<p class='error'>Failed to copy the template file, please contact the administrator.</p>";
                exit;
            }

            // Load the copied file and populate it with data
            $spreadsheet = IOFactory::load($outputFile);
            $sheet = $spreadsheet->getActiveSheet();

            // Populate the spreadsheet with data
            $rows = explode("\n", $csv_report);
            foreach ($rows as $rowIndex => $row) {
                if (trim($row) === '') continue; // Skip empty rows
                $columns = explode(";", $row);
                foreach ($columns as $colIndex => $value) {
                    $sheet->setCellValue([$colIndex + 2, $rowIndex + 3], $value); // Offset by 2 rows and 1 column
                }
            }

            $dateObj   = DateTime::createFromFormat('!m', $month);
            $sheet->setCellValue('D1', $dateObj->format('F'));
            $sheet->setCellValue('G1', $id);

            // Save the updated spreadsheet
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($outputFile);

            // Send the updated file for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"$id-$year-$month.xlsx\"");
            readfile($outputFile);
            unlink($outputFile);
            exit;
        }
    }
}

// Load the "last_used_id" cookie
$last_used_id = $_COOKIE['last_used_id'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Report</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="navigation-buttons">
    <a href="add_shift.php" class="nav-button">Add Shift</a>
    <a href="get_calendar.php" class="nav-button">Get shift calendar</a>
    <a href="generate_report.php" class="nav-button">Generate Report</a>
</div>

<div class="container">
    <h2>Generate Report</h2>
    
    <form method="POST" action="" onsubmit="return validateForm()">
        <!-- ID short text input -->
        <label for="id">ID:</label>
        <input type="text" name="id" id="id" maxlength="3" value="<?= htmlspecialchars($last_used_id) ?>" />
        <br>

        <label for="month">Month:</label>
        <select name="month" id="month">
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
        </select>
        <br>

        <label for="year">Year:</label>
        <select name="year" id="year"></select>
        <br>

        <button type="submit">Generate Report</button>
    </form>
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    const now = new Date();
    const currentMonth = String(now.getMonth() + 1).padStart(2, '0');
    const currentYear  = now.getFullYear();

    document.getElementById('month').value = currentMonth;

    const yearSelect = document.getElementById('year');
    const yearRangeStart = currentYear - 1;
    const yearRangeEnd   = currentYear + 2;

    for (let y = yearRangeStart; y <= yearRangeEnd; y++) {
        const option = document.createElement('option');
        option.value = y;
        option.text  = y;
        yearSelect.add(option);
    }

    yearSelect.value = currentYear;
});

function validateForm() {
    const id    = document.getElementById('id').value.trim();
    const month = document.getElementById('month').value.trim();
    const year  = document.getElementById('year').value.trim();

    // Capitalize the ID
    if (idInput) {
        idInput.value = idInput.value.trim().toUpperCase();
    }

    // Validate ID: must be exactly 3 characters
    if (!id || id.length !== 3) {
        alert("Please enter a valid ID (exactly 3 characters).");
        return false;
    }

    // Validate Month and Year
    if (!month) {
        alert("Please select a month.");
        return false;
    }
    if (!year) {
        alert("Please select a year.");
        return false;
    }
    return true;
}
</script>
</body>
</html>
