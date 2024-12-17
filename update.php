<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'studynest';

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseName = $_POST['courseName'] ?? null;

    if (!$courseName) {
        echo json_encode(['error' => 'Course name is missing']);
        exit;
    }

    // Fetch the courseId using the course name
    $courseQuery = $conn->prepare("SELECT id FROM courses WHERE name = ?");
    $courseQuery->bind_param("s", $courseName);
    $courseQuery->execute();
    $courseResult = $courseQuery->get_result();

    if ($courseResult->num_rows > 0) {
        $courseId = $courseResult->fetch_assoc()['id'];

        // Fetch topics based on the courseId
        $topicsQuery = $conn->prepare("SELECT id, name FROM topics WHERE course_id = ?");
        $topicsQuery->bind_param("i", $courseId);
        $topicsQuery->execute();
        $topicsResult = $topicsQuery->get_result();

        $topics = [];
        while ($row = $topicsResult->fetch_assoc()) {
            $topics[] = $row;
        }

        echo json_encode($topics);
    } else {
        echo json_encode(['error' => 'No course found with the given name']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Profile</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
<section class="upload-note">
   <h2>Upload Note</h2>
   <label for="courses">Select a Course:</label>
   <select id="courses">
      <option value="" disabled selected>Select Course</option>
      <option value="Calculus">Calculus</option>
      <option value="Linear Algebra">Linear Algebra</option>
      <option value="Statistics">Statistics</option>
      <option value="Database Management System">Database Management System</option>
      <option value="Principles of Economics">Principles of Economics</option>
      <option value="Python Programming">Python Programming</option>
   </select>

   <div id="topics-container" style="margin-top: 20px; display: none;">
      <label for="topics">Select a Topic:</label>
      <select id="topics">
         <option value="" disabled selected>Select Topic</option>
      </select>
   </div>

   <div id="note-form" style="margin-top: 20px; display: none;">
      <label for="noteText">Enter Your Note:</label>
      <textarea id="noteText" rows="4" placeholder="Write your note here or upload a file"></textarea>
      <input type="file" id="noteFile" />
      <button onclick="uploadNote()">Upload Note</button>
   </div>
</section>

<a href="#" class="inline-btn">Upload Note</a>

</header>   

<div class="side-bar">
   <div id="close-btn">
      <i class="fas fa-times"></i>
   </div>
   <div class="profile">
      <a href="dashboard.html" class="btn">view profile</a>
   </div>
   <nav class="navbar">
      <a href="index.html"><i class="fas fa-home"></i><span>Home</span></a>
      <a href="about.html"><i class="fas fa-question"></i><span>About</span></a>
      <a href="courses.html"><i class="fas fa-graduation-cap"></i><span>Courses</span></a>
      <a href="teachers.html"><i class="fas fa-chalkboard-user"></i><span>What's New?</span></a>
      <a href="contact.html"><i class="fas fa-headset"></i><span>Contact Us</span></a>
   </nav>
</div>

<section class="user-profile">
   <div class="info">
      <div class="user">
         <img src="images/pic-1.jpg" alt="">
         <h3>Shaikh Anas</h3>
         <p>Student</p>
      </div>
      <div class="box-container">
         <div class="box">
            <div class="flex">
               <i class="fas fa-bookmark"></i>
               <div>
                  <label for="courses">Select a Course:</label>
                  <select id="courses">
                     <option value="" disabled selected>Select Course</option>
                     <option value="1">Calculus</option>
                     <option value="2">Linear Algebra</option>
                     <option value="3">Statistics</option>
                     <option value="4">Database Management System</option>
                     <option value="5">Principles of Economics</option>
                     <option value="6">Python Programming</option>
                  </select>
               </div>
            </div>
            <a href="#" class="inline-btn" id="upload-btn" onclick="displayTopics()">Upload Note</a>
         </div>
         <div class="box" id="topics-container" style="display: none; margin-top: 20px;">
            <div class="flex">
               <i class="fas fa-book-open"></i>
               <div>
                  <label for="topics">Select a Topic:</label>
                  <select id="topics">
                     <option value="" disabled selected>Select Topic</option>
                  </select>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>

<script>
function displayTopics() {
   const coursesDropdown = document.getElementById('courses');
   const courseName = coursesDropdown.options[coursesDropdown.selectedIndex].text;

   if (!courseName || coursesDropdown.value === "") {
      alert('Please select a course first.');
      return;
   }

   fetch('getTopics.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `courseName=${encodeURIComponent(courseName)}`
   })
   .then(response => response.json())
   .then(data => {
      if (data.error) {
         console.error(data.error);
         alert('Error fetching topics: ' + data.error);
         return;
      }

      const topicsDropdown = document.getElementById('topics');
      topicsDropdown.innerHTML = '<option value="" disabled selected>Select Topic</option>';

      if (data.length > 0) {
         data.forEach(topic => {
            const option = document.createElement('option');
            option.value = topic.id;
            option.textContent = topic.name;
            topicsDropdown.appendChild(option);
         });

         document.getElementById('topics-container').style.display = 'block';
      } else {
         alert('No topics available for the selected course.');
         document.getElementById('topics-container').style.display = 'none';
      }
   })
   .catch(error => {
      console.error('Error fetching topics:', error);
      alert('An error occurred while fetching topics.');
   });
}

</script>

</body>
</html>
