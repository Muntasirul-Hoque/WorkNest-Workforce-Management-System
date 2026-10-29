// Leave page uses server-side form submission (POST)
// No AJAX needed for this page currently.

document.addEventListener("DOMContentLoaded", function () {
    // Set min date to today
    const today = new Date().toISOString().split("T")[0];
    const sdInput = document.querySelector('input[name="emp_sd"]');
    const edInput = document.querySelector('input[name="emp_ed"]');
    
    if (sdInput) sdInput.min = today;
    if (edInput) edInput.min = today;
});