<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_deleted_payments_log extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'student_fees_deposite_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE,
            ),
            'student_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'class_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'section_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'amount_detail' => array(
                'type' => 'TEXT',
                'null' => TRUE,
            ),
            'deleted_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE,
            ),
            'deleted_by' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'deletion_reason' => array(
                'type' => 'TEXT',
                'null' => FALSE,
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('student_fees_deposite_deleted');
    }

    public function down()
    {
        $this->dbforge->drop_table('student_fees_deposite_deleted');
    }
}
