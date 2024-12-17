<?php
session_start();
include './db/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$newsId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$newsId) {
    header('Location: manaNews.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    // Handle file upload if there's a new file
    $fileAttachment = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $fileExtension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_news.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
            $fileAttachment = $fileName;
        }
    }
    
    // Update the news entry
    if ($fileAttachment) {
        $update_query = "UPDATE news SET newsTitle = ?, newsContent = ?, fileAttachment = ? WHERE newsId = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssi", $title, $content, $fileAttachment, $newsId);
    } else {
        $update_query = "UPDATE news SET newsTitle = ?, newsContent = ? WHERE newsId = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssi", $title, $content, $newsId);
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "News updated successfully!";
        header('Location: manaNews.php');
        exit;
    } else {
        $_SESSION['error'] = "Error updating news!";
    }
}

// Fetch the news entry
$query = "SELECT * FROM news WHERE newsId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $newsId);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();

if (!$news) {
    header('Location: manaNews.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
    <style>
        .edit-container {
            margin: 20px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container edit-container">
        <h2 class="mb-4">Edit News</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" 
                       value="<?php echo htmlspecialchars($news['newsTitle']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content" rows="10"><?php echo htmlspecialchars($news['newsContent']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="attachment" class="form-label">Attachment (Optional)</label>
                <input type="file" class="form-control" id="attachment" name="attachment">
                <?php if ($news['fileAttachment']): ?>
                    <div class="mt-2">
                        Current attachment: 
                        <a href="uploads/<?php echo htmlspecialchars($news['fileAttachment']); ?>" target="_blank">
                            <?php echo htmlspecialchars($news['fileAttachment']); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <a href="manaNews.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update News</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        CKEDITOR.replace('content');
    </script>
</body>
</html>