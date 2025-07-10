<?php
// PHP code to handle form submission and database interaction
$registrationMessage = ''; // Variable to hold success/error messages

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_submit'])) {
    // Database credentials
    $servername = "localhost";
    $username = "root";        // Default XAMPP MySQL username
    $password = "";            // Default XAMPP MySQL password is empty
    $dbname = "epic_usm_db";   // Your database name, as requested, no changes here

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        $registrationMessage = "Koneksi database gagal: " . $conn->connect_error;
    } else {
        // Get form data and sanitize it
        $fullName = $conn->real_escape_string($_POST['fullName'] ?? '');
        $email = $conn->real_escape_string($_POST['email'] ?? '');
        $phone = $conn->real_escape_string($_POST['phone'] ?? '');
        $nim = $conn->real_escape_string($_POST['nim'] ?? '');
        $studyProgram = $conn->real_escape_string($_POST['studyProgram'] ?? ''); // This will now come from the select input
        $interest = $conn->real_escape_string($_POST['interest'] ?? '');
        $message = $conn->real_escape_string($_POST['message'] ?? '');

        // Basic validation
        if (empty($fullName) || empty($email)) {
            $registrationMessage = "Nama Lengkap dan Email harus diisi.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $registrationMessage = "Format email tidak valid.";
        } else {
            // Cek apakah tabel registrations ada
            $checkSql = "SELECT id FROM registrations WHERE email = '$email'";
            $checkResult = $conn->query($checkSql);

            if ($checkResult === false) {
                $registrationMessage = "Tabel 'registrations' tidak ditemukan atau query error: " . $conn->error;
            } elseif ($checkResult->num_rows > 0) {
                $registrationMessage = "Email ini sudah terdaftar. Silakan gunakan email lain.";
            } else {
                // Prepare SQL to insert data
                $sql = "INSERT INTO registrations (full_name, email, phone, nim, study_program, interest, message)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                // 'sssssss' indicates all 7 parameters are strings
                $stmt->bind_param("sssssss", $fullName, $email, $phone, $nim, $studyProgram, $interest, $message);

                if ($stmt->execute()) {
                    // Jika sukses, redirect ke halaman awal (tanpa tab baru)
                    header("Location: index.php?success=1");
                    exit();
                } else {
                    $registrationMessage = "Error saat pendaftaran: " . $stmt->error;
                }
                $stmt->close();
            }
        }
        $conn->close();
    }
    // Encode message for JavaScript to handle
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                const registrationMessageElement = document.getElementById('registrationMessage');
                const registrationForm = document.getElementById('registrationForm');
                registrationMessageElement.textContent = " . json_encode($registrationMessage) . ";
                if (" . json_encode(strpos($registrationMessage, 'Terima kasih') !== false) . ") {
                    registrationMessageElement.style.color = 'var(--secondary-color)';
                    registrationForm.reset();
                } else {
                    registrationMessageElement.style.color = 'red';
                }
                registrationMessageElement.style.fontWeight = 'bold';
                openModal('registrationModal'); // Re-open modal to show message
            });
          </script>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPIC USM - English Proficiency Improvement Community</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* -------------------------------------- */
        /* Global Variables & Base Styles         */
        /* -------------------------------------- */
        :root {
            --primary-color: #FF8C42;
            /* Warm Orange */
            --secondary-color: #4F8C58;
            /* Elegant Dark Green */
            --text-dark: #2c3e50;
            /* Soft Dark Text */
            --text-light: #555;
            /* Gray text for paragraphs */
            --background-light: #fdfdff;
            /* Very Light Background */
            --background-gray: #f2f4f8;
            /* Soft Gray Background */
            --card-bg: #FFFFFF;
            /* Clean White for Cards */
            --border-radius-sm: 6px;
            --border-radius-md: 12px;
            --box-shadow-light: 0 4px 15px rgba(0, 0, 0, 0.08);
            /* Soft Shadow */
            --box-shadow-hover: 0 8px 25px rgba(0, 0, 0, 0.15);
            /* Slightly Stronger Shadow on Hover */
            --transition-speed: 0.3s ease-out;
            --gradient-primary-secondary: linear-gradient(to right, var(--secondary-color), var(--primary-color));
        }

        body {
            font-family: 'Inter', sans-serif;
            /* Using Inter as the main font */
            margin: 0;
            padding: 0;
            line-height: 1.7;
            color: var(--text-light);
            background-color: var(--background-light);
            scroll-behavior: smooth;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 30px;
        }

        a {
            text-decoration: none;
            color: var(--primary-color);
            transition: color var(--transition-speed);
        }

        a:hover {
            color: var(--secondary-color);
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: 'Montserrat', sans-serif;
            color: var(--text-dark);
            margin-top: 0;
            line-height: 1.3;
        }

        h1 {
            font-size: 2.8em;
        }

        h2 {
            font-size: 2.2em;
        }

        h3 {
            font-size: 1.8em;
        }

        h4 {
            font-size: 1.4em;
        }

        h5 {
            font-size: 1.1em;
        }

        .btn {
            display: inline-block;
            padding: 14px 30px;
            border-radius: var(--border-radius-md);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 0.95em;
            cursor: pointer;
            text-align: center;
            transition: background-color var(--transition-speed), transform 0.2s ease-out, box-shadow var(--transition-speed);
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 4px 10px rgba(255, 140, 66, 0.3);
        }

        .btn-primary:hover {
            background-color: #e67533;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 140, 66, 0.4);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: #fff;
            box-shadow: 0 4px 10px rgba(79, 140, 88, 0.3);
        }

        .btn-secondary:hover {
            background-color: #3f7047;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(79, 140, 88, 0.4);
        }

        /* -------------------------------------- */
        /* Header Section                         */
        /* -------------------------------------- */
        header {
            background-color: var(--secondary-color);
            color: #fff;
            padding: 15px 0;
            box-shadow: var(--box-shadow-light);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 1.9em;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .logo img {
            height: 40px;
            margin-right: 10px;
        }

        header nav ul {
            display: flex;
            gap: 25px;
        }

        header nav a {
            color: #fff;
            font-weight: 600;
            padding: 8px 0;
            position: relative;
            opacity: 0.9;
            transition: opacity var(--transition-speed);
        }

        header nav a:hover {
            opacity: 1;
            transform: translateY(-2px);
        }

        header nav a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0%;
            height: 2px;
            background-color: var(--primary-color);
            transition: width var(--transition-speed);
        }

        header nav a:hover::after {
            width: 100%;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: var(--border-radius-md);
            padding: 8px 15px;
            transition: background-color var(--transition-speed);
        }

        .search-bar:focus-within {
            background-color: rgba(255, 255, 255, 0.25);
        }

        .search-bar input {
            border: none;
            background: transparent;
            color: #fff;
            outline: none;
            padding: 0;
            width: 180px;
            font-family: 'Inter', sans-serif;
        }

        .search-bar input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-bar button {
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            font-size: 1.1em;
            margin-left: 10px;
            transition: color var(--transition-speed);
        }

        .search-bar button:hover {
            color: var(--primary-color);
        }

        .user-profile .btn {
            border: 1px solid rgba(255, 255, 255, 0.5);
            background: none;
            color: #fff;
            padding: 10px 20px;
        }

        .user-profile .btn:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }



        /* -------------------------------------- */
        /* Hero Section                           */
        /* -------------------------------------- */
        .hero-section {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.7)), url('foto2.jpg');
            /* Ganti dengan URL gambar kamu */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: #fff;
            text-align: center;
            padding: 140px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 550px;

        }

        .hero-content {
            z-index: 1;
            max-width: 900px;
        }

        .hero-content .category-tag {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 0.85em;
            margin-bottom: 20px;
            display: inline-block;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .hero-content h1 {
            font-size: 3.8em;
            margin-bottom: 25px;
            color: #fff;
            font-weight: 700;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.2em;
            line-height: 1.8;
            max-width: 700px;
            margin: 0 auto 40px auto;
            opacity: 0.9;
        }

        /* -------------------------------------- */
        /* Section Styling General                */
        /* -------------------------------------- */
        section {
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        section:nth-of-type(even) {
            background-color: var(--background-gray);
        }

        .section-header {
            text-align: center;
            margin-bottom: 70px;
        }

        .section-header h2 {
            font-size: 2.8em;
            font-weight: 700;
            position: relative;
            padding-bottom: 15px;
            display: inline-block;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }

        .section-header p {
            max-width: 700px;
            margin: 20px auto 0 auto;
            font-size: 1.1em;
            color: var(--text-light);
        }

        /* -------------------------------------- */
        /* Recent Activities Section              */
        /* -------------------------------------- */
        .recent-activities {
            background-color: var(--background-gray);
            background-image: url('https://i.ibb.co/0jFLpMdv/foto1.jpg');
            /* Ganti dengan URL gambar kamu */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            z-index: 1;
        }

        .activity-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .activity-item {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-light);
            transition: transform 0.3s ease, box-shadow var(--transition-speed);
        }

        .activity-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-hover);
        }

        .activity-item .meta {
            font-size: 0.88em;
            color: var(--text-light);
            margin-bottom: 12px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
        }

        .activity-item h4 {
            font-size: 1.25em;
            margin-bottom: 0;
            font-weight: 600;
            line-height: 1.4;
        }

        /* -------------------------------------- */
        /* Stay Connected Section                 */
        /* -------------------------------------- */
        .stay-connected-section {
            background: var(--gradient-primary-secondary);
            color: #fff;
            padding: 80px 0;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .stay-connected-content {
            display: flex;
            align-items: center;
            justify-content: space-around;
            max-width: 1000px;
            width: 100%;
            flex-wrap: wrap;
            gap: 40px;
        }

        .newsletter-text {
            flex: 1;
            text-align: left;
            min-width: 300px;
        }

        .newsletter-text h2 {
            color: #fff;
            margin-bottom: 15px;
            font-size: 2.2em;
            font-weight: 700;
        }

        .newsletter-text p {
            font-size: 1.05em;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .newsletter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .newsletter-form input {
            flex: 1 1 250px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            padding: 14px 22px;
            border-radius: var(--border-radius-md);
            outline: none;
            font-family: 'Inter', sans-serif;
            font-size: 1em;
            transition: border-color var(--transition-speed), background-color var(--transition-speed);
        }

        .newsletter-form input:focus {
            border-color: #fff;
            background-color: rgba(255, 255, 255, 0.25);
        }

        .newsletter-form input::placeholder {
            color: rgba(255, 255, 255, 0.8);
        }

        .newsletter-form .btn-secondary {
            padding: 12px 25px;
            box-shadow: none;
        }

        .newsletter-form .btn-secondary:hover {
            box-shadow: none;
        }

        .newsletter-image {
            flex: 1;
            text-align: right;
            min-width: 300px;
        }

        .newsletter-image img {
            max-width: 100%;
            height: auto;
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-hover);
        }

        /* -------------------------------------- */
        /* Featured Event / Blog Section          */
        /* -------------------------------------- */
        .featured-section {
            background-color: var(--background-light);
        }

        .featured-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            align-items: start;
        }

        .main-content-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-light);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow var(--transition-speed);
            cursor: pointer;
        }

        .main-content-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--box-shadow-hover);
        }

        .main-content-card img {
            width: 100%;
            height: 280px;
            object-fit: cover;
        }

        .main-content-content {
            padding: 30px;
        }

        .main-content-content .meta {
            font-size: 0.88em;
            color: var(--text-light);
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
        }

        .main-content-content .category-tag-small {
            background-color: var(--background-gray);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.78em;
            color: var(--text-dark);
            font-weight: 600;
        }

        .main-content-content h3 {
            font-size: 1.6em;
            margin-bottom: 15px;
            line-height: 1.4;
            font-weight: 700;
        }

        .main-content-content p {
            font-size: 1em;
            line-height: 1.6;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .main-content-content .read-more {
            color: var(--primary-color);
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
        }

        .main-content-content .read-more:hover {
            text-decoration: underline;
        }


        .sidebar {
            background-color: var(--card-bg);
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-light);
            padding: 30px;
        }

        /* About EPIC USM / Meet Our Team */
        .about-ukm-info {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--background-gray);
        }

        .about-ukm-info img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid var(--primary-color);
            box-shadow: 0 0 0 5px rgba(255, 140, 66, 0.1);
        }

        .about-ukm-info h4 {
            margin-bottom: 8px;
            font-size: 1.3em;
            font-weight: 700;
        }

        .about-ukm-info p {
            font-size: 0.95em;
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .about-ukm-info .social-links {
            margin-top: 15px;
        }

        .about-ukm-info .social-links a {
            font-size: 1.4em;
            margin: 0 10px;
            color: var(--text-light);
            transition: color var(--transition-speed), transform 0.2s ease-out;
        }

        .about-ukm-info .social-links a:hover {
            color: var(--primary-color);
            transform: translateY(-3px);
        }

        /* Upcoming Events / Past Achievements */
        .ukm-updates-sidebar {
            margin-top: 20px;
            padding-top: 20px;
        }

        .ukm-updates-sidebar h5 {
            font-size: 1.2em;
            margin-bottom: 20px;
            color: var(--secondary-color);
            font-weight: 700;
        }

        .ukm-updates-sidebar ul li {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed var(--background-gray);
        }

        .ukm-updates-sidebar ul li:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .ukm-updates-sidebar ul li a {
            display: block;
            font-size: 0.95em;
            color: var(--text-dark);
            font-weight: 600;
            transition: color var(--transition-speed);
        }

        .ukm-updates-sidebar ul li a:hover {
            color: var(--primary-color);
        }

        .ukm-updates-sidebar .meta {
            font-size: 0.85em;
            color: var(--text-light);
            margin-top: 5px;
        }


      
        /* -------------------------------------- */
        /* Footer Section                         */
        /* -------------------------------------- */
        footer {
            background-color: var(--secondary-color);
            color: rgba(255, 255, 255, 0.9);
            padding: 30px 0;
            text-align: center;
        }

        /* New Contact Section Styles */
        .contact-section {
            background-color: var(--background-gray);
            /* Or another suitable background */
            padding: 80px 0;
            text-align: center;
        }

        .contact-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
            justify-content: center;
        }

        .contact-item {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--box-shadow-light);
            transition: transform 0.3s ease, box-shadow var(--transition-speed);
        }

        .contact-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-hover);
        }

        .contact-item i {
            font-size: 2.5em;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .contact-item h4 {
            font-size: 1.4em;
            margin-bottom: 10px;
            color: var(--secondary-color);
        }

        .contact-item p {
            font-size: 1em;
            color: var(--text-light);
            margin: 0;
        }


        footer .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        footer p {
            margin: 0;
            font-size: 0.9em;
        }

        .footer-social-links a {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.3em;
            margin-left: 20px;
            transition: color var(--transition-speed), transform 0.2s ease-out;
        }

        .footer-social-links a:hover {
            color: var(--primary-color);
            transform: translateY(-3px);
        }

        /* -------------------------------------- */
        /* POP-UP MODAL STYLES                    */
        /* -------------------------------------- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease-out, visibility 0.4s ease-out;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background-color: var(--card-bg);
            padding: 50px;
            border-radius: var(--border-radius-md);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            text-align: center;
            max-width: 600px;
            width: 90%;
            transform: scale(0.7) translateY(50px);
            opacity: 0;
            transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55), opacity 0.4s ease-out;
            position: relative;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        .modal-close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.8em;
            color: var(--text-light);
            cursor: pointer;
            transition: color var(--transition-speed);
        }

        .modal-close-btn:hover {
            color: var(--primary-color);
            transform: rotate(90deg);
        }

        .modal-content .btn {
            padding: 14px 30px;
        }

        /* Registration Modal Specific Styles - Minimized for readability */
        #registrationModal .modal-content {
            text-align: left;
            max-width: 380px;
            /* Reduced max-width */
            padding: 20px;
            /* Reduced padding */
        }

        #registrationModal .modal-content h3 {
            font-size: 1.4em;
            /* Slightly reduced font size */
            margin-bottom: 10px;
            /* Reduced margin */
        }

        #registrationModal .modal-content p {
            font-size: 0.85em;
            /* Slightly reduced font size */
            margin-bottom: 15px;
            /* Reduced margin */
        }

        #registrationModal .form-group {
            margin-bottom: 10px;
            /* Reduced margin between form groups */
        }

        #registrationModal label {
            margin-bottom: 3px;
            /* Reduced margin below labels */
            font-weight: 600;
            /* Ensure label is bold */
            color: var(--text-dark);
            /* Ensure label color is dark */
            font-family: 'Montserrat', sans-serif;
            /* Ensure font family is Montserrat */
            font-size: 0.8em;
            /* Slightly reduced font size */
        }

        #registrationModal input[type="text"],
        #registrationModal input[type="email"],
        #registrationModal input[type="tel"],
        #registrationModal select,
        /* Added select to this style rule */
        #registrationModal textarea {
            width: calc(100% - 16px);
            padding: 6px;
            /* Reduced padding */
            font-size: 0.85em;
            /* Slightly reduced font size */
            border: 1px solid var(--background-gray);
            /* Ensure border is visible */
            border-radius: var(--border-radius-sm);
            /* Ensure rounded corners */
            font-family: 'Inter', sans-serif;
            /* Ensure font family is Inter */
            color: var(--text-dark);
            /* Ensure text color is dark */
            background-color: var(--background-light);
            /* Ensure background is light */
        }

        #registrationModal textarea {
            min-height: 60px;
            /* Reduced min-height */
        }

        #registrationModal .btn-primary {
            width: auto;
            margin-top: 15px;
            padding: 8px 18px;
            /* Reduced padding for button */
            font-size: 0.8em;
            /* Slightly reduced font size for button */
            display: block;
            /* Ensure button takes full width if needed */
            text-align: center;
            /* Center text in button */
            /* Added visual emphasis for clickable button */
            background-color: var(--primary-color);
            color: #fff;
            border-radius: var(--border-radius-md);
            box-shadow: 0 4px 10px rgba(255, 140, 66, 0.3);
            transition: background-color var(--transition-speed), transform 0.2s ease-out, box-shadow var(--transition-speed);
            border: none;
        }

        #registrationModal .btn-primary:hover {
            background-color: #e67533;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 140, 66, 0.4);
        }


        /* -------------------------------------- */
        /* Responsive Design                      */
        /* -------------------------------------- */
        @media (max-width: 992px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 2.4em;
            }

            h2 {
                font-size: 2em;
            }

            h3 {
                font-size: 1.6em;
            }

            .hero-section {
                padding: 100px 20px;
                min-height: 450px;
            }

            .hero-content h1 {
                font-size: 3em;
            }

            .activity-list,
            .featured-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }

            .main-content-card img {
                height: 220px;
            }

            .prompter-input-area,
            .ai-response-area {
                /* Updated class name */
                width: 95%;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            header nav ul {
                flex-direction: column;
                align-items: center;
                gap: 15px;
                margin-top: 15px;
            }

            .search-bar {
                width: 100%;
                justify-content: center;
                margin-top: 15px;
            }

            .user-profile {
                margin-top: 15px;
            }

            .hero-content h1 {
                font-size: 2.5em;
            }

            .hero-content p {
                font-size: 1em;
            }

            .stay-connected-content {
                flex-direction: column;
                text-align: center;
            }

            .newsletter-text,
            .newsletter-image {
                text-align: center;
            }

            .newsletter-form {
                justify-content: center;
            }

            .newsletter-image img {
                margin-top: 30px;
            }

            .contact-info-grid {
                grid-template-columns: 1fr;
            }

            footer .container {
                flex-direction: column;
            }

            .footer-social-links {
                margin-top: 15px;
            }

            .modal-content {
                padding: 30px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            h1 {
                font-size: 2em;
            }

            h2 {
                font-size: 1.8em;
            }

            .hero-section {
                padding: 80px 15px;
                min-height: 350px;
            }

            .hero-content h1 {
                font-size: 2em;
            }

            .btn {
                padding: 10px 20px;
                font-size: 0.85em;
            }

            .section-header h2 {
                font-size: 2em;
            }

            .activity-item {
                padding: 20px;
            }

            .main-content-content {
                padding: 20px;
            }

            .newsletter-form input {
                padding: 10px;
            }

            .newsletter-form .btn-secondary {
                padding: 10px 20px;
            }

            #registrationModal .modal-content {
                padding: 15px;
            }

            #registrationModal input[type="text"],
            #registrationModal input[type="email"],
            #registrationModal input[type="tel"],
            #registrationModal select,
            #registrationModal textarea {
                padding: 8px;
                font-size: 0.9em;
            }

            #registrationModal .btn-primary {
                padding: 10px 20px;
                font-size: 0.9em;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="container header-content">
            <a href="#" class="logo">
                <img src="logo epic.png" alt="EPIC USM Logo"> EPIC USM
            </a>
            <nav>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#activities">Activities</a></li>
                    <li><a href="#featured">Featured</a></li>
                 
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>
            <div class="search-bar">
                <input type="text" placeholder="Search...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
            <div class="user-profile">
            </div>
        </div>
    </header>

    <main>
        <section id="home" class="hero-section">
            <div class="hero-content">
                <span class="category-tag">English Proficiency Improvement Community</span>
                <h1> Unlock Your English Potential with Us!</h1>
                <p>Join EPIC USM and embark on a transformative journey to enhance your English skills, connect with a
                    vibrant community, and unlock global opportunities.</p>
                <a href="#registrationModal" class="btn btn-primary" onclick="openModal('registrationModal')">Register
                    Now!</a>
            </div>
        </section>
        <!-- Recent Activities Section -->
        <section class="recent-activities" id="activities">
            <div class="container">
                <div class="section-header">
                    <h2>Recent Activities</h2>
                    <p>Berikut beberapa program kerja (proker) yang pernah dijalankan oleh EPIC USM.</p>
                </div>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="meta">2025 | Proker</div>
                        <h4>EPIC Mengajar</h4>
                        <p>Kegiatan pengabdian masyarakat di mana anggota EPIC USM mengajar bahasa Inggris di
                            sekolah-sekolah atau komunitas sekitar, sebagai bentuk kontribusi nyata untuk pendidikan.
                        </p>
                        <a href="https://www.instagram.com/reel/DJOKG_eRTuV/?utm_source=ig_web_copy_link&igsh=MzRlODBiNWFlZA=="
                            target="_blank" class="btn btn-secondary" style="margin-top:15px;">
                            Lihat Dokumentasi Video <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                    <div class="activity-item">
                        <div class="meta">2025 | Proker</div>
                        <h4>EPIC Study Banding ke UDINUS</h4>
                        <p>Kegiatan study banding ke Universitas Dian Nuswantoro (UDINUS) untuk berbagi pengalaman,
                            memperluas relasi, dan meningkatkan wawasan organisasi bersama UKM sejenis di kampus lain.
                        </p>
                        <a href="https://www.instagram.com/reel/DLMeKRORseq/?utm_source=ig_web_copy_link&igsh=MzRlODBiNWFlZA=="
                            target="_blank" class="btn btn-secondary" style="margin-top:15px;">
                            Lihat Dokumentasi Video <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>
        
         <!-- Stay Connected Section -->
    <section class="stay-connected-section">
        <div class="container stay-connected-content">
            <div class="newsletter-text">
                <h2>Media Partner EPIC USM</h2>
                <p>
                    Untuk informasi kerja sama media partner, silakan hubungi kontak di bawah ini. Kami terbuka untuk kolaborasi dengan berbagai media, komunitas, maupun organisasi lain.
                </p>
                <div class="contact-info-grid" style="margin-top:30px;">
                    <div class="contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <h4>Cahya</h4>
                        <p><a href="tel:+6289504502667">+62 895-0450-2667</a></p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <h4>Celo</h4>
                        <p><a href="tel:+6285641481492">+62 856-4148-1492</a></p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <h4>Email</h4>
                        <p><a href="mailto:epicusmjaya@gmail.com">epicusmjaya@gmail.com</a></p>
                    </div>
                </div>
            </div>
            <div style="flex:1; min-width:320px; max-width:420px; margin-left:40px;">
                <h3 style="color:#fff; margin-bottom:18px;">Guidebook Media Partner</h3>
                <a href="Guidebook Media Partner EPIC USM.pdf" class="btn btn-secondary" target="_blank" style="margin-bottom:18px;display:block;">
                    <i class="fas fa-file-pdf"></i> Download Guidebook (PDF)
                </a>
                <iframe src="Guidebook Media Partner EPIC USM.pdf" width="100%" height="400" style="border:1px solid #ccc; border-radius:12px; background:#fff;"></iframe>
            </div>
        </div>
    </section>
        

        <section id="featured" class="featured-section">
            <div class="container">
                <div class="section-header">
                    <h2>Featured Insights & Events</h2>
                    <p>Explore our most popular articles, upcoming major events, and key highlights from our community.
                    </p>
                </div>
                <div class="featured-grid">
                    <div class="main-content-card">
                        <img src="study.png" alt="EPIC USM Featured Image " alt="EPIC USM Featured Image">
                        <div class="main-content-content">
                            <div class="meta">
                                <span><i class="far fa-calendar-alt"></i> 15 June 2025</span>
                                <span class="category-tag-small">Event</span>
                            </div>
                          <h3>Study Banding UNWAHAS ke Universitas Semarang</h3>
                        <p>Pada tanggal 15 Juni 2025, EPIC USM melaksanakan program study banding bersama UNWAHAS ke Universitas Semarang. Kegiatan ini bertujuan untuk memperluas wawasan, mempererat relasi antar organisasi, serta berbagi pengalaman dan inspirasi dalam pengelolaan UKM Bahasa Inggris di lingkungan kampus.</p>
                        </div>
                    </div>
                    <div class="sidebar">
                        <div class="about-ukm-info">
                            <img src="logo epic.png" width="90" height="90"
                                onerror="this.onerror=null; this.src='https://placehold.co/90x90/2c3e50/FFFFFF?text=EPIC';"
                                alt="EPIC USM Avatar">
                            <h4>About EPIC USM</h4>
                            <p>The EPIC English Club at Universitas Semarang (USM) is a student organization. EPIC stands for Educative, Pleasant, Interactive, Ceaseless. This club aims to be a platform for USM students to improve their English language skills and develop organizational abilities through various programs, including international seminars.</p>
                            <div class="social-links">               
                                <a href="https://www.instagram.com/epicusm?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank"><i class="fab fa-instagram"></i></a>
                                <a href= "https://www.tiktok.com/@epicusm?_t=8d3p2sQkH2y&_r=1" target="_blank"><i class="fab fa-tiktok"></i></a>
                                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=epicusmjaya@gmail.com" target="_blank" title="Send Email">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            </div>
                        </div>
                        <div class="ukm-updates-sidebar">
                            <h5>Prestasi Individual & Team Epic USM</h5>

                            <ul>
                                <li>
                                    <strong>Prita 2nd winner best speaker international debate competition
                                        
                                    </strong><br>
                                    <span class="meta">Universitas Negeri Semarang, 2024</span>
                                </li>   
                                    <strong> 1st winner best topic usm international SDG’s student competition </strong>
                                     <span class="meta">ISSCO 2024
