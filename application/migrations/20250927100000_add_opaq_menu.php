<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_opaq_menu extends CI_Migration {

    public function up() {
        // Get the ID of the 'Library' main menu
        $this->db->select('id');
        $this->db->where('lang_key', 'library');
        // Also try to match by access_permissions for robustness
        $this->db->or_where('access_permissions', "('books', 'can_view') || ('issue_return', 'can_view') || ('add_staff_member', 'can_view') || ('add_student', 'can_view') || ('library_category', 'can_view') || ('library_subcategory', 'can_view') || ('library_publisher', 'can_view') || ('library_vendor', 'can_view') || ('library_book_type', 'can_view') || ('library_subject', 'can_view') || ('library_position_rack', 'can_view') || ('library_position_shelf', 'can_view')");
        $query = $this->db->get('sidebar_menus');
        $library_menu = $query->row_array();

        $library_menu_id = 0;
        if ($library_menu) {
            $library_menu_id = $library_menu['id'];
            log_message('debug', 'Migration_Add_opaq_menu: "Library" menu ID found: ' . $library_menu_id);
        } else {
            log_message('error', 'Migration_Add_opaq_menu: "Library" menu not found in sidebar_menus table. Cannot add OPAQ submenu.');
            return; // Stop migration if parent menu not found
        }

        // Data for the new 'OPAQ' submenu item
        $data = array(
            'sidebar_menu_id' => $library_menu_id,
            'lang_key' => 'opaq',
            'url' => 'admin/book/opaq', // This controller method will be created later
            'access_permissions' => 'books,can_view', // Using existing book permissions for now
            'level' => 100, // A high number to place it at the end, adjust as needed
            'is_active' => 1,
            'short_code' => 'opaq',
            'created_at' => date('Y-m-d H:i:s')
        );

        log_message('debug', 'Migration_Add_opaq_menu: Attempting to insert OPAQ submenu with data: ' . json_encode($data));
        $this->db->insert('sidebar_sub_menus', $data);
        if ($this->db->affected_rows() > 0) {
            log_message('debug', 'Migration_Add_opaq_menu: OPAQ submenu inserted successfully.');
        } else {
            log_message('error', 'Migration_Add_opaq_menu: Failed to insert OPAQ submenu. DB Error: ' . $this->db->error()['message']);
        }
    }

    public function down() {
        // Remove the 'OPAQ' submenu item
        $this->db->where('lang_key', 'opaq');
        $this->db->delete('sidebar_sub_menus');
    }
}
