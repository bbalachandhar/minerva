// Prevent AdminLTE tree from initializing
var AdminLTEOptions = {
    enableControlSidebar: true,
    controlSidebarOptions: {
        toggleBtnSelector: "[data-toggle='control-sidebar']",
        selector: ".control-sidebar",
        slide: true
    },
    sidebarExpandOnHover: false,
    sidebarSlimScroll: true
};

$(document).ready(function() {
    // if ($('.purchasemodal').length <= 0 && chk_validate == "") {
        // $("#activelicmodal").modal('show');
    // }
    // $(document).on('click', '.purchasemodal', function() {
    //     $("#activelicmodal").modal('show');
    // })

    // Custom sidebar menu handler - replaces AdminLTE tree
    $('.sidebar').on('click', 'li a', function(e) {
        var $this = $(this);
        var $next = $this.next();
        var $parentLi = $this.parent('li');
        var href = $this.attr('href');
        
        // Check if this is a parent menu item (has treeview class and submenu)
        if ($parentLi.hasClass('treeview') && $next.is('.treeview-menu')) {
            // This is a parent menu toggle - handle expand/collapse
            e.preventDefault();
            e.stopPropagation();
            
            if ($parentLi.hasClass('active')) {
                $next.slideUp(300, function() {
                    $next.removeClass('menu-open');
                });
                $parentLi.removeClass('active menu-open');
            } else {
                var $parentUl = $this.parents('ul').first();
                var $openMenus = $parentUl.find('ul:visible').slideUp(300);
                $openMenus.removeClass('menu-open');
                $parentUl.find('li.treeview').removeClass('active menu-open');
                
                $next.slideDown(300, function() {
                    $next.addClass('menu-open');
                    $parentLi.addClass('active menu-open');
                });
            }
            return false;
        }
        
        // This is a submenu item with a real URL - allow navigation
        if (href && href !== '#' && href !== 'javascript:void(0)' && href.indexOf('javascript:') === -1) {
            // Allow normal navigation
            return true;
        }
        
        // Prevent default for links with no href
        e.preventDefault();
        return false;
    });
});