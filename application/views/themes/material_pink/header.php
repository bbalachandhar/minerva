

<!---   Guest Signup  --->
<div id="myModal" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-header-small">
                <button type="button" class="close closebtnmodal" data-dismiss="modal">&times;</button>
                <h4 ><?php echo $this->lang->line('guest_registration') ?></h4>
            </div>
            <form action="<?php echo base_url() . 'course/guestsignup' ?>" method="post" class="signupform" id="signupform">
                <div class="modal-body">
                    <div class="form-group">
                        <label><?php echo $this->lang->line('name'); ?></label><small class="req"> *</small>
                        <input type="text" class="form-control reg_name" name="name" id="name" autocomplete="off">
                        <span class="text-danger" id="error_refno"></span>
                    </div>
                    <div class="form-group mb10">
                        <label><?php echo $this->lang->line('email_id'); ?></label><small class="req"> *</small>
                        <input type="text"  class="form-control reg_email"  name="email" id="email" autocomplete="off" >
                        <span class="text-danger" id="error_dob"></span>
                    </div>
                    <div class="form-group mb10">
                        <label><?php echo $this->lang->line('password'); ?></label><small class="req"> *</small>
                        <input type="password"  class="form-control reg_password"  name="password" id="password" autocomplete="off" >
                        <span class="text-danger" id="error_dob"></span>
                    </div>
                    <div id="load_signup_captcha"></div>
                </div>
                <div class="modal-footer">
                    <button type="button"  class="modalclosebtn btn  mdbtn" onclick="openmodal()"><?php echo $this->lang->line('login'); ?></button>
                    <button type="submit" id="signupformbtn" class="onlineformbtn mdbtn" ><?php echo $this->lang->line('signup'); ?> </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!---   Guest Login  --->
<div id="loginmodal" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header modal-header-small">
                <button type="button" class="close closebtnmodal" data-dismiss="modal">&times;</button>
                <h4 class=><?php echo $this->lang->line('guest').' '.$this->lang->line('login') ?> </h4>
            </div>
            <form action="<?php echo site_url('course/guestlogin') ?>" method="post" class="loginform" id="loginform">
                <div class="modal-body">
                    <div class="form-group mb10">
                        <label><?php echo $this->lang->line('email_id'); ?></label><small class="req"> *</small>
                        <input type="text"  class="form-control login_email"  name="username" id="username" autocomplete="off">
                        <span class="text-danger" id="error_dob"></span>
                    </div>
                    <div class="form-group mb10">
                        <label><?php echo $this->lang->line('password'); ?></label><small class="req"> *</small>
                        <input type="password"  class="form-control login_password"  name="password" id="password" autocomplete="off">
                        <input type="hidden"  class="form-control"  name="checkout_status" id="checkout_status"  autocomplete="off" >
                        <span class="text-danger" id="error_dob"></span>
                    </div>
                    <div id="load_login_captcha"></div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="pull-left forgotbtn" data-toggle="modal" data-target="#forgotmodal"><i class="fa fa-key"></i> <?php echo $this->lang->line('forgot_password'); ?></a>
                    <button type="button" class="signup modalclosebtn btn mdbtn" data-dismiss="modal"><?php echo $this->lang->line('signup'); ?> </button>
                    <button type="submit" id="loginformbtn" class="onlineformbtn mdbtn" ><?php echo $this->lang->line('submit'); ?></button>
					
					<div class="col-lg-12 col-md-12">
                        <div class="admin-text">
                            <a href="<?php echo site_url('site/userlogin') ?>" target="_blank" ><i class="fa fa-users"></i><?php echo $this->lang->line('student_parent_login'); ?></a>
                        </div>      
                    </div>
					
                </div>
            </form>
            <form action="<?php echo site_url('course/user_submit_login') ?>" method="post" class="gauthenticate-form" id="gauthenticate-form">
                <div class="modal-body">                   
                  <div class="form-group mb10">
                        <label><?php echo $this->lang->line('verification_code'); ?></label><small class="req"> *</small>
                        <input type="text"  class="form-control gauth_code"  name="gauth_code" id="gauth_code" autocomplete="off" >                       
                        <span class="text-danger" id="error_gauth_code"></span>
                    </div>                    
                </div>
                <div class="modal-footer">
                    <a href="#" class="pull-left forgotbtn" data-toggle="modal" data-target="#forgotmodal"><i class="fa fa-key"></i> <?php echo $this->lang->line('forgot_password'); ?></a>
                    <button type="button" class="signup modalclosebtn btn mdbtn" data-dismiss="modal"><?php echo $this->lang->line('signup'); ?> </button>
                    <button type="submit" id="loginformbtn" class="onlineformbtn mdbtn" data-loading-text="<i class='fa fa-spinner fa-spin '></i> wait..."><?php echo $this->lang->line('submit'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="forgotmodal" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header modal-header-small">
                <button type="button" class="close closebtnmodal" data-dismiss="modal">&times;</button>
                <h4 class=><?php echo $this->lang->line('forgot_password'); ?></h4>
            </div>
            <form action="#" method="post" class="loginform" id="forgotform">
                <div class="modal-body">
                    <div class="form-group mb10">
                        <label><?php echo $this->lang->line('email_id'); ?></label><small class="req"> *</small>
                        <input type="email" class="form-control" name="username" id="email" autocomplete="off">
                        <span class="text-danger" id="error_email"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button"  class="modalclosebtn btn  mdbtn" onclick="openmodal()"><?php echo $this->lang->line('login'); ?></button>                    
                    <button type="submit" id="forgotformbtn" class="onlineformbtn mdbtn" ><?php echo $this->lang->line('submit'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function () { 
    $('#myModal,#forgotmodal,#loginmodal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });
});
</script> 
<script>
$(document).on('change','.currency_list',function(e){ 
    let currency_id=$(this).val();
    $.ajax({
        type: 'POST',
        url: base_url+'welcome/changeCurrencyFormat',
        data: {'currency_id':currency_id},
        dataType: 'json',
        beforeSend: function() {
             
        },
        success: function(data) {          
            window.location.reload();
        },
        error: function(xhr) { // if error occured
    
        },
        complete: function() {
            
        }
     
    });
});
</script>