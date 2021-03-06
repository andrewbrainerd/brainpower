<?php
include "util/dbconnect.php";
session_start();
if (isset($_GET["maizenet"]) && !isset($_SESSION["username"])) {
    $_SESSION["username"] = "umcu";
    $_SESSION["authLv"] = "10";
    $_SESSION["activity"] = time();
    header("Refresh: 0");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UMCU Videos</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="/css/reset.css"/>
    <link rel="stylesheet" href="/css/font.css"/>
    <link rel="stylesheet" href="css/video.css"/>
    <style>
        <?php
        $today = date("Y/m/d");
        $al = 0;
        if (isset($_SESSION))  $al = $_SESSION['authLv'];
        if ($al > 10) $sql = "SELECT * FROM Videos where required_auth <= '$al' ORDER BY upload_time DESC";
        else $sql = "SELECT * FROM Videos where required_auth <= '$al' and (release_date <= '$today' OR release_date IS NULL) ORDER BY upload_time DESC";
        $result = $conn->query($sql);
        $numVid = $result->num_rows;
        if ($numVid > 8 && $al > 10) echo ".video { width: calc(100%/4); font-size: 0.9em; }";
        ?>
    </style>
</head>
<body>
<header>
    <h1 id='title'>UMCU Video Library</h1>
    <?php include "header.php"; ?>
</header>
<div id="view">
    <div id="timer"></div>
    <div id="library">
        <?php
        if ($numVid > 0) {
            while ($row = $result->fetch_assoc()) {
                $title = $row["title"];
                $path = $row["location"]; //"https://lobby.umcu.org/video/" .
                $vid = $row["vid"];
                $addClass = "";
                if ($al > 10) {
                    $rd = date_format(date_create($row["release_date"]), "Y/m/d");
                    if ($rd > $today || $rd == NULL) {
                        $addClass = " preview";
                    }
                    else if (!file_exists($path)) {
                        $addClass = " missingVideo";
                    }
                }
                $videoElement = "<div class='video" . $addClass . "' data-vid='" . $vid . "' data-path='" . $path . "'";
                $addClass = "";
                $videoElement .= ">" . $title;
                $videoElement .= "</div>";
                echo $videoElement . "\n\t    ";
            }
        } else echo "<h1>No Available Videos</h1>";
        ?>
    </div>
    <div id="player">
        <video id="video" width="1000" height="562" controls preload="auto" poster="../img/poster.png">
            Your browser does not support HTML5 video
        </video>
        <div class="button" id="back">Back To Library</div>
    </div>
</div>
<script src="/js/secure.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="js/auth.js"></script>
<script src="js/nav.js"></script>
<script src="js/player.js"></script>
</body>
</html>