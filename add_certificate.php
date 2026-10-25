<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}

require_once "../db.php";

$error = "";

$allowed_types = [
    "image/jpeg"      => "jpg",
    "image/png"       => "png",
    "image/webp"      => "webp",
    "application/pdf" => "pdf",
];

$max_size = 5 * 1024 * 1024; // 5 MB

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $issuer = trim($_POST["issuer"] ?? "");
    $date_earned = trim($_POST["date_earned"] ?? "");
    $file = $_FILES["certificate"] ?? null;

    if ($title === "") {

        $error = "Certificate title is required.";

    } elseif (!$file || $file["error"] === UPLOAD_ERR_NO_FILE) {

        $error = "Please choose a file to upload.";

    } elseif ($file["error"] !== UPLOAD_ERR_OK) {

        $error = "The file could not be uploaded. It may be too large.";

    } elseif ($file["size"] > $max_size) {

        $error = "The file must be 5 MB or smaller.";

    } else {

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file["tmp_name"]);

        if (!isset($allowed_types[$mime])) {

            $error = "Only JPG, PNG, WEBP and PDF files are allowed.";

        } else {

            $folder = __DIR__ . "/../uploads/certificates/";

            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            $filename = bin2hex(random_bytes(16))
                . "." . $allowed_types[$mime];

            if (move_uploaded_file(
                $file["tmp_name"],
                $folder . $filename
            )) {

                $file_path = "uploads/certificates/" . $filename;

                $stmt = $conn->prepare(
                    "INSERT INTO certificates
                    (title, issuer, date_earned, file_path)
                    VALUES (?, ?, ?, ?)"
                );

                if ($stmt) {

                    $stmt->bind_param(
                        "ssss",
                        $title,
                        $issuer,
                        $date_earned,
                        $file_path
                    );

                    if ($stmt->execute()) {

                        $stmt->close();

                        header("Location: dashboard.php");
                        exit;

                    } else {

                        unlink($folder . $filename);

                        $error = "Unable to save the certificate.";
                    }

                    $stmt->close();

                } else {

                    unlink($folder . $filename);

                    $error = "Database error. Please try again.";
                }

            } else {

                $error = "Unable to save the uploaded file.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Certificate</title>

    <link rel="stylesheet" href="../style.css">

</head>

<body>

<header class="admin-navbar">

    <h2>
        Add Certificate
    </h2>

    <a href="dashboard.php">
        Dashboard
    </a>

</header>


<main class="form-container">

    <h1>
        Add New Certificate
    </h1>


    <?php if ($error !== ""): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form method="POST" enctype="multipart/form-data">

        <label>
            Certificate Title
        </label>

        <input
            type="text"
            name="title"
            placeholder="Web Development Certificate"
            required
        >


        <label>
            Issued By
        </label>

        <input
            type="text"
            name="issuer"
            placeholder="Coursera, TESDA, DICT..."
        >


        <label>
            Date Earned
        </label>

        <input
            type="text"
            name="date_earned"
            placeholder="March 2026"
        >


        <label>
            Certificate File
        </label>

        <input
            type="file"
            name="certificate"
            accept=".jpg,.jpeg,.png,.webp,.pdf"
            required
        >

        <span class="form-hint">
            JPG, PNG, WEBP or PDF. Maximum 5 MB.
        </span>


        <button
            type="submit"
            class="btn"
        >
            Add Certificate
        </button>

    </form>

</main>

</body>
</html>
