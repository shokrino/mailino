jQuery(document).ready(function($) {
    $('.mailino-color-picker').wpColorPicker();
});
document.addEventListener("DOMContentLoaded", function() {
    var tabs = document.querySelectorAll(".nav-tab-wrapper a");
    var contents = document.querySelectorAll(".tab-content");

    tabs.forEach(function(tab) {
        tab.addEventListener("click", function(e) {
            e.preventDefault();
            tabs.forEach(function(t) {
                t.classList.remove("nav-tab-active");
            });
            tab.classList.add("nav-tab-active");

            contents.forEach(function(content) {
                content.style.display = "none";
            });

            var activeContent = document.querySelector(tab.getAttribute("href"));
            activeContent.style.display = "block";
        });
    });
});