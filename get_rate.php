<?php
function get_rate($shift_type, $ppl, $bonus, $fewer) {
    $hours_worked = 0;

    if ($shift_type === 'PUT' || $shift_type === 'GHO') {

        if ($bonus === 'false') {

            if ($ppl == 0) {
                $hours_worked = 1;
            } elseif ($ppl >= 1 && $ppl <= 2) {
                $hours_worked = 1.9;
            } elseif ($ppl >= 3 && $ppl <= 5) {
                $hours_worked = 2.1;
            } elseif ($ppl >= 6 && $ppl <= 10) {
                $hours_worked = 2.5;
            } elseif ($ppl >= 11 && $ppl <= 15) {
                $hours_worked = 2.8;
            } elseif ($ppl >= 16 && $ppl <= 20) {
                $hours_worked = 3.1;
            } elseif ($ppl >= 21 && $ppl <= 25) {
                $hours_worked = 3.4;
            } else { // $ppl >= 26
                $hours_worked = 3.7;
            }

        }else{

            if ($ppl == 0) {
                $hours_worked = 1.25;
            } elseif ($ppl >= 1 && $ppl <= 2) {
                $hours_worked = 2.45;
            } elseif ($ppl >= 3 && $ppl <= 5) {
                $hours_worked = 2.65;
            } elseif ($ppl >= 6 && $ppl <= 10) {
                $hours_worked = 3.15;
            } elseif ($ppl >= 11 && $ppl <= 15) {
                $hours_worked = 3.55;
            } elseif ($ppl >= 16 && $ppl <= 20) {
                $hours_worked = 3.95;
            } elseif ($ppl >= 21 && $ppl <= 25) {
                $hours_worked = 4.25;
            } else { // $ppl >= 26
                $hours_worked = 4.65;
            }
        }

        
    } elseif ($shift_type === 'BUN') {

        if ($bonus === 'false') {

            if ($ppl == 0) {
                $hours_worked = 1.3;
            } elseif ($ppl >= 1 && $ppl <= 2) {
                $hours_worked = 2.9;
            } elseif ($ppl >= 3 && $ppl <= 5) {
                $hours_worked = 3.3;
            } elseif ($ppl >= 6 && $ppl <= 10) {
                $hours_worked = 3.7;
            } elseif ($ppl >= 11 && $ppl <= 15) {
                $hours_worked = 4.1;
            } elseif ($ppl >= 16 && $ppl <= 20) {
                $hours_worked = 4.5;
            } elseif ($ppl >= 21 && $ppl <= 25) {
                $hours_worked = 4.9;
            } else { // $ppl >= 26
                $hours_worked = 5.3;
            }

        }else{

            if ($ppl == 0) {
                $hours_worked = 1.7;
            } elseif ($ppl >= 1 && $ppl <= 2) {
                $hours_worked = 3.65;
            } elseif ($ppl >= 3 && $ppl <= 5) {
                $hours_worked = 4.2;
            } elseif ($ppl >= 6 && $ppl <= 10) {
                $hours_worked = 4.7;
            } elseif ($ppl >= 11 && $ppl <= 15) {
                $hours_worked = 5.2;
            } elseif ($ppl >= 16 && $ppl <= 20) {
                $hours_worked = 5.65;
            } elseif ($ppl >= 21 && $ppl <= 25) {
                $hours_worked = 6.2;
            } else { // $ppl >= 26
                $hours_worked = 6.7;
            }
        }
    }

    // Adjust hours_worked based on bonus and fewer people
    
    if ($fewer === 'true') {
        $hours_worked += 0.15;
    }

    return $hours_worked;
}
?>
