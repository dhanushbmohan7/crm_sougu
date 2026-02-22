<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(document).ready(function () {

    // ---------------------------
    // 1. RESTORE STATE ON LOAD
    // ---------------------------
    const savedState = localStorage.getItem("sidebarCollapsed");

    if (savedState === "true") {
        $("#sidebar").addClass("collapsed");
        $("#content").addClass("expanded");
        $("#topNavbar").addClass("expanded");
    }

    // ---------------------------
    // 2. ON CLICK: TOGGLE + SAVE
    // ---------------------------
    $("#toggleSidebar").click(function () {

        $("#sidebar").toggleClass("collapsed");
        $("#content").toggleClass("expanded");
        $("#topNavbar").toggleClass("expanded");

        // save state in localStorage
        const isCollapsed = $("#sidebar").hasClass("collapsed");
        localStorage.setItem("sidebarCollapsed", isCollapsed);
    });

});
</script>
