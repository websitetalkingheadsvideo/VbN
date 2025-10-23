<?php
// Admin interface for managing questionnaire questions
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

include "../includes/connect.php";

// Handle form submissions
if ($_POST) {
    if (isset($_POST["action"])) {
        switch ($_POST["action"]) {
            case "add":
                $sql = "INSERT INTO questionnaire_questions (category, question, answer1, answer2, answer3, answer4, clanWeight1, clanWeight2, clanWeight3, clanWeight4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssssssss", 
                    $_POST["category"], $_POST["question"], $_POST["answer1"], 
                    $_POST["answer2"], $_POST["answer3"], $_POST["answer4"],
                    $_POST["clanWeight1"], $_POST["clanWeight2"], $_POST["clanWeight3"], $_POST["clanWeight4"]
                );
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Question added successfully!";
                } else {
                    $error = "Error adding question: " . mysqli_error($conn);
                }
                break;
                
            case "edit":
                $sql = "UPDATE questionnaire_questions SET category=?, question=?, answer1=?, answer2=?, answer3=?, answer4=?, clanWeight1=?, clanWeight2=?, clanWeight3=?, clanWeight4=? WHERE ID=?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssssssssi", 
                    $_POST["category"], $_POST["question"], $_POST["answer1"], 
                    $_POST["answer2"], $_POST["answer3"], $_POST["answer4"],
                    $_POST["clanWeight1"], $_POST["clanWeight2"], $_POST["clanWeight3"], $_POST["clanWeight4"], $_POST["id"]
                );
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Question updated successfully!";
                } else {
                    $error = "Error updating question: " . mysqli_error($conn);
                }
                break;
                
            case "delete":
                $sql = "DELETE FROM questionnaire_questions WHERE ID=?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $_POST["id"]);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Question deleted successfully!";
                } else {
                    $error = "Error deleting question: " . mysqli_error($conn);
                }
                break;
        }
    }
}

