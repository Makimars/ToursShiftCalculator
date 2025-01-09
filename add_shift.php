<?php
// Set the cookies on form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = strtoupper($_POST['id'] ?? '');
    $shift_type = $_POST['shift_type'] ?? '';

    // Set the "last_used_id" cookie
    if (!empty($id)) {
        setcookie('last_used_id', $id, time() + (86400 * 30), "/"); // Expires in 30 days
    }

    // Set the "last_shift_type" cookie
    if (!empty($shift_type)) {
        setcookie('last_shift_type', $shift_type, time() + (86400 * 30), "/"); // Expires in 30 days
    }

    // Other form processing...
    $date         = $_POST['date'] ?? '';
    $time         = $_POST['time'] ?? '';
    $people_count = (int) ($_POST['people_count'] ?? 0);
    $bonus_day    = isset($_POST['bonus_day']) ? 'true' : 'false';
    $fewer_people = isset($_POST['fewer_people']) ? 'true' : 'false';

    $dateParts = explode('-', $date);
    $year  = $dateParts[0] ?? 'unknownYear';
    $month = $dateParts[1] ?? 'unknownMonth';

    $filename = "../TourFiles/$id-$year-$month.csv";
    $file = fopen($filename, 'a');
    $data = [$shift_type, $date, $time, $people_count, $bonus_day, $fewer_people];
    fputcsv($file, $data);
    fclose($file);

    echo "<div class='notification'>Shift successfully added!</div>";
}

// Load the cookies
$last_used_id = $_COOKIE['last_used_id'] ?? '';
$last_shift_type = $_COOKIE['last_shift_type'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add a Shift</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="navigation-buttons">
    <a href="add_shift.php" class="nav-button">Add Shift</a>
    <a href="generate_report.php" class="nav-button">Generate Report</a>
</div>

<div class="container">
    <h1>Add a Shift</h1>
    
    <form method="POST" action="" onsubmit="return validateForm()">
        <!-- ID text input -->
        <label for="id">ID:</label>
        <input type="text" name="id" id="id" maxlength="3" value="<?= htmlspecialchars($last_used_id) ?>" />
        <br>
        
        <!-- Shift Type dropdown -->
        <label for="shift_type">Shift Type:</label>
        <select name="shift_type" id="shift_type">
            <option value="">--Select a Type--</option>
            <option value="PUT" <?= $last_shift_type === 'PUT' ? 'selected' : '' ?>>PUT</option>
            <option value="GHO" <?= $last_shift_type === 'GHO' ? 'selected' : '' ?>>GHO</option>
            <option value="BUN" <?= $last_shift_type === 'BUN' ? 'selected' : '' ?>>BUN</option>
        </select>
        <br>
        
        <!-- Date input -->
        <label for="date">Date:</label>
        <input type="date" name="date" id="date" />
        <br>
        
        <!-- Time -->
        <label for="time">Time:</label>
        <input type="text" name="time" id="time" />
        <br>
        
        <!-- People Count -->
        <label for="people_count">People Count:</label>
        <input type="number" name="people_count" id="people_count" min="0" max="40" />
        <br>
        
        <!-- Bonus Day -->
        <div class="checkbox-group">
            <input type="checkbox" name="bonus_day" id="bonus_day" />
            <label for="bonus_day">Bonus Day</label>
        </div>
        
        <!-- Fewer People -->
        <div class="checkbox-group">
            <input type="checkbox" name="fewer_people" id="fewer_people" />
            <label for="fewer_people">Fewer people in a split tour</label>
        </div>
        
        <button type="submit">Submit</button>
    </form>
</div>

<script>

// List of bonus days (use MM-DD format, e.g., '12-26')
const bonusDays = [
    '01-01', // New Year's Day
    '04-18', // Velký pátek
    '04-21', // Velikonoce
    '05-01', // 1. máj
    '05-08', // den vítězství
    '07-05', // Cyril a Metodej
    '07-06', // Jan Hus
    '09-28', // Sv. vaclav
    '10-28', // zalozeni csr
    '11-17', // Sametova revoluce
    '12-23', // 
    '12-26', // 
    '12-31', // silvestr 
    // Add more dates as needed
];

document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('date');
    const bonusDayCheckbox = document.getElementById('bonus_day');

    if (dateInput && bonusDayCheckbox) {
        // Add event listener for changes to the date input
        dateInput.addEventListener('change', () => {
            const selectedDate = dateInput.value; // Get selected date (YYYY-MM-DD)
            const monthDay = selectedDate.substring(5); // Extract MM-DD part
            if (bonusDays.includes(monthDay)) {
                bonusDayCheckbox.checked = true; // Tick the checkbox
            } else {
                bonusDayCheckbox.checked = false; // Untick the checkbox
            }
        });
    }
});

window.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('date');
    if (dateInput) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        dateInput.value = `${year}-${month}-${day}`;
    }
});

function validateForm() {
    const idInput = document.getElementById('id');
    const shiftTypeSelect = document.getElementById('shift_type');
    const peopleCountInput = document.getElementById('people_count');

     // Capitalize the ID
     if (idInput) {
        idInput.value = idInput.value.trim().toUpperCase();
    }

    // Validate ID: trimmed string must be exactly 3 characters
    const id = idInput.value.trim();
    if (id.length !== 3) {
        alert("ID must be exactly 3 characters.");
        return false;
    }

    // Validate Shift Type
    if (shiftTypeSelect.value === "") {
        alert("Please select a Shift Type.");
        return false;
    }

    // Validate People Count
    const peopleVal = parseInt(peopleCountInput.value, 10);
    if (isNaN(peopleVal) || peopleVal < 0 || peopleVal > 40) {
        alert("People Count must be between 0 and 40.");
        return false;
    }

    return true;
}
</script>
</body>
</html>
