<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}

require_once "../db.php";

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    header("Location: dashboard.php");
    exit;
}


$stmt = $conn->prepare(
    "SELECT * FROM projects WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

$project = $result->fetch_assoc();

$stmt->close();


if (!$project) {
    header("Location: dashboard.php");
    exit;
}


$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $technologies = trim($_POST["technologies"] ?? "");
    $project_link = trim($_POST["project_link"] ?? "");


    if ($title === "" || $description === "") {

        $error =
            "Title and description are required.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE projects
             SET title = ?,
                 description = ?,
                 technologies = ?,
                 project_link = ?
             WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param(
                "ssssi",
                $title,
                $description,
                $technologies,
                $project_link,
                $id
            );

            if ($stmt->execute()) {

                header(
                    "Location: dashboard.php"
                );

                exit;

            } else {

                $error =
                    "Unable to update the project.";
            }

            $stmt->close();

        } else {

            $error =
                "Database error. Please try again.";
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

    <title>Edit Project</title>

    <link rel="stylesheet" href="../style.css">

</head>

<body>

<header class="admin-navbar">

    <h2>
        Edit Project
    </h2>

    <a href="dashboard.php">
        Dashboard
    </a>

</header>


<main class="form-container">

    <h1>
        Edit Project
    </h1>


    <?php if ($error !== ""): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label>
            Project Title
        </label>

        <input
            type="text"
            name="title"
            value="<?= htmlspecialchars(
                $project["title"]
            ) ?>"
            required
        >


        <label>
            Description
        </label>

        <textarea
            name="description"
            rows="6"
            required
        ><?= htmlspecialchars(
            $project["description"]
        ) ?></textarea>


        <label>
            Technologies
        </label>

        <input
            type="text"
            name="technologies"
            value="<?= htmlspecialchars(
                $project["technologies"]
            ) ?>"
        >


        <label>
            Project Link
        </label>

        <input
            type="url"
            name="project_link"
            value="<?= htmlspecialchars(
                $project["project_link"]
            ) ?>"
        >


        <button
            type="submit"
            class="btn"
        >
            Update Project
        </button>

    </form>

</main>

</body>
</html>