// Get all questions
$result = mysqli_query($conn, "SELECT * FROM questionnaire_questions ORDER BY ID");
$questions = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionnaire Admin - Valley by Night</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/admin_questionnaire.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>🦇 Questionnaire Admin Panel</h1>
            <p>Manage questionnaire questions and clan scoring</p>
            <a href="../admin/admin_panel.php" style="color: #c9a96e;">← Back to Admin Panel</a>
        </div>

        <?php if (isset($message)): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Add New Question Form -->
        <div class="question-form">
            <h2>Add New Question</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category" required>
                        <option value="">Select Category</option>
                        <option value="embrace">Embrace</option>
                        <option value="personality">Personality</option>
                        <option value="perspective">Perspective</option>
                        <option value="powers">Powers</option>
                        <option value="motivation">Motivation</option>
                        <option value="supernatural">Supernatural</option>
                        <option value="secrets">Secrets</option>
                        <option value="fears">Fears</option>
                        <option value="scenario">Scenario</option>
                        <option value="workplace">Workplace</option>
                        <option value="family">Family</option>
                        <option value="social">Social</option>
                        <option value="moral">Moral</option>
                        <option value="power">Power</option>
                        <option value="life">Life</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="question">Question:</label>
                    <textarea name="question" id="question" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="answer1">Answer 1:</label>
                    <input type="text" name="answer1" id="answer1" required>
                </div>
                
                <div class="form-group">
                    <label for="answer2">Answer 2:</label>
                    <input type="text" name="answer2" id="answer2" required>
                </div>
                
                <div class="form-group">
                    <label for="answer3">Answer 3:</label>
                    <input type="text" name="answer3" id="answer3">
                </div>
                
                <div class="form-group">
                    <label for="answer4">Answer 4:</label>
                    <input type="text" name="answer4" id="answer4">
                </div>
                
                <div class="form-group">
                    <label for="clanWeight1">Clan Weight 1 (format: clan:points,clan:points):</label>
                    <input type="text" name="clanWeight1" id="clanWeight1" placeholder="ventrue:3,tremere:2">
                </div>
                
                <div class="form-group">
                    <label for="clanWeight2">Clan Weight 2:</label>
                    <input type="text" name="clanWeight2" id="clanWeight2" placeholder="tremere:3,nosferatu:2">
                </div>
                
                <div class="form-group">
                    <label for="clanWeight3">Clan Weight 3:</label>
                    <input type="text" name="clanWeight3" id="clanWeight3" placeholder="brujah:3,gangrel:2">
                </div>
                
                <div class="form-group">
                    <label for="clanWeight4">Clan Weight 4:</label>
                    <input type="text" name="clanWeight4" id="clanWeight4" placeholder="malkavian:3,nosferatu:2">
                </div>
                
                <button type="submit" class="btn btn-primary">Add Question</button>
            </form>
        </div>

        <!-- Questions List -->
        <div class="questions-table">
            <h2>Existing Questions (<?php echo count($questions); ?>)</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Question</th>
                        <th>Answers</th>
                        <th>Clan Weights</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $question): ?>
                    <tr>
                        <td><?php echo $question["ID"]; ?></td>
                        <td><?php echo ucfirst($question["category"]); ?></td>
                        <td><?php echo substr($question["question"], 0, 60) . "..."; ?></td>
                        <td>
                            <?php 
                            $answers = array_filter([$question["answer1"], $question["answer2"], $question["answer3"], $question["answer4"]]);
                            echo count($answers) . " answers";
                            ?>
                        </td>
                        <td>
                            <?php 
                            $weights = array_filter([$question["clanWeight1"], $question["clanWeight2"], $question["clanWeight3"], $question["clanWeight4"]]);
                            echo count($weights) . " weights";
                            ?>
                        </td>
                        <td>
                            <button onclick="toggleEdit(<?php echo $question["ID"]; ?>)" class="btn btn-secondary">Edit</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm(\"Are you sure you want to delete this question?\");">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $question["ID"]; ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    
                    <!-- Edit Form (Hidden by default) -->
                    <tr id="edit-<?php echo $question["ID"]; ?>" class="edit-form">
                        <td colspan="6">
                            <form method="POST">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo $question["ID"]; ?>">
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                    <div class="form-group">
                                        <label>Category:</label>
                                        <select name="category" required>
                                            <option value="embrace" <?php echo $question["category"] == "embrace" ? "selected" : ""; ?>>Embrace</option>
                                            <option value="personality" <?php echo $question["category"] == "personality" ? "selected" : ""; ?>>Personality</option>
                                            <option value="perspective" <?php echo $question["category"] == "perspective" ? "selected" : ""; ?>>Perspective</option>
                                            <option value="powers" <?php echo $question["category"] == "powers" ? "selected" : ""; ?>>Powers</option>
                                            <option value="motivation" <?php echo $question["category"] == "motivation" ? "selected" : ""; ?>>Motivation</option>
                                            <option value="supernatural" <?php echo $question["category"] == "supernatural" ? "selected" : ""; ?>>Supernatural</option>
                                            <option value="secrets" <?php echo $question["category"] == "secrets" ? "selected" : ""; ?>>Secrets</option>
                                            <option value="fears" <?php echo $question["category"] == "fears" ? "selected" : ""; ?>>Fears</option>
                                            <option value="scenario" <?php echo $question["category"] == "scenario" ? "selected" : ""; ?>>Scenario</option>
                                            <option value="workplace" <?php echo $question["category"] == "workplace" ? "selected" : ""; ?>>Workplace</option>
                                            <option value="family" <?php echo $question["category"] == "family" ? "selected" : ""; ?>>Family</option>
                                            <option value="social" <?php echo $question["category"] == "social" ? "selected" : ""; ?>>Social</option>
                                            <option value="moral" <?php echo $question["category"] == "moral" ? "selected" : ""; ?>>Moral</option>
                                            <option value="power" <?php echo $question["category"] == "power" ? "selected" : ""; ?>>Power</option>
                                            <option value="life" <?php echo $question["category"] == "life" ? "selected" : ""; ?>>Life</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Question:</label>
                                        <textarea name="question" required><?php echo htmlspecialchars($question["question"]); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Answer 1:</label>
                                        <input type="text" name="answer1" value="<?php echo htmlspecialchars($question["answer1"]); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Answer 2:</label>
                                        <input type="text" name="answer2" value="<?php echo htmlspecialchars($question["answer2"]); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Answer 3:</label>
                                        <input type="text" name="answer3" value="<?php echo htmlspecialchars($question["answer3"]); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Answer 4:</label>
                                        <input type="text" name="answer4" value="<?php echo htmlspecialchars($question["answer4"]); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Clan Weight 1:</label>
                                        <input type="text" name="clanWeight1" value="<?php echo htmlspecialchars($question["clanWeight1"]); ?>" placeholder="ventrue:3,tremere:2">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Clan Weight 2:</label>
                                        <input type="text" name="clanWeight2" value="<?php echo htmlspecialchars($question["clanWeight2"]); ?>" placeholder="tremere:3,nosferatu:2">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Clan Weight 3:</label>
                                        <input type="text" name="clanWeight3" value="<?php echo htmlspecialchars($question["clanWeight3"]); ?>" placeholder="brujah:3,gangrel:2">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Clan Weight 4:</label>
                                        <input type="text" name="clanWeight4" value="<?php echo htmlspecialchars($question["clanWeight4"]); ?>" placeholder="malkavian:3,nosferatu:2">
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Question</button>
                                <button type="button" onclick="toggleEdit(<?php echo $question["ID"]; ?>)" class="btn btn-secondary">Cancel</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../js/admin_questionnaire.js"></script>
</body>
</html>