</span>
                                </li>
                                <li style="margin-top:20px;">
                                    <strong> Yesa Tampubolon 3rd winner international essay competition</strong><br>
                                    <span class="meta"> USM INTEREST 2024
</span>
                                </li>
                                 <strong> 1st ISSCO speech 2024
                        </strong>
                        <li style="margin-top:12px;">
                                 <span class="meta">ISSCO speech 2024 
</span>
                                </li>
                                <li style="margin-top:20px;">
                                    <strong>Gilang 1st winner international essay competition</strong><br>
                                    <span class="meta">usm interest 2024</span>
                                </li>
                            </ul>
                            <hr style="margin:20px 0;">
                            <h5>EPIC USM Regular Schedule</h5>
                            <ul>
                                <li>
                                    <strong>Setiap Senin</strong><br>
                                    📍Location: Basecamp EPIC Prof Dr Muladi S.H Gedung Menara USM Lt.4<br>
                                    🕰Time: 3.30 PM - 4.30 PM
                                </li>
                                <li style="margin-top:12px;">
                                    <strong>Setiap Jumat</strong><br>
                                    📍Location: Basecamp EPIC Prof Dr Muladi S.H Gedung Menara USM Lt.4<br>
                                    ⏰ Time: 1.30 PM - 2.30 PM
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <section class="ai-prompter-section" id="ai-prompter">
        <div class="container ai-prompter-content">
            <div class="prompter-input-area">
                <Flat type="btn btn-primary" id="sendPrompterBtn">
                    
