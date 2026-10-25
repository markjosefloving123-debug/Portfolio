
<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit;
}

require_once "../db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $technologies = trim($_POST["technologies"] ?? "");
    $project_link = trim($_POST["project_link"] ?? "");

    if ($title === "" || $description === "") {

        $error = "Title and description are required.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO projects
            (title, description, technologies, project_link)
            VALUES (?, ?, ?, ?)"
        );

        if ($stmt) {

            $stmt->bind_param(
                "ssss",
                $title,
                $description,
                $technologies,
                $project_link
            );

            if ($stmt->execute()) {

                header(
                    "Location: dashboard.php"
                );

                exit;

            } else {

                $error =
                    "Unable to add the project.";
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

    <title>Add Project</title>

    <link rel="stylesheet" href="../style.css">

</head>

<body>

<header class="admin-navbar">

    <h2>
        Add Project
    </h2>

    <a href="dashboard.php">
        Dashboard
    </a>

</header>


<main class="form-container">

    <h1>
        Add New Project
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
            placeholder="Project title"
            required
        >


        <label>
            Description
        </label>

        <textarea
            name="description"
            rows="6"
            placeholder="Project description"
            required
        ></textarea>


        <label>
            Technologies
        </label>

        <input
            type="text"
            name="technologies"
            placeholder="HTML, CSS, PHP"
        >


        <label>
            Project Link
        </label>

        <input
            type="url"
            name="project_link"
            placeholder="https://example.com"
        >


        <button
            type="submit"
            class="btn"
        >
            Add Project
        </button>

    </form>

</main>

</body>
</html>
