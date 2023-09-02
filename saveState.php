<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_FILES["state"]) && isset($_FILES["screenshot"]) && isset($_POST["gameName"])) {
        $gameName = $_POST["gameName"]; // Retrieve the gameName
        $stateFile = $_FILES["state"]["tmp_name"];
        $screenshotFile = $_FILES["screenshot"]["tmp_name"];

        // Read state data using fread
        $stateData = "";
        $stateHandle = fopen($stateFile, "rb");
        while (!feof($stateHandle)) {
            $stateData .= fread($stateHandle, 8192); // Read in chunks of 8KB
        }
        fclose($stateHandle);

        // Read screenshot data using fread
        $screenshotData = "";
        $screenshotHandle = fopen($screenshotFile, "rb");
        while (!feof($screenshotHandle)) {
            $screenshotData .= fread($screenshotHandle, 8192);
        }
        fclose($screenshotHandle);

        // Save the data to files with dynamic filenames
        file_put_contents("./saves/{$gameName}.state", $stateData, LOCK_EX);
        file_put_contents("./img/{$gameName}.png", $screenshotData, LOCK_EX);

        echo "Data saved successfully!";
    } else {
        echo "Invalid data received.";
    }
}
?>