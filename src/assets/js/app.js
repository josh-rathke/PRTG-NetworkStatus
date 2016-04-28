$(document).foundation();

// Change color of Sidebar Icon when Section is Active
function sidebar_location_highlighter() {
    $('.sidebar a').parent().parent().removeClass('active-section');
    $('.sidebar a.active').parent().parent().addClass('active-section');
}

$(document).ready( sidebar_location_highlighter );
$(window).scroll( sidebar_location_highlighter );