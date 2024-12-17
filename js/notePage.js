document.addEventListener('DOMContentLoaded', function () {
    // Sample data (This should be fetched from your backend using PHP and database)
    const topicId = 1; // Example: Dynamically set based on the topic clicked
    const topicName = 'Calculus'; // Example: Set based on the topic name
    
    // Set the topic name in the header
    document.getElementById('topicName').innerText = topicName;

    // Fetch notes for the topic
    fetchNotes(topicId);

    // Handle modal visibility for comments
    const modal = document.getElementById('commentModal');
    const closeModal = document.getElementById('closeModal');
    closeModal.onclick = function () {
        modal.style.display = 'none';
    }

    // Function to fetch notes (In real implementation, this should be an AJAX call)
    function fetchNotes(topicId) {
        // Simulate fetching data for notes (should be fetched from PHP backend)
        const notes = [
            { noteId: 1, title: 'Limits in Calculus', description: 'Notes on limits...', likes: 5, comments: ['Great explanation!', 'Very helpful.'] },
            { noteId: 2, title: 'Derivatives', description: 'Notes on derivatives...', likes: 3, comments: ['Nice notes!', 'I learned a lot!'] },
        ];

        const notesList = document.getElementById('notesList');
        notesList.innerHTML = ''; // Clear existing notes

        notes.forEach(note => {
            const noteCard = document.createElement('div');
            noteCard.classList.add('note-card');
            noteCard.innerHTML = `
                <h2>${note.title}</h2>
                <p>${note.description}</p>
                <div class="buttons">
                    <button onclick="downloadNote(${note.noteId})">Download</button>
                    <button id="likeBtn${note.noteId}" onclick="likeNote(${note.noteId})">Like (${note.likes})</button>
                    <button onclick="viewComments(${note.noteId})">View Comments</button>
                </div>
            `;
            notesList.appendChild(noteCard);
        });
    }

    // Handle like button functionality
    function likeNote(noteId) {
        const likeButton = document.getElementById(`likeBtn${noteId}`);
        likeButton.classList.toggle('liked');
        // In a real application, you would update the likes in the database via an AJAX call
        let newLikes = likeButton.classList.contains('liked') ? 1 : 0; // Example of toggling
        likeButton.innerText = `Like (${newLikes})`;
    }

    // Show comments when 'View Comments' is clicked
    function viewComments(noteId) {
        const commentsContainer = document.getElementById('commentsContainer');
        commentsContainer.innerHTML = ''; // Clear previous comments
        const noteComments = ['Great explanation!', 'Very helpful.']; // Fetch from DB

        noteComments.forEach(comment => {
            const commentDiv = document.createElement('div');
            commentDiv.innerText = comment;
            commentsContainer.appendChild(commentDiv);
        });

        modal.style.display = 'flex'; // Open the modal
    }

    // Add new comment
    document.getElementById('addCommentBtn').addEventListener('click', function () {
        const newComment = document.getElementById('newComment').value;
        if (newComment) {
            const commentDiv = document.createElement('div');
            commentDiv.innerText = newComment;
            document.getElementById('commentsContainer').appendChild(commentDiv);
            document.getElementById('newComment').value = ''; // Clear textarea
            // In real app, the new comment would be posted to the server
        }
    });

    // Simulate downloading a note (just for UI purposes)
    function downloadNote(noteId) {
        alert(`Downloading note ${noteId}`);
        // Here, you would trigger an actual download process (e.g., fetch the note from the server)
    }

    // Function to handle the button click that redirects to the questions page
    function goToQuestionsPage() {
        window.location.href = 'questions_page.php'; // Redirect to the questions page
    }
});

function likeNote(noteId) {
    // Create a new XMLHttpRequest object
    var xhr = new XMLHttpRequest();
    
    // Open a POST request to update the like count
    xhr.open("POST", "update_likes.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // Send the noteId to the server
    xhr.send("noteId=" + noteId);

    // Update the button's like count once the request is completed
    xhr.onload = function() {
        if (xhr.status == 200) {
            var response = xhr.responseText;
            // Here you would update the like count in the DOM
            // For simplicity, we reload the page to reflect the updated like count
            location.reload();
        }
    };
}
