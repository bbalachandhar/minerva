 $(document).on('focus', ':input', function() {
     $(this).attr('autocomplete', 'off');
 });


modal_click_disabled=(...params)=>{

  for (i=0; i<params.length; i++) {
  
         $('#'+params[i]).modal({
         backdrop: 'static',
         keyboard: false,
         show: false
     });
  }


}

function modal_loader_div(){
    var div_top = $('<div/>');
    var div_model = $('<div/>').addClass('modal_body_loader');
    var sub_img = $('<img/>').attr('src', 'https://dev.webfeb.com/ss640dev/backend/images/chatloading.gif').appendTo(div_model);
    var div_loader = $('<div/>').addClass('modal_body_loader_fade');
    div_top.append(div_loader).append(div_model);
    return div_top;
}

    function reset_form(selector){
        $(selector).find('input:text, input:password, input:file, select, textarea').val('');
        $(selector).find('input:radio, input:checkbox').prop('checked',false);
        $(selector +" select option").prop("selected", false);
    }

function ensureBootstrap(callback) {
    if ($.fn.modal) {
        callback();
        return;
    }
    var scriptId = 'bootstrap-js';
    if (!document.getElementById(scriptId)) {
        var script = document.createElement('script');
        script.id = scriptId;
        script.src = (typeof baseurl === 'string' ? baseurl : '/') + 'backend/bootstrap/js/bootstrap.min.js';
        document.head.appendChild(script);
    }
    var tries = 0;
    var timer = setInterval(function() {
        tries += 1;
        if ($.fn.modal) {
            clearInterval(timer);
            callback();
        } else if (tries > 50) {
            clearInterval(timer);
        }
    }, 100);
}

