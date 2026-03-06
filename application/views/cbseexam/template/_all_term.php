<?php
if(!empty($result)){
?>

<div class="table-responsive">  
    <table class="table table-strippedn table-hover mb10">
        <thead>
            <tr class="active">
                <th width="20%"><?php echo $this->lang->line('term'); ?></th>
                <th width="20%"><?php echo $this->lang->line('weightage'); ?></th>
				<th  > </th>
						
            </tr>
        </thead>
        <tbody>
            <?php 
                foreach($result as $key=>$value){
            ?>
            <tr> 
                <th><?php echo $value['name']; ?></th>
                <td><input type="text" class="form-control" name="term_weightage[<?php echo $key; ?>]" value="<?php if(isset($templatedata['term_details'][$key]['weightage'])){ echo $templatedata['term_details'][$key]['weightage']; } ?>"></td>
				<th  > </th>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <table class="table table-strippedn table-hover mb10">
        <thead>
            <tr class="active">
                <th colspan="2"><?php echo $this->lang->line('term'); ?></th>                
                <th colspan="2"><?php echo $this->lang->line('exam_name'); ?></th>                              
                <th class="text-center"><?php echo $this->lang->line('grading'); ?></th>
                <th class="text-center"><?php echo $this->lang->line('teacher_remark'); ?></th>                
                <th class="text-center"><?php echo $this->lang->line('subject_note'); ?></th>                
            </tr>
        </thead>
        <tbody>
            <?php foreach($result as $key=>$value){
                 if($templatedata['marksheet_type'] == "all_term"){ ?>
            <tr>                
                <th><input type="checkbox" class="checkbox checkBoxExam termcheckbox" data-id="<?php echo $key; ?>" name="terms[]" value="<?php echo $key; ?>" <?php if(isset($templatedata['term_exam']) && array_key_exists($key,$templatedata['term_exam'])){ echo "checked"; }?>></td>
                <td><b><?php echo $value['name']; ?></b></th>
                <td colspan="5"></td>
            </tr>
            <?php foreach ($value['exam'] as $examkey => $examvalue) { ?>
            <tr>
                <td colspan="2"></td>
                <td >
                    <input type="checkbox" class="checkbox checkBoxExam examcheckbox_<?php echo $key;?>" name="exam[<?php echo $key;?>][<?php echo $examvalue['id'];?>]"  value="<?php echo $examvalue['id'];?>" <?php if(isset($templatedata['term_exam'][$key]) && array_key_exists($examvalue['id'],$templatedata['term_exam'][$key])){ echo "checked"; }?>>  </td>                
                 <th>   <?php echo $examvalue['name'];?>                      
                </th>           
                <td>
                    <div class="d-flex justify-content-center">
                        <input type="radio" class="checkbox checkBoxExam grading_<?php echo $key;?>" name="grading" <?php if($examvalue['id']==$templatedata['gradeexam_id']){ echo "checked";} ?> value="<?php echo $examvalue['id'];?>">
                    </div>     
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center">
                        <input type="radio" class="checkbox checkBoxExam remark_<?php echo $key;?>" name="teacher_remark" <?php if($examvalue['id']==$templatedata['remarkexam_id']){ echo "checked";} ?> value="<?php echo $examvalue['id'];?>" > 
                    </div>    
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center">
                    <input type="radio" class="checkbox checkBoxExam subject_note_<?php echo $key;?>" name="subject_note" 
                    <?php if($examvalue['id']==$templatedata['subjectnoteexam_id']){ echo "checked";} ?> value="<?php echo $examvalue['id'];?>" > 
                    </div>    
                </td>
            </tr>
            <?php  
            }   

                 }else{
                       ?>
            <tr>                
                <td><input type="checkbox" class="checkbox checkBoxExam termcheckbox" data-id="<?php echo $key; ?>" name="terms[]" value="<?php echo $key; ?>" ></td>
                
                <th>  <?php echo $value['name']; ?></th>
                <td colspan="5"></td>
            </tr>
            <?php foreach ($value['exam'] as $examkey => $examvalue) { ?>
            <tr>
                <td colspan="2"></td>
                <td ><input type="checkbox" class="checkbox checkBoxExam examcheckbox_<?php echo $key;?>" name="exam[<?php echo $key;?>][<?php echo $examvalue['id'];?>]"  value="<?php echo $examvalue['id'];?>" >  </td>                
                <th><?php echo $examvalue['name'];?></th>           
                <td>
                    <div class="d-flex justify-content-center">
                        <input type="radio" class="checkbox checkBoxExam grading_<?php echo $key;?>" name="grading"  value="<?php echo $examvalue['id'];?>">
                    </div>     
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center">
                        <input type="radio" class="checkbox checkBoxExam remark_<?php echo $key;?>" name="teacher_remark"  value="<?php echo $examvalue['id'];?>" > 
                    </div>    
                </td>

                <td class="text-center">
                    <div class="d-flex justify-content-center">
                        <input type="radio" class="checkbox checkBoxExam subject_note_<?php echo $key;?>" name="subject_note"  value="<?php echo $examvalue['id'];?>" > 
                    </div>    
                </td>

            </tr>
            <?php  
              }    

                 }
                 
             }
          ?>
        </tbody>  
    </table> 
</div>

<?php
}else{

?>
<div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
<?php
}
?>

<script type="text/javascript">
    $('.termcheckbox').change(function(){        
        var termcheckbox = $(this).attr('data-id');        
        if(this.checked){
          $(".examcheckbox_" + termcheckbox).prop('checked', true);
        } else {
          $(".examcheckbox_" + termcheckbox).prop('checked', false);
          $(".grading_" + termcheckbox).prop('checked', false);
          $(".remark_" + termcheckbox).prop('checked', false);
          $(".subject_note_" + termcheckbox).prop('checked', false);
        } 
    });    
</script>