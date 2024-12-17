let toggleBtn = document.getElementById('toggle-btn');
let body = document.body;
let darkMode = localStorage.getItem('dark-mode');

const enableDarkMode = () =>{
   toggleBtn.classList.replace('fa-sun', 'fa-moon');
   body.classList.add('dark');
   localStorage.setItem('dark-mode', 'enabled');
}

const disableDarkMode = () =>{
   toggleBtn.classList.replace('fa-moon', 'fa-sun');
   body.classList.remove('dark');
   localStorage.setItem('dark-mode', 'disabled');
}

if(darkMode === 'enabled'){
   enableDarkMode();
}

toggleBtn.onclick = (e) =>{
   darkMode = localStorage.getItem('dark-mode');
   if(darkMode === 'disabled'){
      enableDarkMode();
   }else{
      disableDarkMode();
   }
}

let profile = document.querySelector('.header .flex .profile');

document.querySelector('#user-btn').onclick = () =>{
   profile.classList.toggle('active');
   search.classList.remove('active');
}

let search = document.querySelector('.header .flex .search-form');

document.querySelector('#search-btn').onclick = () =>{
   search.classList.toggle('active');
   profile.classList.remove('active');
}

let sideBar = document.querySelector('.side-bar');

document.querySelector('#menu-btn').onclick = () =>{
   sideBar.classList.toggle('active');
   body.classList.toggle('active');
}

document.querySelector('#close-btn').onclick = () =>{
   sideBar.classList.remove('active');
   body.classList.remove('active');
}

window.onscroll = () =>{
   profile.classList.remove('active');
   search.classList.remove('active');

   if(window.innerWidth < 1200){
      sideBar.classList.remove('active');
      body.classList.remove('active');
   }
}

document.addEventListener("DOMContentLoaded", () => {
   // Attach event listener to the Upload Note button
   document.querySelector(".inline-btn").addEventListener("click", () => {
       const courseSelect = document.getElementById("courses");
       const topicsContainer = document.getElementById("topics-container");
       if (courseSelect.value) {
           topicsContainer.style.display = "block"; // Show topics dropdown
           fetchTopics(); // Populate topics based on the selected course
       } else {
           alert("Please select a course first.");
       }
   });
});

function fetchTopics() {
   const courseId = document.getElementById('courses').value;

   if (courseId) {
      fetch('getTopics.php', {
         method: 'POST',
         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
         body: `courseId=${courseId}`
      })
      .then(response => response.json())
      .then(data => {
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
            document.getElementById('topics-container').style.display = 'none';
         }
      })
      .catch(error => console.error('Error fetching topics:', error));
   }
}

function openModal(modalId) {
   document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
   document.getElementById(modalId).style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
   document.getElementById('menu-btn').addEventListener('click', function() {
      openModal('noteModal');
   });
});