<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}

require_once "../db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = filter_input(
        INPUT_POST,
        "id",
        FILTER_VALIDATE_INT
    );

    if ($id) {

        $stmt = $conn->prepare(
            "SELECT file_path FROM certificates WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param("i", $id);
            $stmt->execute();

            $cert = $stmt->get_result()->fetch_assoc();

            $stmt->close();

            if ($cert) {

                $stmt = $conn->prepare(
                    "DELETE FROM certificates WHERE id = ?"
                );

                if ($stmt) {

                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();

                    // Remove the uploaded file as well
                    $file = __DIR__ . "/../uploads/certificates/"
                        . basename($cert["file_path"]);

                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }
}

header("Location: dashboard.php");

exit;

?>
