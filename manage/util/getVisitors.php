<?php
/**
 * Created by PhpStorm.
 * User: abrainerd
 * Date: 3/1/2016
 * Time: 4:02 PM
 */

include "dbconnect.php";

$today = $_POST["searchDate"];
$branch = $_POST["branch"];
if ($today == "") $today = date("Y/m/d");

$slt = "SELECT * ";
$frm = "FROM SimpleVisitors ";
$whr = "WHERE location='$branch' AND visit_date = '$today' ";
$ord = "ORDER BY time_out ASC, time_in DESC";
$sql = $slt . $frm . $whr . $ord;

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    for ($statusTable = 0; $statusTable < 3; $statusTable++) {
        $slt = "SELECT *";
        $frm = " FROM SimpleVisitors sv ";
        $ljn = "LEFT JOIN TeamMembers tm on sv.team_id = tm.tid ";
        $whr = " WHERE location='$branch' AND visit_date = '$today' AND status=$statusTable ";
        $ord = "ORDER BY time_out ASC, time_in DESC";
        $sql = $slt . $frm . $ljn . $whr . $ord;
        switch ($statusTable) {
            case 0:
                $tableTitle = "Members Excitedly Waiting";
                break;
            case 1:
                $tableTitle = "Members Being aMAIZEd";
                break;
            case 2:
                $tableTitle = "Members Happily Serviced";
                break;
            default:
                $tableTitle = "something went wrong";
        }
        echo "<h2 class='tableTitle'>$tableTitle</h2>";

        $result = $conn->query($sql);
        echo "<div class='tableContainer' id='status$statusTable''>";
        if ($result->num_rows > 0) {
            echo "<div class='table' id='viewTable'>";
            buildTableHeader($statusTable);
            while ($row = $result->fetch_assoc()) {
                buildTableRow($statusTable, $row);
            }
            echo "</div>";  // table
        }
        echo "</div>"; // table container
        echo "<hr>";
    }
} else {
    echo "<h1>No Visitor Check-Ins Today</h1>";
}

$conn->close();

function buildTableHeader($table)
{
    echo "<div class='row viewHeader'>";
    echo "<div class='hcell'>Name</div>";
    if ($table == 0) {        // Status = Waiting
        echo "<div class='hcell time'>Time Waiting</div>";
        echo "<div class='hcell'>Reason</div>";
        echo "<div class='hcell'>Appointment With</div>";
    } else if ($table == 1) {   // Status = Being Helped
        echo "<div class='hcell time'>Time With MSR</div>";
        echo "<div class='hcell'>MSR</div>";
        echo "<div class='hcell'>Reason</div>";
    } else if ($table == 2) {   // Status = Done
        echo "<div class='hcell time'>Visit Length</div>";
        echo "<div class='hcell'></div>";
    }
    echo "</div>";
}
function buildTableRow($table, $row)
{
    $currentTime = time();
    $reason = $row["reason"];
    if (strlen($reason) > 30) {
        $reason = substr($reason, 0, 16) . "...";
    }
    $timeIn = strtotime($row["time_in"]);
    $timeHelp = strtotime($row["time_help"]);
    $timeOut = strtotime($row["time_out"]);
    $teamMember = $row["team_name"];
    if ($teamMember == "") $teamMember = $row["team_id"];
    $vid = $row["vid"]; // remove for live (probably)
    $status = $row['status'];
    if ($status == "2") $reason = $reason . " --> " . $row["note_text"];
    if ($table == 0) {
        $timeElapsed = gmdate("H:i:s", $currentTime - $timeIn);
        $hours = intval(substr($timeElapsed, 0, 2));
        $minutes = intval(substr($timeElapsed, 3, 5));
        $meetingWith = $row["meetingWith"] != "" ? $row["meetingWith"] : "-";
        echo "<div class='row' data-vid='$vid' data-status='$status'>";
        echo "<div class='cell'>" . $row["fname"] . " " . $row["lname"] . "</div>";
        echo "<div class='cell time'>";
        if ($hours > 0) echo "$hours hr ";
        echo "$minutes min</div>";
        echo "<div class='cell reason'>" . $reason . "</div>";
        echo "<div class='cell appointment'>" . $meetingWith . "</div>";
    } else if ($table == 1) {
        $timeElapsed = gmdate("H:i:s", $currentTime - $timeHelp);
        $hours = intval(substr($timeElapsed, 0, 2));
        $minutes = (intval(substr($timeElapsed, 3, 5)));
        echo "<div class='row' data-vid='$vid' data-status='$status'>";
        echo "<div class='cell'>" . $row["fname"] . " " . $row["lname"] . "</div>";
        echo "<div class='cell time'>";
        if ($hours > 0) echo "$hours hr ";
        echo "$minutes min</div>";
        echo "<div class='cell'>$teamMember</div>";
        echo "<div class='cell reason'>" . $reason . "</div>";
    } else if ($table == 2) {
        $timeElapsed = gmdate("H:i:s", $timeOut - $timeIn);
        $hours = intval(substr($timeElapsed, 0, 2));
        $minutes = (intval(substr($timeElapsed, 3, 5)));
        echo "<div class='row noHover' data-vid='-1'>";
        echo "<div class='cell'>" . $row["fname"] . " " . $row["lname"] . "</div>";
        echo "<div class='cell time'>";
        if ($hours > 0) echo "$hours hr ";
        echo "$minutes min</div>";
        echo "<div class='cell'><input type='button' class='detailsButton' data-vid='$vid' value='Visit Details'/></div>";
    }
    echo "</div>";  // row
}