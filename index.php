<?php
// --- CONFIGURATION ---
// Set the timezone based on your location.
date_default_timezone_set('Asia/Bahrain');
define('DATA_FILE', __DIR__ . '/conference_data.json');

// --- FUNCTIONS ---

/**
 * Reads and decodes data from the JSON file.
 * Creates the file with default data if it doesn't exist.
 * @return array The decoded data.
 */
function getData()
{
    if (!file_exists(DATA_FILE)) {
        $defaultData = ['title' => 'Meeting Title', 'questions' => []];
        file_put_contents(
            DATA_FILE, json_encode(
                $defaultData,
            ));
        return $defaultData;
    }
    $jsonData = file_get_contents(DATA_FILE);
    return json_decode($jsonData, true);
}

/**
 * Encodes and saves data to the JSON file.
 * @param array $data The data to save.
 */
function saveData($data)
{
    file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

// --- CONTROLLER LOGIC ---

// Initialize a variable for success messages
$successMessage = '';

// Handle POST requests for updating data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = getData();

    // Check which action is being performed
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'set_title':
                if (!empty($_POST['title'])) {
                    $data['title'] = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
                    $successMessage = 'Title has been updated successfully.';
                }
                break;
            case 'add_question':
                if (!empty($_POST['question'])) {
                    $newQuestion = [
                        'text' => htmlspecialchars($_POST['question'], ENT_QUOTES, 'UTF-8'),
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    $data['questions'][] = $newQuestion;
                    $successMessage = 'Your question has been submitted successfully.';
                }
                break;
            case 'delete_question':
                if (isset($_POST['question_index'])) {
                    $index = (int)$_POST['question_index'];
                    if (isset($data['questions'][$index])) {
                        array_splice($data['questions'], $index, 1);
                        $successMessage = 'Question has been deleted.';
                    }
                }
                break;
        }
    }

    saveData($data);

    // Redirect to the same page to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
    exit;
}


// Determine the view (user or admin)
$isAdminView = isset($_GET['view']) && $_GET['view'] === 'admin';
$conferenceData = getData();
$meetingTitle = $conferenceData['title'] ?? 'Meeting Title';
$questions = $conferenceData['questions'] ?? [];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-T">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CALLS - SIDEPANEL</title>
    <style>
        /* import tajawal font */
            
        :root {
            --background-color: #121212;
            --surface-color: #1e1e1e;
            --primary-color: #bb86fc;
            --primary-variant-color: #3700b3;
            --secondary-color: #03dac6;
            --text-color: #e1e1e1;
            --border-color: #333333;
            --error-color: #cf6679;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Tajawal", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 40px);
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            flex-grow: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 20px;
        }

        .header .logo {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
            fill: var(--primary-color);
        }

        .header h1 {
            margin: 0;
            color: var(--text-color);
            font-size: 1.5em;
            font-weight: 500;
        }

        .card {
            background-color: var(--surface-color);
            border-radius: 8px;
            padding: 20px;
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .card h2 {
            margin-top: 0;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.9em;
            color: #b3b3b3;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            background-color: var(--background-color);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--text-color);
            box-sizing: border-box;
            font-size: 1em;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: #000;
        }

        .btn-primary:hover {
            background-color: #a966f8;
        }

        .btn-danger {
            background-color: var(--error-color);
            color: #000;
            padding: 5px 10px;
            font-size: 0.8em;
            width: auto;
        }

        .question-list {
            list-style: none;
            padding: 0;
        }

        .question-item {
            background-color: var(--background-color);
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .question-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .question-time {
            font-size: 0.8em;
            color: #888;
        }

        .question-text {
            word-wrap: break-word;
        }

        .no-questions {
            text-align: center;
            color: #888;
            padding: 20px;
            border: 1px dashed var(--border-color);
            border-radius: 4px;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            margin-top: 30px;
            border-top: 1px solid var(--border-color);
            color: #888;
            font-size: 0.9em;
        }

        .footer-title {
            font-weight: bold;
            color: #aaa;
        }
    </style>
</head>

<body>

    <div class="container">
        <header class="header">
            <img src="https://github.com/aldoyh/aldoyh/blob/main/logos-n-beyond/doy-tech-powered-tiny.png?raw=true" alt="Logo" class="logo">
            <h1><?= htmlspecialchars($meetingTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        </header>

        <main>
            <?php if ($isAdminView): ?>
                <!-- ADMIN VIEW -->
                <div class="card">
                    <h2>Admin Controls</h2>
                    <form action="<?= $_SERVER['PHP_SELF'] ?>?view=admin" method="post">
                        <input type="hidden" name="action" value="set_title">
                        <div class="form-group">
                            <label for="title">Set Meeting Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($meetingTitle, ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Title</button>
                    </form>
                </div>
                <div class="card">
                    <h2>Submitted Questions (<?= count($questions) ?>)</h2>
                    <?php if (empty($questions)): ?>
                        <p class="no-questions">No questions have been submitted yet.</p>
                    <?php else: ?>
                        <ul class="question-list">
                            <?php foreach (array_reverse($questions) as $index => $question):
                                // Original index must be calculated after reversing
                                $originalIndex = count($questions) - 1 - $index;
                            ?>
                                <li class="question-item">
                                    <div class="question-item-header">
                                        <span class="question-time"><?= $question['timestamp'] ?></span>
                                        <form action="<?= $_SERVER['PHP_SELF'] ?>?view=admin" method="post" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_question">
                                            <input type="hidden" name="question_index" value="<?= $originalIndex ?>">
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                    <p class="question-text"><?= nl2br($question['text']) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- USER VIEW -->
                <div class="card">
                    <h3>Links & Docs</h3>
                    <ul>
                        <?php
                        // Pushed or Saved Links to Pages, Files or Videos

                        ?>
                    </ul>
                </div>
                <div class=" card">
                                <h2>Ask a Private Question</h2>
                                <p style="font-size: 0.9em; color: #b3b3b3; margin-top:-10px; margin-bottom: 20px;">Your question will be sent privately to the meeting host.</p>
                                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
                                    <input type="hidden" name="action" value="add_question">
                                    <div class="form-group">
                                        <label for="question">Your Question</label>
                                        <textarea id="question" name="question" class="form-control" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit Question</button>
                                </form>
                </div>

            <?php endif; ?>
        </main>
    </div>

    <footer class="footer">
        <div class="footer-title"><?= htmlspecialchars($meetingTitle, ENT_QUOTES, 'UTF-8') ?></div>
        <div><?= date('l, F j, Y') ?> &bull; <?= date('T') ?></div>
    </footer>

</body>

</html>