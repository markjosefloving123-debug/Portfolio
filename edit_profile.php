<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}

require_once "../db.php";

$error = "";

$allowed_types = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/webp" => "webp",
];

$max_size = 5 * 1024 * 1024; // 5 MB

$folder = __DIR__ . "/../uploads/profile/";


/* Saves one uploaded picture. Returns the new path,
   or null when nothing was uploaded or an error occurred. */

function upload_picture(
    ?array $file,
    array $allowed_types,
    int $max_size,
    string $folder,
    string &$error
): ?string {

    if (!$file || $file["error"] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        $error = "A file could not be uploaded. It may be too large.";
        return null;
    }

    if ($file["size"] > $max_size) {
        $error = "Each picture must be 5 MB or smaller.";
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    if (!isset($allowed_types[$mime])) {
        $error = "Only JPG, PNG and WEBP pictures are allowed.";
        return null;
    }

    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    $filename = bin2hex(random_bytes(16))
        . "." . $allowed_types[$mime];

    if (!move_uploaded_file($file["tmp_name"], $folder . $filename)) {
        $error = "Unable to save the uploaded picture.";
        return null;
    }

    return "uploads/profile/" . $filename;
}


/* Deletes an old picture, but only if it was uploaded
   through this page (never your original files). */

function delete_uploaded(string $path, string $folder): void
{
    if (strpos($path, "uploads/profile/") === 0) {

        $file = $folder . basename($path);

        if (is_file($file)) {
            unlink($file);
        }
    }
}


/* Current pictures (defaults if nothing is saved yet) */

$profile = [
    "main_image"  => "neow.jpg",
    "hover_image" => "meows.png",
];

try {
    $result = $conn->query(
        "SELECT main_image, hover_image FROM profile WHERE id = 1"
    );

    if ($result && $row = $result->fetch_assoc()) {
        $profile = $row;
    }
} catch (Throwable $e) {
    $error = "The profile table is missing. Run database.sql first.";
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && $error === "") {

    $new_main = upload_picture(
        $_FILES["main_image"] ?? null,
        $allowed_types,
        $max_size,
        $folder,
        $error
    );

    $new_hover = null;

    if ($error === "") {

        $new_hover = upload_picture(
            $_FILES["hover_image"] ?? null,
            $allowed_types,
            $max_size,
            $folder,
            $error
        );
    }

    if ($error !== "") {

        // Discard anything already saved during this request
        if ($new_main) {
            delete_uploaded($new_main, $folder);
        }

    } else {

        $main_image = $new_main ?? $profile["main_image"];
        $hover_image = $profile["hover_image"];

        if ($new_hover) {
            $hover_image = $new_hover;
        } elseif (isset($_POST["remove_hover"])) {
            $hover_image = "";
        }

        $stmt = $conn->prepare(
            "INSERT INTO profile (id, main_image, hover_image)
             VALUES (1, ?, ?)
             ON DUPLICATE KEY UPDATE
                main_image = VALUES(main_image),
                hover_image = VALUES(hover_image)"
        );

        if ($stmt) {

            $stmt->bind_param("ss", $main_image, $hover_image);

            if ($stmt->execute()) {

                $stmt->close();

                // Remove replaced pictures from the server
                if ($main_image !== $profile["main_image"]) {
                    delete_uploaded($profile["main_image"], $folder);
                }

                if ($hover_image !== $profile["hover_image"]) {
                    delete_uploaded($profile["hover_image"], $folder);
                }

                header("Location: edit_profile.php?saved=1");
                exit;

            } else {

                $error = "Unable to save your profile pictures.";
            }

            $stmt->close();

        } else {

            $error = "Database error. Please try again.";
        }

        if ($error !== "") {

            if ($new_main) {
                delete_uploaded($new_main, $folder);
            }

            if ($new_hover) {
                delete_uploaded($new_hover, $folder);
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

    <title>Edit Profile</title>

    <link rel="stylesheet" href="../style.css">

</head>

<body>

<header class="admin-navbar">

    <h2>
        Edit Profile
    </h2>

    <a href="dashboard.php">
        Dashboard
    </a>

</header>


<main class="form-container">

    <h1>
        Profile Pictures
    </h1>


    <?php if (isset($_GET["saved"])): ?>

        <div class="success">
            Your profile pictures have been updated.
        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <div class="avatar-row">

        <div class="avatar-current">

            <img
                src="../<?= htmlspecialchars($profile["main_image"]) ?>"
                alt="Current main picture"
                class="avatar-preview"
            >

            Main picture

        </div>


        <div class="avatar-current">

            <?php if (!empty($profile["hover_image"])): ?>

                <img
                    src="../<?= htmlspecialchars($profile["hover_image"]) ?>"
                    alt="Current hover picture"
                    class="avatar-preview"
                >

            <?php else: ?>

                <div class="avatar-empty">
                    None
                </div>

            <?php endif; ?>

            Hover picture

        </div>

    </div>


    <form method="POST" enctype="multipart/form-data">

        <label>
            New Main Picture
        </label>

        <input
            type="file"
            name="main_image"
            accept=".jpg,.jpeg,.png,.webp"
        >

        <span class="form-hint">
            Shown normally. Leave empty to keep the current one.
        </span>


        <label>
            New Hover Picture
        </label>

        <input
            type="file"
            name="hover_image"
            accept=".jpg,.jpeg,.png,.webp"
        >

        <span class="form-hint">
            Shown when someone hovers over your picture.
        </span>


        <?php if (!empty($profile["hover_image"])): ?>

            <label class="checkbox-row">

                <input
                    type="checkbox"
                    name="remove_hover"
                >

                Remove the hover picture

            </label>

        <?php endif; ?>


        <span class="form-hint">
            JPG, PNG or WEBP. Maximum 5 MB each. Square pictures look best.
        </span>


        <button
            type="submit"
            class="btn"
        >
            Save Pictures
        </button>

    </form>

</main>

</body>
</html>
