function goToPage(totalPages) {
    let pageInput = document.getElementById("page-input").value;
    let search = "<?php echo esc_js($search); ?>";
    let searchBy = "<?php echo esc_js($search_by); ?>";

    if (pageInput >= 1 && pageInput <= totalPages) {
        window.location.href = "?page_num=" + pageInput + "&search=" + encodeURIComponent(search) + "&search_by=" + encodeURIComponent(searchBy);
    } else {
        alert("Please enter a valid page number between 1 and " + totalPages);
    }
}

function toggleAbstract(button) {
    const fullAbstract = button.previousElementSibling;
    const shortAbstract = fullAbstract.previousElementSibling;

    if (fullAbstract.style.display === 'none') {
        fullAbstract.style.display = 'inline';
        button.textContent = 'Read Less';
    } else {
        fullAbstract.style.display = 'none';
        button.textContent = 'Read More';
    }
}