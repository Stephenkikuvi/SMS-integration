<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    $message = $_POST['message'];
    $contacts = $_POST['contacts'];
    $callback = $_POST['callback'];
    $senderName = $_POST['sender_name'];

    if (empty($message) || empty($contacts) || empty($callback) || empty($senderName)) {
        echo "All fields are required.";
        exit;
    }

    $host = 'localhost';
    $username = "root";
    $password = "";
    $database = "auth";

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $sql = 'SELECT phone FROM profiles';
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $phoneNumbers = [];
        while ($row = $result->fetch_assoc()) {
            $phoneNumbers[] = $row['phone'];
        }

        $contacts = implode(',', $phoneNumbers);
    } else {
        echo 'No phone numbers found in the database';
        exit;
    }

    $conn->close();

    $postData = array(
        'contacts' => $contacts,
        'message' => $message,
        'callback' => $callback,
        'sender_name' => $senderName
    );

    $jsonData = json_encode($postData);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://swift.jambopay.co.ke/api/public/send-many');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Your bearer token from step 1',
        'Content-Type: application/json'
    ));

    $response = curl_exec($ch);

    if ($response === false) {
        echo 'Error: ' . curl_error($ch);
    } else {
        
        $responseData = json_decode($response, true);

        print_r($responseData);
    }

    curl_close($ch);
} else {
    echo "Invalid request method.";
}
?>
