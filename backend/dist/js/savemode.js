$(document).ready(function() {
    var formmodified = 0;

    $('form *').on('change input', function() {
        formmodified = 1;
    });

    $('form').on('submit', function() {
        formmodified = 0;
    });

    $(document).on('click', 'a[href]', function(e) {
        if (!formmodified) return;
        var href = $(this).attr('href');
        if (!href || href === '#' || href.charAt(0) === '#' || href === 'javascript:void(0)' || href.indexOf('javascript:') === 0) return;
        if ($(this).attr('target') === '_blank') return;
        if ($(this).attr('data-toggle')) return;

        e.preventDefault();
        var link = href;
        swal({
            title: 'Unsaved Changes',
            text: 'You have unsaved changes. Do you want to leave without saving?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Leave Page',
            cancelButtonText: 'Stay on Page'
        }, function(isConfirm) {
            if (isConfirm) {
                formmodified = 0;
                window.location.href = link;
            }
        });
    });

    window.onbeforeunload = function() {
        if (formmodified == 1) {
            return true;
        }
    };
});