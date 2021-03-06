<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload New Video</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="/css/reset.css"/>
    <link rel="stylesheet" href="/css/font.css"/>
    <link rel="stylesheet" href="css/video.css"/>
    <link rel="stylesheet" href="css/ripple.css"/>
</head>
<body>
<header>
    <h1 id='title'>Upload New Video</h1>
    <?php include "header.php"; ?>
</header>
<div id="view">
    <div id="uploadContainer">
        <form id="uploadForm" action="util/upload.php" method="post" enctype="multipart/form-data">
            <div id="chooseVideo" class="button">Select Video to Upload</div>
            <input type="file" id="videoUpload" name="videoUpload"/>
            <label for="videoTitle">Video Title</label>
            <input type="text" id="videoTitle" name="videoTitle"/>
            <label for="authLevel">Auth Level</label>
            <select id="authLevel" name="authLevel">
                <option value="10">All Team</option>
                <option value="50">IT Only</option>
            </select>
            <label for="releaseDate">Relase Date</label>
            <input type="date" id="releaseDate" name="releaseDate"/>
            <input type="submit" id="uploadButton" class="button" value="Upload Video" name="submit">
            <div id="progress-div">
                <div id="progress-bar"></div>
            </div>
            <div id="targetLayer"></div>
        </form>
        <div id="uploading" class='uil-ripple-css'>
            <div></div>
            <div></div>
        </div>
    </div>
    <div style="display: none;">Icons made by
        <a href="http://www.freepik.com" title="Freepik">Freepik</a> from
        <a href="http://www.flaticon.com" title="Flaticon">www.flaticon.com
        </a> is licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0"
                               target="_blank">CC 3.0 BY</a>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="js/upload.js"></script>
<script src="js/auth.js"></script>
</body>
</html>