</section> 
        <script>
            // PERINGATAN PENTING:
            // Menempatkan API Key langsung di kode frontend (client-side) sangat TIDAK DISARANKAN untuk produksi.
            // Ini dapat menyebabkan kebocoran API Key Anda dan potensi penyalahgunaan.
            // Untuk aplikasi produksi, disarankan untuk memanggil API melalui backend server Anda.
            // Ganti 'YOUR_GEMINI_API_KEY' dengan API Key Gemini Anda yang sebenarnya.
            const GEMINI_API_KEY = 'AIzaSyC5OfcxfBzKv_r3MaOUiQV2f5bh9bxdnYoY';

            // Endpoint untuk Google AI Gemini API (generasi teks)
            // Model yang digunakan di sini adalah 'gemini-pro'. Anda bisa menggantinya jika ada model lain.
            const apiEndpoint = `https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=${GEMINI_API_KEY}`;

            document.getElementById('sendPrompterBtn').addEventListener('click', async () => {
                const prompt = document.getElementById('prompterInput').value;
                const aiResponseArea = document.getElementById('aiResponseArea');
                const sendPrompterBtn = document.getElementById('sendPrompterBtn');
                const loadingSpinner = sendPrompterBtn.querySelector('.loading-spinner');

                if (!prompt.trim()) {
                    aiResponseArea.innerHTML = 'Mohon masukkan pertanyaan Anda.';
                    return;
                }

                // Tampilkan spinner loading
                loadingSpinner.style.display = 'inline-block';
                sendPrompterBtn.disabled = true;
                aiResponseArea.innerHTML = 'Memproses pertanyaan Anda...';

                try {
                    const response = await fetch(apiEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            contents: [{
                                parts: [{
                                    text: prompt
                                }]
                            }]
                        })
                    });

                    const data = await response.json();
                    console.log(data); // Log respons lengkap untuk debugging

                    let jawaban = "Maaf, tidak dapat menghasilkan jawaban."; // Default

                    // Sesuaikan cara mengambil jawaban berdasarkan struktur respons Gemini API
                    if (data.candidates && data.candidates.length > 0 && data.candidates[0].content && data.candidates[0].content.parts && data.candidates[0].content.parts.length > 0) {
                        jawaban = data.candidates[0].content.parts[0].text;
                    } else if (data.error) {
                        jawaban = `Terjadi kesalahan: ${data.error.message}`;
                    }

                    aiResponseArea.innerHTML = jawaban;

                } catch (error) {
                    console.error('Error fetching AI response:', error);
                    aiResponseArea.innerHTML = 'Terjadi kesalahan saat menghubungi server AI. Mohon coba lagi.';
                } finally {
                    // Sembunyikan spinner loading dan aktifkan tombol kembali
                    loadingSpinner.style.display = 'none';
                    sendPrompterBtn.disabled = false;
                }
            });
        </script>

        <section id="contact" class="contact-section">
            <div class="container">
                <div class="section-header">
                    <h2>Contact Us</h2>
                    <p>Have questions or want to get involved? Reach out to us through our various channels.</p>
                </div>
                <div class="contact-info-grid" style="margin-top:30px;">
                    <div class="contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <h4>Cahya</h4>
                        <p><a href="tel:+6289504502667">+62 895-0450-2667</a></p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <h4>Celo</h4>
                        <p><a href="tel:+6285641481492">+62 856-4148-1492</a></p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <h4>Email</h4>
                        <p><a href="mailto:epicusmjaya@gmail.com">epicusmjaya@gmail.com</a></p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 EPIC USM. All Rights Reserved.</p>
            <div class="footer-social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
            </div>
        </div>
    </footer>

    <div id="registrationModal" class="modal-overlay">
        <div class="modal-content">
            <button class="modal-close-btn" onclick="closeModal('registrationModal')">&times;</button>
            <h3>Join EPIC USM Community</h3>
            <p>Fill out the form below to become a member and start your journey to English proficiency!</p>
            <form id="registrationForm" method="POST" action="index.php">
                <input type="hidden" name="form_submit" value="1">
                <!-- Tambahkan daftar program studi S-1 di sini -->
                <div class="form-group">
                    <label for="regName">Full Name:</label>
                    <input type="text" id="regName" name="fullName" required>
                </div>
                <div class="form-group">
                    <label for="regEmail">Email Address:</label>
                    <input type="email" id="regEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="regPhone">Phone Number:</label>
                    <input type="tel" id="regPhone" name="phone">
                </div>
                <div class="form-group">
                    <label for="regNIM">NIM (Nomor Induk Mahasiswa):</label>
                    <input type="text" id="regNIM" name="nim">
                </div>
                <div class="form-group">
                    <label for="regStudyProgram">Fakultas dan Program Studi USM:</label>
                    <select id="regStudyProgram" name="studyProgram" required>
                        <option value="">-- Select your program --</option>
                        <option value="Fakultas Ekonomi">Fakultas Ekonomi</option>
                        <option value="Manajemen">Manajemen</option>
                        <option value="Akuntansi">Akuntansi</option>
                        <option value="Fakultas Teknik">Fakultas Teknik</option>
                        <option value="Teknik Sipil">Teknik Sipil</option>
                        <option value="Arsitektur">Arsitektur</option>
                        <option value="Teknik Elektro">Teknik Elektro</option>
                        <option value="Perencanaan Wilayah dan Kota">Perencanaan Wilayah dan Kota</option>
                        <option value="Fakultas Teknologi Pertanian">Fakultas Teknologi Pertanian</option>
                        <option value="Teknologi Pangan">Teknologi Pangan</option>
                        <option value="Fakultas Hukum">Fakultas Hukum</option>
                        <option value="Ilmu Hukum">Ilmu Hukum</option>
                        <option value="Fakultas Psikologi">Fakultas Psikologi</option>
                        <option value="Psikologi">Psikologi</option>
                        <option value="Fakultas Teknologi Informasi dan Komunikasi">Fakultas Teknologi Informasi dan
                            Komunikasi</option>
                        <option value="Teknik Informatika">Teknik Informatika</option>
                        <option value="Sistem Informasi">Sistem Informasi</option>
                        <option value="Ilmu Komunikasi">Ilmu Komunikasi</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="regInterest">Area of Interest in English:</label>
                    <select id="regInterest" name="interest" required>
                        <option value="">-- Select your interest --</option>
                        <option value="Speaking">Speaking</option>
                        <option value="Debate">Debate</option>
                        <option value="Writing">Writing</option>
                        <option value="Reading">Reading</option>
                        <option value="Listening">Listening</option>
                        <option value="Translation">Translation</option>
                        <option value="Public Speaking">Public Speaking</option>
                        <option value="Storytelling">Storytelling</option>
                        <option value="News Casting">News Casting</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="regMessage">Why do you want to join EPIC USM?</label>
                    <textarea id="regMessage" name="message"></textarea>
                </div>
                <p id="registrationMessage" style="margin-top: 15px;"></p> <button type="submit"
                    class="btn btn-primary">Register</button>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            // Clear message when closing modal, if any
            const messageElement = document.getElementById('registrationMessage');
            if (messageElement) {
                messageElement.textContent = '';
                messageElement.style.color = 'inherit';
            }
            // Reset the form if it's the registration modal
            const registrationForm = document.getElementById('registrationForm');
            if (registrationForm && modalId === 'registrationModal') {
                registrationForm.reset();
            }
        }

        // Close modal when clicking outside of it
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function (event) {
                if (event.target === this) {
                    closeModal(this.id);
                }
            });
        });

        // Close modal when pressing the Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal-overlay').forEach(modal => {
                    if (modal.classList.contains('active')) {
                        closeModal(modal.id);
                    }
                });
            }
        });
        // AI Prompter functionality// AI Prompter Functionality
        const prompterInput = document.getElementById('prompterInput');
        const sendPrompterBtn = document.getElementById('sendPrompterBtn');
        const aiResponseArea = document.getElementById('aiResponseArea');
        const loadingSpinner = sendPrompterBtn.querySelector('.loading-spinner');
        const sendButtonText = sendPrompterBtn.childNodes[0]; // Get the text node "Hasilkan Jawaban"

        if (sendPrompterBtn) {
            sendPrompterBtn.addEventListener('click', async () => {
                const prompt = prompterInput.value.trim();
                if (!prompt) {
                    aiResponseArea.textContent = "Please enter a topic or keyword to generate a prompt.";
                    aiResponseArea.classList.add('active');
                    return;
                }

                // Show loading spinner and disable button
                sendButtonText.nodeValue = 'Generating... '; // Update button text
                loadingSpinner.style.display = 'inline-block';
                sendPrompterBtn.disabled = true;
                aiResponseArea.classList.remove('active'); // Hide previous response
                aiResponseArea.textContent = "Generating your response..."; // Set temporary text

                try {
                    let chatHistory = [];
                    // Modified prompt to be more general, like a conversational AI
                    chatHistory.push({ role: "user", parts: [{ text: prompt }] });
                    const payload = { contents: chatHistory };
                    // API key yang diberikan oleh pengguna
                    const apiKey = "AIzaSyBON-F10m0p5xNDSMk_sQAwaiQlazfXJ74";
                    const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=${apiKey}`;

                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (result.candidates && result.candidates.length > 0 &&
                        result.candidates[0].content && result.candidates[0].content.parts &&
                        result.candidates[0].content.parts.length > 0) {
                        const generatedText = result.candidates[0].content.parts[0].text;
                        aiResponseArea.textContent = generatedText;
                    } else {
                        aiResponseArea.textContent = "Maaf, saya tidak dapat menghasilkan respons. Silakan coba lagi dengan masukan yang berbeda.";
                    }
                } catch (error) {
                    console.error('Error generating prompt:', error);
                    aiResponseArea.textContent = "Terjadi kesalahan saat terhubung ke AI. Silakan coba lagi nanti.";
                } finally {
                    // Hide loading spinner and enable button
                    sendButtonText.nodeValue = 'Hasilkan Jawaban'; // Reset button text
                    loadingSpinner.style.display = 'none';
                    sendPrompterBtn.disabled = false;
                    aiResponseArea.classList.add('active'); // Show the response
                }
            });
        }
        // Hero Background Change Functionality});

        // Function to update the background of the hero section
        function updateHeroBackground() {
            if (currentBg >= heroBackgrounds.length) {
                currentBg = 0;
            }
            const bgUrl = `url('https://i.ibb.co/${heroBackgrounds[currentBg]}')`;
            heroSection.style.backgroundImage = bgUrl;
            currentBg++;
        }



        // Daftar file gambar kolase (ganti dengan nama file kamu)
        const heroBackgrounds = [
            'foto2.jpg',
            'foto3.jpg',
            'foto4.jpg',
        ];
        let currentBg = 0;
        const heroSection = document.querySelector('.hero-section');

        function changeHeroBg() {
            heroSection.style.backgroundImage =
                `linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('${heroBackgrounds[currentBg]}')`;
            currentBg = (currentBg + 1) % heroBackgrounds.length;
        }

        // Ganti foto setiap 4 detik
        setInterval(changeHeroBg, 4000);
        // Set foto awal saat halaman dimuat
        changeHeroBg();
    </script>
</body>

</html>