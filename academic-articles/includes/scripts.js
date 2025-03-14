// Function to handle page navigation
function goToPage(maxPage) {
    var pageInput = document.getElementById('page-input').value;
    var page = parseInt(pageInput);
    
    if (isNaN(page) || page < 1) {
        page = 1;
    } else if (page > maxPage) {
        page = maxPage;
    }
    
    // Get current URL parameters
    var urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page_num', page);
    
    // Redirect to the new page
    window.location.href = '?' + urlParams.toString();
}