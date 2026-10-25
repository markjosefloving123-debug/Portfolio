<?php

require_once "db.php";

$projects = [];

$sql = "SELECT * FROM projects ORDER BY created_at DESC";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

$certificates = [];

try {
    $cert_result = $conn->query(
        "SELECT * FROM certificates ORDER BY created_at DESC"
    );

    if ($cert_result) {
        while ($row = $cert_result->fetch_assoc()) {
            $certificates[] = $row;
        }
    }
} catch (Throwable $e) {
    // The certificates table has not been created yet.
}

// Profile pictures (editable from the admin dashboard)
$profile = [
    "main_image"  => "neow.jpg",
    "hover_image" => "meows.png",
];

try {
    $profile_result = $conn->query(
        "SELECT main_image, hover_image FROM profile WHERE id = 1"
    );

    if ($profile_result && $row = $profile_result->fetch_assoc()) {
        $profile = $row;
    }
} catch (Throwable $e) {
    // The profile table has not been created yet. Defaults are used.
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $user_message = trim($_POST["message"] ?? "");

    if ($name === "" || $email === "" || $user_message === "") {

        $message = "Please complete all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO messages (name, email, message)
             VALUES (?, ?, ?)"
        );

        if ($stmt) {

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $user_message
            );

            if ($stmt->execute()) {
                $message = "Your message has been sent successfully.";
            } else {
                $message = "Unable to send your message. Please try again.";
            }

            $stmt->close();

        } else {
            $message = "Something went wrong. Please try again later.";
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

    <title>Our Portfolio</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<header class="navbar">

    <div class="logo">
        Portfolio
    </div>

    <nav>
        <a href="#home">Home</a>
        <a href="#projects">Projects</a>
        <a href="#certificates">Certificates</a>
        <a href="#contact">Contact</a>
        <a href="login.php" class="admin-link">
            Admin
        </a>
    </nav>

</header>


<!-- PROFILE -->

<section class="hero" id="home">

    <div class="hero-content">

        <div class="profile-picture">

            <div class="profile-swap">

                <img
                    src="<?= htmlspecialchars($profile["main_image"]) ?>"
                    alt="Profile Picture"
                    class="swap-default"
                >

                <?php if (!empty($profile["hover_image"])): ?>

                    <img
                        src="<?= htmlspecialchars($profile["hover_image"]) ?>"
                        alt="Profile Picture Hover"
                        class="swap-hover"
                    >

                <?php endif; ?>

            </div>

        </div>


        <div class="profile-info">

            <p class="small-title">
                WELCOME TO MY PORTFOLIO
            </p>

            <h1>
                Hi, I'am  <span>Mark</span>
            </h1>

            <h2>
                Student 
            </h2>

            <p>
                Welcome to my portfolio. Here you can view
                my projects, skills, and contact information.
            </p>

            <a href="#projects" class="btn">
                View Projects
            </a>

        </div>

    </div>

</section>


<!-- PROJECTS -->

<section class="projects" id="projects">

    <div class="section-heading">

        <p>MY WORK</p>

        <h2>MY Projects</h2>

    </div>


    <div class="project-grid">

        <?php if (count($projects) > 0): ?>

            <?php foreach ($projects as $project): ?>

                <div class="project-card">

                    <div class="project-content">

                        <h3>
                            <?= htmlspecialchars($project["title"]) ?>
                        </h3>

                        <p>
                            <?= nl2br(
                                htmlspecialchars(
                                    $project["description"]
                                )
                            ) ?>
                        </p>

                        <span class="tag">
                            <?= htmlspecialchars(
                                $project["technologies"]
                            ) ?>
                        </span>

                        <?php if (!empty($project["project_link"])): ?>

                            <a
                                href="<?= htmlspecialchars(
                                    $project["project_link"]
                                ) ?>"
                                target="_blank"
                                class="project-btn"
                            >
                                View Project
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>
                No projects available yet.
            </p>

        <?php endif; ?>

    </div>

</section>


<!-- CERTIFICATES -->

<section class="certificates" id="certificates">

    <div class="section-heading">

        <p>ACHIEVEMENTS</p>

        <h2>My Certificates</h2>

    </div>


    <div class="certificate-grid">

        <?php if (count($certificates) > 0): ?>

            <?php foreach ($certificates as $cert): ?>

                <?php
                $isPdf = strtolower(
                    pathinfo($cert["file_path"], PATHINFO_EXTENSION)
                ) === "pdf";
                ?>

                <div class="certificate-card">

                    <div class="certificate-inner">

                        <?php if ($isPdf): ?>

                            <a
                                href="<?= htmlspecialchars($cert["file_path"]) ?>"
                                target="_blank"
                                class="certificate-pdf"
                            >
                                PDF
                                <span>Click to open</span>
                            </a>

                        <?php else: ?>

                            <img
                                src="<?= htmlspecialchars($cert["file_path"]) ?>"
                                alt="<?= htmlspecialchars($cert["title"]) ?>"
                                class="certificate-preview"
                            >

                        <?php endif; ?>

                        <div class="certificate-info">

                            <h3>
                                <?= htmlspecialchars($cert["title"]) ?>
                            </h3>

                            <?php if (!empty($cert["issuer"])): ?>
                                <p class="certificate-issuer">
                                    <?= htmlspecialchars($cert["issuer"]) ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($cert["date_earned"])): ?>
                                <p>
                                    <?= htmlspecialchars($cert["date_earned"]) ?>
                                </p>
                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p class="certificates-empty">
                No certificates available yet.
            </p>

        <?php endif; ?>

    </div>

</section>


<!-- CONTACT -->

<section class="contact" id="contact">

    <div class="section-heading">

        <p>GET IN TOUCH</p>

        <h2>Contact Us</h2>

    </div>


    <?php if ($message !== ""): ?>

        <div class="alert">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <form
        method="POST"
        class="contact-form"
    >

        <label>Name</label>

        <input
            type="text"
            name="name"
            placeholder="Enter your name"
            required
        >


        <label>Email</label>

        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            required
        >


        <label>Message</label>

        <textarea
            name="message"
            rows="6"
            placeholder="Write your message..."
            required
        ></textarea>


        <button
            type="submit"
            class="btn"
        >
            Send Message
        </button>

    </form>

</section>


<footer>

    <h3>OurPortfolio</h3>

    <p>
        Thank you for visiting our portfolio.
    </p>

</footer>

<!-- Click-to-enlarge viewer for certificates -->
<div id="lightbox" class="lightbox">
    <img id="lightbox-img" src="" alt="Certificate">
</div>

<script>
    const lightbox = document.getElementById("lightbox");
    const lightboxImg = document.getElementById("lightbox-img");

    document.querySelectorAll(".certificate-preview").forEach(function (img) {
        img.addEventListener("click", function () {
            lightboxImg.src = img.src;
            lightbox.classList.add("open");
        });
    });

    lightbox.addEventListener("click", function () {
        lightbox.classList.remove("open");
    });
</script>

</body>
</html>