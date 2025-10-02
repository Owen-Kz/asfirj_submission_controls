<?php

include "../cors.php";
include "../db.php";

session_start();

$data = json_decode(file_get_contents("php://input"), true);

// Access the values
$email_post = $data['email'];
$pass = $data['pass'];

if (isset($pass) && isset($email_post)) {

    $userID = mysqli_real_escape_string($con, $email_post);

    $stmt = $con->prepare("SELECT * FROM `authors_account` WHERE `email` = ? LIMIT 1");
    if (!$stmt) {
        echo json_encode(array("error" => $con->error));
        exit;
    }
    $stmt->bind_param("s", $userID);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $count = mysqli_num_rows($result);

        if ($result && $count > 0) {
            // Get and verify the user’s password if the account exists  
            $row = mysqli_fetch_array($result);
            $storedHashedPassword = $row["password"];
            $userMainID = $row["id"];

            if ($row["account_status"] !== "verified") {
                $response = array(
                    'status' => 'error',
                    'message' => 'Account Not verified, Check your email to verify your account',
                    'user_data' => "[]"
                );
                echo json_encode($response);
                exit;
            }

            if (password_verify($pass, $storedHashedPassword)) {

                // 🔒 Rehash password if needed (e.g., old $2y$ → new $2b$)
                if (password_needs_rehash($storedHashedPassword, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($pass, PASSWORD_DEFAULT);

                    $updateStmt = $con->prepare("UPDATE `authors_account` SET `password`=? WHERE `id`=?");
                    if ($updateStmt) {
                        $updateStmt->bind_param("si", $newHash, $userMainID);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }
                }

                $_SESSION["user_id"] = $row["id"];
                $_SESSION["user_email"] = $row["email"];

                $ip_add = getenv("REMOTE_ADDR");

                $response = array(
                    'status' => 'success',
                    'message' => 'Logged in successfully',
                    'user_data' => $row,
                    'ip' => $ip_add,
                    'userEmail' => md5($userMainID)
                );
                echo json_encode($response);
            } else {
                $response = array('status' => 'error', 'message' => 'Invalid Credentials', 'user_data' => "[]");
                echo json_encode($response);
            }
        } else {
            $response = array('status' => 'error', 'message' => 'User not found', 'user_data' => '$row');
            echo json_encode($response);
        }
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    $response = array('status' => 'error', 'message' => 'Fill all fields', 'user_data' => '$row');
    echo json_encode($response);
}
