<?php
// Check if the "url" parameter is provided in the GET request
if (isset($_GET['url'])) {
    // Get the URL from the GET parameter
    $url = $_GET['url'];

    // Initialize a cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Execute the cURL session and fetch the data from the webcall URL
    $data = curl_exec($ch);

    // Close the cURL session
    curl_close($ch);

    // Serve the fetched data as the response
    header('Content-Type: application/json'); // Set the appropriate content type (change as needed)
    echo $data;
}