function initModal($el, options) {
    if ($.fn.modal) {
        $el.modal(options);
    }
}


 $(document).ready(function() {
     console.log('school-custom.js ready. $.fn.modal exists:', typeof $.fn.modal);
     console.log('sessionModal element found:', $('#sessionModal').length);
     
       $('body').popover({
    selector: '[data-toggle="popover"]',
    trigger: 'hover',
    container: 'body',
    html: true,
    content: function () {
         return $(this).closest('td').find('.fee_detail_popover').html();
    }
});
     initModal($('#sessionModal'), {
         backdrop: 'static',
         keyboard: false,
         show: false
     });
     console.log('Session modal initialized. Click handlers attached.');
     
     $(document).on('click', '[data-target="#sessionModal"]', function(e) {
         console.log('Pencil icon clicked!');
         e.preventDefault();
         console.log('Showing modal. $.fn.modal:', typeof $.fn.modal);
         $('#sessionModal').modal('show');
     });
     $(document).on('click', '.drop5', function(e) {
         e.preventDefault();
         var $trigger = $(this);
         ensureBootstrap(function() {
             if ($.fn.dropdown) {
                 $trigger.dropdown('toggle');
             }
         });
     });
     initModal($('#activelicmodal'), {
         backdrop: false,
         keyboard: false,
         show: false
     });
       $('#activelicmodal').on('show.bs.modal', function(event) {
         $('#purchase_code').trigger("reset");
          $('.lic_modal-body .error_message').html("");
        
       });
     $('#sessionModal').on('show.bs.modal', function(event) {
         var $modalDiv = $(event.delegateTarget);
         $('.sessionmodal_body').html("");
         $.ajax({
             type: "POST",
             url: baseurl + "admin/admin/getSession",
             dataType: 'text',
             data: {},
             beforeSend: function() {
                 $modalDiv.addClass('modal_loading');
             },
             success: function(data) {
                 $('.sessionmodal_body').html(data);
             },
             error: function(xhr) { // if error occured
                 $modalDiv.removeClass('modal_loading');
             },
             complete: function() {
                 $modalDiv.removeClass('modal_loading');
             },
         });
     })
     $(document).on('click', '.submit_session', function() {
         var $this = $(this);
         var datastring = $("form#form_modal_session").serialize();
         $.ajax({
             type: "POST",
             url: baseurl + "admin/admin/updateSession",
             dataType: "json",
             data: datastring,
             beforeSend: function() {
                 $this.button('loading');
             },
             success: function(data) {
                 if (data.status == 1) {
                     $('#sessionModal').modal('hide');
                     window.location.href = baseurl + "admin/admin/dashboard";
                       successMsg(data.message);
                 }
             },
             error: function(xhr) {
                 $this.button('reset');
             },
             complete: function() {
                 $this.button('reset');
             },
         });
     });
    
     //=============Sticky header==============
     $('#alert').affix({
         offset: {
             top: 10,
             bottom: function() {}
         }
     })
     $('#alert2').affix({
         offset: {
             top: 20,
             bottom: function() {}
         }
     })
     //========================================
     //==============User Quick session============
     initModal($('#user_sessionModal'), {
         backdrop: 'static',
         keyboard: false,
         show: false
     });
     $('#user_sessionModal').on('show.bs.modal', function(event) {
         var $modalDiv = $(event.delegateTarget);
         $('.user_sessionmodal_body').html("");
         $.ajax({
             type: "POST",
             url: baseurl + "common/getSudentSessions",
             dataType: 'text',
             data: {},
             beforeSend: function() {
                 $modalDiv.addClass('modal_loading');
             },
             success: function(data) {
                 $('.user_sessionmodal_body').html(data);
             },
             error: function(xhr) { // if error occured
                 $modalDiv.removeClass('modal_loading');
             },
             complete: function() {
                 $modalDiv.removeClass('modal_loading');
             },
         });
     });
     $(document).on('click', '.submit_usersession', function() {
         var $this = $(this);
         var datastring = $("form#form_modal_usersession").serialize();
         $.ajax({
             type: "POST",
             url: baseurl + "common/updateSession",
             dataType: "json",
             data: datastring,
             beforeSend: function() {
                 $this.button('loading');
             },
             success: function(data) {
                 if (data.status == 1) {
                     $('#sessionModal').modal('hide');
                     window.location.href = baseurl + "user/user/dashboard";
                      successMsg(data.message);
                 }
             },
             error: function(xhr) {
                 $this.button('reset');
             },
             complete: function() {
                 $this.button('reset');
             },
         });
     });
     //====================
     initModal($('#commanSessionModal'), {
         backdrop: 'static',
         keyboard: false,
         show: false
     });
     $('#commanSessionModal').on('show.bs.modal', function(event) {
         var $modalDiv = $(event.delegateTarget);
         $('.commonsessionmodal_body').html("");
         $.ajax({
             type: "POST",
             url: baseurl + "common/getAllSession",
             dataType: 'text',
             data: {},
             beforeSend: function() {
                 $modalDiv.addClass('modal_loading');
             },
             success: function(data) {
                 $('.commonsessionmodal_body').html(data);
             },
             error: function(xhr) { // if error occured
                 $modalDiv.removeClass('modal_loading');
             },
             complete: function() {
                 $modalDiv.removeClass('modal_loading');
             },
         });
     });
     $(document).on('click', '.submit_common_session', function() {
         var $this = $(this);
         var datastring = $("form#form_modal_commonsession").serialize();
         $.ajax({
             type: "POST",
             url: baseurl + "common/updateSession",
             dataType: "json",
             data: datastring,
             beforeSend: function() {
                 $this.button('loading');
             },
             success: function(data) {
                 if (data.status == 1) {
                     $('#sessionModal').modal('hide');
                     window.location.href = data.redirect_url;
                   successMsg(data.message);
                 }
             },
             error: function(xhr) {
                 $this.button('reset');
             },
             complete: function() {
                 $this.button('reset');
             },
         });
     });
     $("#purchase_code").submit(function(e) {
         var form = $(this);
         var url = form.attr('action');
         var $this = $(this);
         var $btn = $this.find("button[type=submit]");
         $.ajax({
             type: "POST",
             url: url,
             data: form.serialize(),
             dataType: 'JSON',
             beforeSend: function() {
                  $('.lic_modal-body .error_message').html("");
                 $btn.button('loading');
             },
             success: function(response, textStatus, xhr) {


                 if (xhr.status != 200) {
                     var $newmsgDiv = $("<div/>") // creates a div element              
                         .addClass("alert alert-danger") // add a class
                         .html(response.response);
                     $('.lic_modal-body .error_message').append($newmsgDiv);
                 }else if(xhr.status == 200){

                 if (response.status == 2) {
                     $.each(response.error, function(key, value) {
                         $('#input-' + key).parents('.form-group').find('#error').html(value);
                     });
                 }else if (response.status == 1) {
                     successMsg(response.message);
                     window.location.href=baseurl+'admin/admin/dashboard';
                     $('#activelicmodal').modal('hide');
                 }
             }
                 
             },
             error: function(xhr, status, error) {
               $btn.button('reset');
               var r = jQuery.parseJSON(xhr.responseText);          
               var $newmsgDiv = $("<div/>") // creates a div element              
                         .addClass("alert alert-danger") // add a class
                         .html(r.response);
                     $('.lic_modal-body .error_message').append($newmsgDiv);
              
             },
             complete: function() {
                 $btn.button('reset');
             },
         });
         e.preventDefault();
     });
 });

 
 $(document).ready(function () {
     initModal($('#andappModal'), {
         backdrop: 'static',
         keyboard: false,
         show: false
     });
       $('#andappModal').on('hidden.bs.modal', function(e) { 
         $('#andappModal .andapp_modal-body .alert-danger').remove();
         $('#andappModal .input-error').html("");        
       }) ;

      $("#andapp_code").on('submit', (function (e) {
        e.preventDefault();

        var _this = $(this);
        var $this = _this.find("button[type=submit]:focus");

        $.ajax({
             type: "POST",
             url: _this.attr('action'),
             data: _this.serialize(),
             dataType: 'JSON',
            beforeSend: function () {
                $('.andapp_modal-body .error_message').html("");
                $("[class^='input-error']").html("");
                $this.button('loading');

            },
             success: function(response, textStatus, xhr) {
                 if (xhr.status != 200) {
                     var $newmsgDiv = $("<div/>") // creates a div element
                         .addClass("alert alert-danger") // add a class
                         .html(response.response);
                     $('.lic_modal-body .error_message').append($newmsgDiv);
                 }else if(xhr.status == 200){

                 if (response.status == 2) {
                     $.each(response.error, function(key, value) {
                         $('#input-' + key).parents('.form-group').find('#error').html(value);
                     });
                 }else if (response.status == 1) {
                     successMsg(response.message);
                     window.location.href=baseurl+'schsettings';
                     $('#andappModal').modal('hide');
                 }
             }
             },
            error: function (xhr) { // if error occured
                 $this.button('reset');
               var r = jQuery.parseJSON(xhr.responseText);
               var $newmsgDiv = $("<div/>") // creates a div element
                         .addClass("alert alert-danger") // add a class
                         .html(r.response);
                     $('.andapp_modal-body .error_message').append($newmsgDiv);
            },
            complete: function () {
                $this.button('reset');
            }

        });
    }));
    initModal($('#addonModal'), {
        backdrop: 'static',
        keyboard: false,
        show: false
    });
        $('#addonModal').on('shown.bs.modal', function(e) { 
        $('.addon_type',this).val($(e.relatedTarget).data('addon'));
        $('.addon_version',this).val($(e.relatedTarget).data('addonVersion'));
       }) ;
          $('#addonModal').on('hidden.bs.modal', function(e) { 
          $('#addonModal .addon_modal-body .alert-danger').remove();
          $('#addonModal .input-error').html("");
          $('.addon_type',this).val("");
          $('.addon_version',this).val("");

       }) ;

      $("#addon_verify").on('submit', (function (e) {
        e.preventDefault();

        var _this = $(this);
        var $this = _this.find("button[type=submit]:focus");

        $.ajax({
             type: "POST",
             url: _this.attr('action'),
             data: _this.serialize(),
             dataType: 'JSON',
            beforeSend: function () {
                $('.addon_modal-body .error_message').html("");
                $("[class^='input-error']").html("");
                $this.button('loading');

            },
             success: function(response, textStatus, xhr) {
                 if (xhr.status != 200) {
                     var $newmsgDiv = $("<div/>") // creates a div element
                         .addClass("alert alert-danger") // add a class
                         .html(response.response);
                     $('.addon_modal-body .error_message').append($newmsgDiv);
                 }else if(xhr.status == 200){

                 if (response.status == 2) {
                     $.each(response.error, function(key, value) {
                         $('#input-' + key).parents('.form-group').find('#error').html(value);
                     });
                 }else if (response.status == 1) {
                     successMsg(response.message);
                     window.location.href=response.back;
                     $('#addonModal').modal('hide');
                 }
             }
             },
            error: function (xhr) { // if error occured
                 $this.button('reset');
               var r = jQuery.parseJSON(xhr.responseText);
               var $newmsgDiv = $("<div/>") // creates a div element
                         .addClass("alert alert-danger") // add a class
                         .html(r.response);
                     $('.addon_modal-body .error_message').append($newmsgDiv);
            },
            complete: function () {
                $this.button('reset');
            }

        });
    }));
      
    });
