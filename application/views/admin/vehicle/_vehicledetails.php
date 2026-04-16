<div class="row">
<input type="hidden" name="id" value="<?php echo set_value('id', $editvehicle->id); ?>" >
        <div class="col-sm-3 col-md-2 col-lg-2">
            <div class="form-group">
                <label ><?php echo  $this->lang->line('vehicle_photo'); ?></label>
                <?php if(!empty($editvehicle->vehicle_photo)){ ?>
                    <img class="profile-user-img img-responsive img-rounded me-0" src="<?php echo $this->media_storage->getImageURL('/uploads/vehicle_photo/'.$editvehicle->vehicle_photo); ?>" alt="User profile picture">
                <?php }else{ ?>
                    <div class="route-bus-icon"><i class="fa fa-bus"></i></div>
                <?php } ?>
                 
            </div>
        </div>

        <div class="col-lg-10 col-md-10 col-sm-9">
            <div class="row">        
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><b><?php echo $this->lang->line('vehicle_number'); ?>: </b><span><?php echo $editvehicle->vehicle_no; ?></span></div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><b><?php echo $this->lang->line('vehicle_model'); ?>: </b><span><?php echo $editvehicle->vehicle_model; ?></span></div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><b><?php echo $this->lang->line('year_made'); ?>: </b><span><?php echo $editvehicle->manufacture_year; ?></span></div>
                </div> 
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><b><?php echo $this->lang->line('registration_number'); ?>: </b><span><?php echo $editvehicle->registration_number; ?></span></div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><b><?php echo $this->lang->line('chasis_number'); ?>: </b><span><?php echo $editvehicle->chasis_number; ?></span></div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><b>Engine Number: </b><span><?php echo $editvehicle->engine_number; ?></span></div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><b><?php echo $this->lang->line('max_seating_capacity'); ?>: </b><span><?php echo $editvehicle->max_seating_capacity; ?></span></div>
                </div> 
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><b><?php echo $this->lang->line('driver_name'); ?>: </b><span><?php echo $editvehicle->driver_name; ?></span></div>
                </div> 
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><b><?php echo $this->lang->line('driver_license'); ?>: </b><span><?php echo $editvehicle->driver_licence; ?></span></div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><b><?php echo $this->lang->line('driver_contact'); ?>: </b><span><?php echo $editvehicle->driver_contact; ?></span></div>
                </div>  
            </div>
            
         </div><!--./col-md-12-->    
    </div><!--./row-->     
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="route-text pb10"><b><?php echo $this->lang->line('note'); ?>: </b><span><?php echo $editvehicle->note; ?></span></div>
                </div>  
            </div>

            <!-- Validity / Expiry Dates -->
            <?php
            $date_fmt = $this->customlib->getSchoolDateFormat();
            $date_sections = [
                'FC Validity'            => ['fc_validity_start',    'fc_validity_end'],
                'Insurance'              => ['insurance_start',       'insurance_end'],
                'Permit Expiry'          => ['permit_expiry_start',   'permit_expiry_end'],
                'Road Tax'               => ['road_tax_start',        'road_tax_end'],
                'Pollution Certificate'  => ['pollution_cert_start',  'pollution_cert_end'],
                'Green Tax'              => ['green_tax_start',       'green_tax_end'],
            ];
            $has_dates = false;
            foreach ($date_sections as $cols) {
                foreach ($cols as $f) {
                    if (!empty($editvehicle->$f)) { $has_dates = true; break 2; }
                }
            }
            if ($has_dates):
            ?>
            <div class="row"><div class="col-sm-12"><hr class="mt5 mb5"><strong><i class="fa fa-calendar"></i> Validity &amp; Expiry Dates</strong></div></div>
            <?php foreach ($date_sections as $title => $cols):
                $start_raw = !empty($editvehicle->{$cols[0]}) ? $editvehicle->{$cols[0]} : '';
                $end_raw   = !empty($editvehicle->{$cols[1]}) ? $editvehicle->{$cols[1]} : '';
                $ts_s = $start_raw ? $this->customlib->dateyyyymmddTodateformat($start_raw) : null;
                $ts_e = $end_raw   ? $this->customlib->dateyyyymmddTodateformat($end_raw)   : null;
                $start_disp = $ts_s ? date($date_fmt, $ts_s) : '—';
                $end_disp   = $ts_e ? date($date_fmt, $ts_e) : '—';
            ?>
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><b><?php echo $title; ?>:</b></div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><span class="text-muted">Start:</span> <?php echo $start_disp; ?></div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="route-text"><span class="text-muted">End:</span> <?php echo $end_disp; ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
