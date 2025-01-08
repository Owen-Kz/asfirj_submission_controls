<?php

include "../cors.php";
include "../db.php";
include "./isAdminAccount.php";

$data = json_decode(file_get_contents("php://input"), true);
$adminId = $data["admin_id"];
if (isset($adminId)) {
    $isAdminAccount = isAdminAccount($adminId);
    if ($isAdminAccount) {
        $stmt = $con->prepare("SELECT s.*
                    FROM submissions s
                    INNER JOIN (
                        SELECT 
                            article_id,
                            title,
                            MAX(revision_id) AS max_revision_id
                        FROM submissions
                        GROUP BY article_id, title
                    ) grouped
                    ON s.article_id = grouped.article_id
                    AND s.title = grouped.title
                    AND s.revision_id = grouped.max_revision_id
                    ORDER BY s.id DESC;
                    ");
        if (!$stmt) {
            echo json_encode(array("error" => $stmt->error));
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $submissions = array();

        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
                // Check if $value is not null
                if ($value !== null) {
                    $row[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                } else {
                    // You can also set a default value or keep it as null if needed
                    $row[$key] = ''; // or null
                }
            }
            $submissions[] = $row;
        }


        // var_dump($submissions);

        $json = json_encode(array("success" => "Admin Account", "submissions" => $submissions));
        if ($json === false) {
            echo json_last_error_msg();
        } else {
            echo $json;
        }

    } else {
        echo json_encode(array("error" => "Not Admin Account"));
    }
} else {
    echo json_encode(array("error" => "Invalid Parameters"));
}