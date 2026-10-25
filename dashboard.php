<?php

session_start();

if (!isset($_SESSION["admin_id"])) {

    header("Location: ../login.php");
    exit;
}

require_once "../db.php";

$result = $conn->query(
    "SELECT * FROM projects
     ORDER BY created_at DESC"
);

$certificates = null;

try {
    $certificates = $conn->query(
        "SELECT * FROM certificates
         ORDER BY created_at DESC"
    );
} catch (Throwable $e) {
    // The certificates table has not been created yet.
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="../style.css">

</head>

<body>

<header class="admin-navbar">

    <h2>
        Admin Dashboard
    </h2>

    <div>

        <a href="../index.php">
            View Portfolio
        </a>

        <a href="../logout.php">
            Logout
        </a>

    </div>

</header>


<main class="dashboard">

    <h1>
        Welcome, <?= htmlspecialchars(
            $_SESSION["admin_username"]
        ) ?>
    </h1>

    <p class="dashboard-subtitle">
        Manage your portfolio website.
    </p>


    <div class="dashboard-grid">

        <a
            href="add_project.php"
            class="dashboard-card"
        >

            <div class="dashboard-icon">
                +
            </div>

            <h2>
                Add Project
            </h2>

            <p>
                Add a new project to your portfolio.
            </p>

        </a>


        <a
            href="add_certificate.php"
            class="dashboard-card"
        >

            <div class="dashboard-icon">
                🏆
            </div>

            <h2>
                Add Certificate
            </h2>

            <p>
                Upload a certificate to display.
            </p>

        </a>


        <a
            href="edit_profile.php"
            class="dashboard-card"
        >

            <div class="dashboard-icon">
                👤
            </div>

            <h2>
                Edit Profile
            </h2>

            <p>
                Change your profile pictures.
            </p>

        </a>


        <a
            href="messages.php"
            class="dashboard-card"
        >

            <div class="dashboard-icon">
                ✉
            </div>

            <h2>
                Messages
            </h2>

            <p>
                View messages from visitors.
            </p>

        </a>


        <a
            href="dashboard.php"
            class="dashboard-card"
        >

            <div class="dashboard-icon">
                📁
            </div>

            <h2>
                Manage Projects
            </h2>

            <p>
                Edit or delete existing projects.
            </p>

        </a>

    </div>


    <h2 class="admin-section-title">
        Projects
    </h2>


    <div class="table-container">

        <table>

            <thead>

                <tr>
                    <th>Title</th>
                    <th>Technologies</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($project =
                    $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $project["title"]
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $project["technologies"]
                            ) ?>
                        </td>

                        <td>

                            <a
                                href="edit_project.php?id=<?= $project["id"] ?>"
                                class="edit"
                            >
                                Edit
                            </a>

                            <a
                                href="delete_project.php?id=<?= $project["id"] ?>"
                                class="delete"
                                onclick="return confirm(
                                    'Are you sure you want to delete this project?'
                                );"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="3">
                        No projects found.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>


    <h2 class="admin-section-title" style="margin-top: 50px;">
        Certificates
    </h2>


    <div class="table-container">

        <table>

            <thead>

                <tr>
                    <th>File</th>
                    <th>Title</th>
                    <th>Issued By</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

            <?php if ($certificates && $certificates->num_rows > 0): ?>

                <?php while ($cert =
                    $certificates->fetch_assoc()): ?>

                    <?php
                    $isPdf = strtolower(
                        pathinfo($cert["file_path"], PATHINFO_EXTENSION)
                    ) === "pdf";
                    ?>

                    <tr>

                        <td>
                            <?php if ($isPdf): ?>
                                PDF
                            <?php else: ?>
                                <img
                                    src="../<?= htmlspecialchars(
                                        $cert["file_path"]
                                    ) ?>"
                                    alt=""
                                    class="table-thumb"
                                >
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cert["title"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cert["issuer"] ?? "") ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($cert["date_earned"] ?? "") ?>
                        </td>

                        <td>

                            <a
                                href="../<?= htmlspecialchars(
                                    $cert["file_path"]
                                ) ?>"
                                target="_blank"
                                class="edit"
                            >
                                View
                            </a>

                            <form
                                method="POST"
                                action="delete_certificate.php"
                                style="display: inline;"
                                onsubmit="return confirm(
                                    'Are you sure you want to delete this certificate?'
                                );"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $cert["id"] ?>"
                                >

                                <button
                                    type="submit"
                                    class="delete-btn"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="5">
                        No certificates found.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</main>

</body>
</html>