$(document).ready(function() {
    var formmodified = 0;
    $('form *').on('change input', function(){
        formmodified = 1;
    });
    window.onbeforeunload = function() {
        if (formmodified == 1) {
            return "You have unsaved changes. Do you wish to leave the page?";
        }
    };
    $('form').on('submit', function() {
        formmodified = 0;
    });
});