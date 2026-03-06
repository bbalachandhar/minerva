    <div class="spaceb60 table-responsive" style="padding-top: 5px;margin: auto;">
       <?php
         if (isset($student_result)) {
            if (!empty($student_result)) { ?>
             <div class="" id="div_print" style="widows: 100%;">
               <h3 style="text-align: center;"><?php echo $this->lang->line('report_card'); ?>
               <br>
               <small><?php echo $exam->name; ?></small>
               </h3>
                <table width="100%">
                  <tr>
                     <td> <b><?php echo $this->lang->line('student_name'); ?> : </b>
                      <?php echo $student['firstname'] . " " . $student['middlename'] . " " . $student['lastname']; ?></td>
                     <td> <b><?php echo $this->lang->line('roll_no'); ?> :  </b><?php echo $student['roll_no']; ?></td>
                     </tr>
                     <tr>
                     <td> <b><?php echo $this->lang->line('class'); ?> : </b>
                      <?php echo $student['class'] . " (" . $student['section'] . ")";  ?></td>
                     <td><b><?php echo $this->lang->line('admission_no'); ?> : </b>
                      <?php echo $student['admission_no']; ?></td>
                  </tr>
                </table>
                  <table  width="100%" style="border-collapse: collapse; cellspacing: 0;padding-top: 10px;">
                   <thead>
                      <tr  style="border: 1px solid gray; !important; padding: 4px !important;">
                           <th style="text-align: left !important; vertical-align: top !important; border: 1px solid gray; !important; padding: 4px !important;">
                              <?php echo $this->lang->line('subject'); ?>
                           </th>
                         <?php
                           foreach ($exam_assessments as $subject_assesment_key => $subject_assesment_value) { ?>
                            <th  style="border: 1px solid gray; !important; padding: 4px !important;">
                               <?php
                                $subject_assesment_code = ($subject_assesment_value->code != "") ? " (" . $subject_assesment_value->code . ")" : "";
                                 echo $subject_assesment_value->name . $subject_assesment_code;
                                 echo "<br/>";
                                 echo  "(" . $this->lang->line('max_marks') . ' ' . $subject_assesment_value->maximum_marks . ")";
                                 ?>
                            </th>
                        <?php
                           }
                        ?>
                        <th style="border: 1px solid gray; !important; padding: 4px !important;" >
                            <?php echo $this->lang->line('total'); ?>
                         </th>
                         <th  style="border: 1px solid gray; !important; padding: 4px !important;">
                            <?php echo $this->lang->line('grade'); ?>
                         </th>
                      </tr>
                   </thead>
                   <tbody>
                      <?php
                        $total_marks = 0;
                        $total_max_marks = 0;
                        foreach ($subjects as $subject_key => $subject_value) {
                           $subject_total = 0;
                           $subject_max_total = 0;
                        ?>
                         <tr  style="border: 1px solid gray; !important; padding: 4px !important;">
                            <td   style="border: 1px solid gray ;!important; padding: 4px !important;">
                               <?php 
                                $subjectcode = ($subject_value->subject_code != "") ? " (" . $subject_value->subject_code . ")" : "";
                                
                                echo $subject_value->subject_name . $subjectcode; ?>
                            </td>
                            <?php
                              foreach ($exam_assessments as $subject_assessment_key => $subject_assessment_value) { ?>
                               <td  style="text-align: center;border: 1px solid gray; !important; padding: 4px !important;">
                                  <?php
                                    $assessment_exists =  find_subject_assessment_exists($subject_assessments, $subject_value->id, $subject_assessment_value->id);

                                    if ($assessment_exists) {
                                       $assessment_array = findAssessmentValue($subject_value->subject_id, $subject_assessment_value->id, $student_result);
                                       echo ($assessment_array['is_absent']) ? $this->lang->line('abs') : $assessment_array['marks'];
                                       if ($assessment_array['marks'] == "N/A") {
                                          $assessment_array['marks'] = 0;
                                       }
                                       $total_max_marks += $assessment_array['maximum_marks'];
                                       $subject_max_total += $assessment_array['maximum_marks'];
                                       $total_marks += $assessment_array['marks'];
                                       $subject_total += $assessment_array['marks'];
                                    } else {
                                       echo "<b>xx</b>";
                                    }
                                    ?>
                               </td>
                           <?php }  ?>
                            <td style="vertical-align: top;text-align: center;border: 1px solid gray; !important; padding: 4px !important;">
                               <?php echo $subject_total; ?>
                            </td>
                            <td style="vertical-align: top;text-align: center;" >
                               <?php
                                 $subject_percentage = getPercent($subject_max_total, $subject_total);
                                 echo getGrade($exam, $subject_percentage);
                                 ?>
                            </td>
                         </tr>
                      <?php  }  ?>
                   </tbody>
                </table>
                  <table style="border-collapse: collapse; cellspacing: 0;font-weight: bold;margin-top:10px;" width="100%">
                   <tbody>
                      <tr  style="border: 1px solid gray; !important;padding:10px !important;">
                         <td width="25%" style="border: 1px solid gray ;!important;padding:10px;!important;">
                            <?php echo $this->lang->line('overall_marks'); ?> :
                            <?php echo two_digit_float($total_marks, 2) . "/" . $total_max_marks ?>
                         </td>
                         <td  width="25%"  style="border: 1px solid gray; !important;padding:10px !important;">
                            <?php echo $this->lang->line('percentage'); ?> :
                            <?php
                              $exam_percentage = getPercent($total_max_marks, $total_marks);
                              echo two_digit_float($exam_percentage, 2) . "%";
                              ?>
                         </td>
                         <td  width="25%"  style="border: 1px solid gray; !important;padding:10px !important;">
                            <?php echo $this->lang->line('grade'); ?> :
                            <?php echo getGrade($exam, $exam_percentage) ?>
                         </td>
                          <td  width="25%"  style="border: 1px solid gray; !important; padding: 4px !important;">
                            <?php echo $this->lang->line('rank'); ?> :
                            <?php echo $student_result['rank']; ?>
                         </td>
                      </tr>
                   </tbody>
                </table>
             </div>
          <?php
            } else {
            ?>
             <div class="alert alert-danger">
                <?php echo $this->lang->line('no_record_found'); ?>
             </div>
       <?php
            }
         }
         ?>
    </div>

    <?php
      function find_subject_assessment_exists($subject_assessments, $cbse_exam_timetable_id, $cbse_exam_assessment_type_id)
      {
         if (!empty($subject_assessments)) {
            foreach ($subject_assessments as $key => $value) {
               if ($value->id == $cbse_exam_timetable_id) {
                  if (!empty($value->subject_assessments)) {
                     foreach ($value->subject_assessments as $askey => $asvalue) {
                        if ($asvalue->cbse_exam_timetable_id == $cbse_exam_timetable_id  && $asvalue->cbse_exam_assessment_type_id == $cbse_exam_assessment_type_id) {
                           return true;
                           break;
                        }
                     }
                  }
               }
            }
         }
         return false;
      }

      function findAssessmentValue($find_subject_id, $find_cbse_subject_assessment_type_id, $student_value)
      {
         $return_array = [
            'maximum_marks' => "",
            'marks' => "",
            'note' => "",
            'is_absent' => "",
         ];

         if (array_key_exists('subjects', $student_value)) {
            if (array_key_exists($find_subject_id, $student_value['subjects'])) {
               $result_array = ($student_value['subjects'][$find_subject_id]['exam_assessments'][$find_cbse_subject_assessment_type_id]);
               $return_array = [
                  'maximum_marks' => $result_array['maximum_marks'],
                  'marks' => is_null($result_array['marks']) ? "N/A" : $result_array['marks'],
                  'note' => $result_array['note'],
                  'is_absent' => $result_array['is_absent'],
               ];
            }
         }
         return $return_array;
      }

      function getGrade($grade_array, $Percentage)
      {
         if (!empty($grade_array->grades)) {
            foreach ($grade_array->grades as $grade_key => $grade_value) {

               if ($grade_value->minimum_percentage <= $Percentage) {
                  return $grade_value->name;
                  break;
               } elseif (($grade_value->minimum_percentage >= $Percentage && $grade_value->maximum_percentage <= $Percentage)) {

                  return $grade_value->name;
                  break;
               }
            }
         }
         return "-";
      }
      ?>

   