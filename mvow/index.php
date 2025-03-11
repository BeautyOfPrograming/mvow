<?php
require_once 'includes/database.php';
require_once 'includes/header.php';

// Configuration
$recordingsPerPage = 6;

// Get the current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1

// Get all recordings
$recordingsDir = 'recordings/';
$allRecordings = [];
if (is_dir($recordingsDir)) {
    $files = scandir($recordingsDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'wav') {
            $allRecordings[] = $file;
        }
    }
}

// Handle search
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($searchQuery) {
    $allRecordings = array_filter($allRecordings, function($recording) use ($searchQuery) {
        return stripos($recording, $searchQuery) !== false;
    });
}

// Calculate pagination
$totalRecordings = count($allRecordings);
$totalPages = ceil($totalRecordings / $recordingsPerPage);
$page = min($page, $totalPages); // Ensure page is not higher than total pages
$start = ($page - 1) * $recordingsPerPage;
$currentRecordings = array_slice($allRecordings, $start, $recordingsPerPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Audio Archive</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #a8c0ff, #3f2b96);
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            width: 85%;
            max-width: 1300px;
            margin: 50px auto;
            padding: 50px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
            color: #e0f7fa;
            font-size: 2.5em;
        }

        .search-box {
            text-align: center;
            margin-bottom: 30px;
        }


.search-box form {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px; /* Adds spacing between the input and button */
}

        .search-box input[type="text"] {
            width: 100%;
            max-width: 700px;
            padding: 12px 20px;
            border: none;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 1.1em;
            outline: none;
            transition: background 0.3s ease;
        }

        .search-box input[type="text"]::placeholder {
            color: #bbdefb;
        }

        .search-box input[type="text"]:focus {
            background: rgba(255, 255, 255, 0.25);
        }

        .search-box button {
            padding: 12px 20px;
            margin-left: 10px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.15);
            color: #bbdefb;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-box button:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .recordings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .recording-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .recording-card:hover {
            transform: translateY(-5px);
        }

        .recording-card a {
            color: #bbdefb;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.2em;
            display: block;
            transition: color 0.3s ease;
            cursor: pointer;
        }

        .recording-card a:hover {
            color: #e1f5fe;
        }

        p.no-recordings {
            text-align: center;
            font-style: italic;
            color: #b0bec5;
            margin-top: 40px;
            font-size: 1.1em;
        }

        footer {
            margin-top: auto;
            text-align: center;
            padding: 30px 0;
            color: #b0bec5;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .pagination a {
            padding: 12px 20px;
            margin: 0 8px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            text-decoration: none;
            color: #bbdefb;
            transition: background 0.3s ease;
            font-size: 1.1em;
        }

        .pagination a:hover,
        .pagination a.active {
            background: rgba(255, 255, 255, 0.25);
        }

        .custom-audio-player {
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
          
            border-radius: 10px;
        
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .custom-audio-player audio {
            width: 100%;
            outline: none;
        }

        .custom-audio-player button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .custom-audio-player button:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Heart Sound</h1>

    <!-- Search Box -->
    <div class="search-box">
        <form action="" method="GET">
            <input type="text" name="search" placeholder="Search recordings..." value="<?= htmlspecialchars($searchQuery) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Custom Audio Player -->
    <div class="custom-audio-player" id="customAudioPlayer">
        <audio id="audioPlayer" controls>
            <source src="" type="audio/wav">
            Your browser does not support the audio element.
        </audio>
    </div>

    <div class="recordings-grid">
        <?php
        if (!empty($currentRecordings)) {
            foreach ($currentRecordings as $file) {
                echo '<div class="recording-card">';
                echo '<a href="#" onclick="playAudio(\'' . htmlspecialchars($recordingsDir . $file) . '\')">' . htmlspecialchars($file) . '</a>';
                echo '</div>';
            }
        } else {
            echo '<p class="no-recordings">No recordings found.</p>';
        }
        ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($searchQuery) ?>">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($searchQuery) ?>" <?= ($i === $page) ? 'class="active"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($searchQuery) ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Public Audio Archive. All rights reserved.</p>
</footer>

<script>
    function playAudio(filePath) {
        var audioPlayer = document.getElementById('audioPlayer');
        var source = audioPlayer.querySelector('source');
        source.src = filePath;
        audioPlayer.load();
        audioPlayer.play();
    }
</script>

</body>
</html>