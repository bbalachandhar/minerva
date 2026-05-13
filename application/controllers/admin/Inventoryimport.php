<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Inventoryimport extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'download'));
        $this->load->library('CSVReader');
    }

    public function itemcategory()
    {
        $this->renderImportPage('itemcategory');
    }

    public function itemstore()
    {
        $this->renderImportPage('itemstore');
    }

    public function itemsupplier()
    {
        $this->renderImportPage('itemsupplier');
    }

    public function item()
    {
        $this->renderImportPage('item');
    }

    public function itemstock()
    {
        $this->renderImportPage('itemstock');
    }

    public function assetlocation()
    {
        $this->renderImportPage('assetlocation');
    }

    public function assetregister()
    {
        $this->renderImportPage('assetregister');
    }

    public function assetassignment()
    {
        $this->renderImportPage('assetassignment');
    }

    public function import($module = null)
    {
        $config = $this->getModuleConfig($module);
        if (empty($config)) {
            show_404();
            return;
        }

        $this->ensurePrivilege($config['privilege']);
        $this->form_validation->set_rules('file', 'CSV File', 'callback_handle_csv_upload');

        if ($this->form_validation->run() == false) {
            $this->renderImportView($config);
            return;
        }

        $rows = $this->csvreader->parse_file($_FILES['file']['tmp_name']);
        if (empty($rows)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No data found in the uploaded CSV file.</div>');
            redirect($config['page_url']);
            return;
        }

        $summary = $this->processImport($module, $rows);
        $this->session->set_flashdata('msg', $this->buildSummaryMessage($summary));
        redirect($config['page_url']);
    }

    public function downloadsample($module = null)
    {
        $config = $this->getModuleConfig($module);
        if (empty($config)) {
            show_404();
            return;
        }

        $this->ensurePrivilege($config['privilege']);

        $data = file_get_contents($config['sample_file']);
        force_download(basename($config['sample_file']), $data);
    }

    public function handle_csv_upload()
    {
        if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $mimes = array(
                'text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt',
            );

            $temp = explode('.', $_FILES['file']['name']);
            $extension = strtolower(end($temp));

            if ($_FILES['file']['error'] > 0) {
                $this->form_validation->set_message('handle_csv_upload', 'Error opening the file.');
                return false;
            }

            if (!in_array($_FILES['file']['type'], $mimes)) {
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }

            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }

            return true;
        }

        $this->form_validation->set_message('handle_csv_upload', $this->lang->line('please_select_file'));
        return false;
    }

    private function renderImportPage($module)
    {
        $config = $this->getModuleConfig($module);
        if (empty($config)) {
            show_404();
            return;
        }

        $this->ensurePrivilege($config['privilege']);
        $this->renderImportView($config);
    }

    private function renderImportView($config)
    {
        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', $config['sub_menu']);

        $data = array(
            'title' => $config['title'],
            'module_label' => $config['module_label'],
            'page_url' => $config['page_url'],
            'action_url' => site_url('admin/inventoryimport/import/' . $config['module']),
            'download_url' => site_url('admin/inventoryimport/downloadsample/' . $config['module']),
            'back_url' => site_url($config['back_url']),
            'guide_url' => site_url('admin/inventorydashboard/guide'),
            'headers' => $config['headers'],
            'sample_row' => $config['sample_row'],
            'instructions' => $config['instructions'],
            'onboarding_steps' => $this->getOnboardingSteps(),
        );

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/import_master', $data);
        $this->load->view('layout/footer', $data);
    }

    private function ensurePrivilege($privilege)
    {
        if (!$this->rbac->hasPrivilege($privilege, 'can_add')) {
            access_denied();
        }
    }

    private function processImport($module, $rows)
    {
        switch ($module) {
            case 'itemcategory':
                return $this->importItemCategories($rows);
            case 'itemstore':
                return $this->importItemStores($rows);
            case 'itemsupplier':
                return $this->importItemSuppliers($rows);
            case 'item':
                return $this->importItems($rows);
            case 'itemstock':
                return $this->importItemStocks($rows);
            case 'assetlocation':
                return $this->importAssetLocations($rows);
            case 'assetregister':
                return $this->importAssetRegister($rows);
            case 'assetassignment':
                return $this->importAssetAssignments($rows);
            default:
                return array('imported' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => array('Unsupported import module.'));
        }
    }

    private function importItemCategories($rows)
    {
        $summary = $this->newSummary();
        $supportsIsAsset = $this->db->field_exists('is_asset', 'item_category');
        $supportsTrackingMode = $this->db->field_exists('asset_tracking_mode', 'item_category');

        foreach ($rows as $index => $row) {
            $name = $this->getCsvValue($row, array('item_category', 'category_name', 'item_category_name'));
            $description = $this->getCsvValue($row, array('description'));

            if ($name === '') {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': item_category is required.';
                continue;
            }

            $data = array(
                'item_category' => $name,
                'description' => $description,
            );

            if ($supportsIsAsset) {
                $isAsset = $this->getCsvValue($row, array('is_asset'));
                if ($isAsset !== '') {
                    $data['is_asset'] = $this->normalizeBoolean($isAsset);
                }
            }

            if ($supportsTrackingMode) {
                $trackingMode = $this->getCsvValue($row, array('asset_tracking_mode'));
                if ($trackingMode !== '') {
                    $data['asset_tracking_mode'] = $trackingMode;
                }
            }

            $existing = $this->db->query('SELECT id FROM item_category WHERE LOWER(item_category) = ?', array(strtolower($name)))->row_array();
            if (!empty($existing)) {
                $data['id'] = $existing['id'];
                $this->itemcategory_model->add($data);
                $summary['updated']++;
            } else {
                $this->itemcategory_model->add($data);
                $summary['imported']++;
            }
        }

        return $summary;
    }

    private function importItemStores($rows)
    {
        $summary = $this->newSummary();

        foreach ($rows as $index => $row) {
            $name = $this->getCsvValue($row, array('item_store', 'store_name'));
            $code = $this->getCsvValue($row, array('code', 'store_code'));
            $description = $this->getCsvValue($row, array('description'));

            if ($name === '') {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': item_store is required.';
                continue;
            }

            $existing = null;
            if ($code !== '') {
                $existing = $this->db->query('SELECT id FROM item_store WHERE LOWER(code) = ?', array(strtolower($code)))->row_array();
            }
            if (empty($existing)) {
                $existing = $this->db->query('SELECT id FROM item_store WHERE LOWER(item_store) = ?', array(strtolower($name)))->row_array();
            }

            $data = array(
                'item_store' => $name,
                'code' => $code,
                'description' => $description,
            );

            if (!empty($existing)) {
                $data['id'] = $existing['id'];
                $this->itemstore_model->add($data);
                $summary['updated']++;
            } else {
                $this->itemstore_model->add($data);
                $summary['imported']++;
            }
        }

        return $summary;
    }

    private function importItemSuppliers($rows)
    {
        $summary = $this->newSummary();

        foreach ($rows as $index => $row) {
            $name = $this->getCsvValue($row, array('item_supplier', 'supplier_name'));
            if ($name === '') {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': item_supplier is required.';
                continue;
            }

            $data = array(
                'item_supplier' => $name,
                'phone' => $this->getCsvValue($row, array('phone')),
                'email' => $this->getCsvValue($row, array('email')),
                'address' => $this->getCsvValue($row, array('address')),
                'contact_person_name' => $this->getCsvValue($row, array('contact_person_name')),
                'contact_person_phone' => $this->getCsvValue($row, array('contact_person_phone')),
                'contact_person_email' => $this->getCsvValue($row, array('contact_person_email')),
                'description' => $this->getCsvValue($row, array('description')),
            );

            $existing = $this->db->query('SELECT id FROM item_supplier WHERE LOWER(item_supplier) = ?', array(strtolower($name)))->row_array();
            if (!empty($existing)) {
                $data['id'] = $existing['id'];
                $this->itemsupplier_model->add($data);
                $summary['updated']++;
            } else {
                $this->itemsupplier_model->add($data);
                $summary['imported']++;
            }
        }

        return $summary;
    }

    private function importItems($rows)
    {
        $summary = $this->newSummary();

        foreach ($rows as $index => $row) {
            $name = $this->getCsvValue($row, array('item_name', 'name', 'item'));
            $categoryName = $this->getCsvValue($row, array('item_category', 'category_name'));
            $unit = $this->getCsvValue($row, array('unit'));
            $description = $this->getCsvValue($row, array('description'));

            if ($name === '' || $categoryName === '' || $unit === '') {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': item_name, item_category, and unit are required.';
                continue;
            }

            $category = $this->findCategoryByName($categoryName);
            if (empty($category)) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': item category not found - ' . $categoryName . '.';
                continue;
            }

            $existing = $this->db->query('SELECT id FROM item WHERE LOWER(name) = ? AND item_category_id = ?', array(strtolower($name), $category['id']))->row_array();

            $data = array(
                'item_category_id' => $category['id'],
                'name' => $name,
                'unit' => $unit,
                'description' => $description,
            );

            if (!empty($existing)) {
                $data['id'] = $existing['id'];
                $this->item_model->add($data);
                $summary['updated']++;
            } else {
                $this->item_model->add($data);
                $summary['imported']++;
            }
        }

        return $summary;
    }

    private function importItemStocks($rows)
    {
        $summary = $this->newSummary();

        foreach ($rows as $index => $row) {
            $itemName = $this->getCsvValue($row, array('item_name', 'item'));
            $categoryName = $this->getCsvValue($row, array('item_category', 'category_name'));
            $supplierName = $this->getCsvValue($row, array('supplier_name', 'item_supplier'));
            $storeName = $this->getCsvValue($row, array('store_name', 'item_store'));
            $quantityValue = $this->getCsvValue($row, array('quantity'));
            $purchasePrice = $this->getCsvValue($row, array('purchase_price', 'price'));
            $dateValue = $this->getCsvValue($row, array('date'));
            $symbol = $this->getCsvValue($row, array('symbol'));
            $description = $this->getCsvValue($row, array('description'));
            $licenseKey = $this->getCsvValue($row, array('license_key'));
            $licenseFrom = $this->getCsvValue($row, array('license_valid_from'));
            $licenseTill = $this->getCsvValue($row, array('license_valid_till'));
            $warrantyUpto = $this->getCsvValue($row, array('warranty_upto'));
            $batchNo = $this->getCsvValue($row, array('batch_no', 'batch_number', 'reg_no', 'registration_no'));

            if ($itemName === '' || $categoryName === '' || $supplierName === '' || $quantityValue === '' || $purchasePrice === '' || $dateValue === '') {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': item_name, item_category, supplier_name, quantity, purchase_price, and date are required.';
                continue;
            }

            $category = $this->findCategoryByName($categoryName);
            if (empty($category)) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': item category not found - ' . $categoryName . '.';
                continue;
            }

            $item = $this->findItemByNameAndCategory($itemName, $category['id']);
            if (empty($item)) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': item not found - ' . $itemName . ' in category ' . $categoryName . '.';
                continue;
            }

            $supplier = $this->findSupplierByName($supplierName);
            if (empty($supplier)) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': supplier not found - ' . $supplierName . '.';
                continue;
            }

            $storeId = null;
            if ($storeName !== '') {
                $store = $this->findStoreByNameOrCode($storeName);
                if (empty($store)) {
                    $summary['errors'][] = 'Row ' . ($index + 2) . ': store not found - ' . $storeName . '.';
                    continue;
                }
                $storeId = $store['id'];
            }

            if (!is_numeric($quantityValue)) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': quantity must be numeric.';
                continue;
            }

            if (!is_numeric($purchasePrice)) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': purchase_price must be numeric.';
                continue;
            }

            $normalizedDate = $this->normalizeDate($dateValue);
            if ($normalizedDate === null) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': date must be a valid date. Use YYYY-MM-DD for best results.';
                continue;
            }

            $symbol = in_array($symbol, array('+', '-')) ? $symbol : '+';
            $quantity = $symbol . $this->normalizeNumberString($quantityValue);

            $data = array(
                'item_id' => $item['id'],
                'symbol' => $symbol,
                'supplier_id' => $supplier['id'],
                'store_id' => $storeId,
                'quantity' => $quantity,
                'purchase_price' => (float) $purchasePrice,
                'date' => $normalizedDate,
                'description' => $description,
                'attachment' => '',
            );

            if ($batchNo !== '') {
                $data['batch_no'] = $batchNo;
            }
            if ($licenseKey !== '') {
                $data['license_key'] = $licenseKey;
            }
            if ($licenseFrom !== '') {
                $normalizedLicenseFrom = $this->normalizeDate($licenseFrom);
                if ($normalizedLicenseFrom !== null) {
                    $data['license_valid_from'] = $normalizedLicenseFrom;
                }
            }
            if ($licenseTill !== '') {
                $normalizedLicenseTill = $this->normalizeDate($licenseTill);
                if ($normalizedLicenseTill !== null) {
                    $data['license_valid_till'] = $normalizedLicenseTill;
                }
            }
            if ($warrantyUpto !== '') {
                $normalizedWarranty = $this->normalizeDate($warrantyUpto);
                if ($normalizedWarranty !== null) {
                    $data['warranty_upto'] = $normalizedWarranty;
                }
            }

            $this->itemstock_model->add($data);
            $summary['imported']++;
        }

        return $summary;
    }

    private function importAssetLocations($rows)
    {
        $summary = $this->newSummary();
        $validTypes = array('room', 'lab', 'department', 'office', 'store', 'corridor', 'other');

        foreach ($rows as $index => $row) {
            $code = $this->getCsvValue($row, array('location_code', 'code'));
            $name = $this->getCsvValue($row, array('location_name', 'name'));
            $type = strtolower($this->getCsvValue($row, array('location_type', 'type')));
            $notes = $this->getCsvValue($row, array('notes', 'description'));

            if ($code === '' || $name === '') {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': location_code and location_name are required.';
                continue;
            }

            if ($type !== '' && !in_array($type, $validTypes)) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': location_type must be one of: ' . implode(', ', $validTypes) . '.';
                continue;
            }

            $data = array(
                'location_code' => strtoupper($code),
                'location_name' => $name,
                'location_type' => $type !== '' ? $type : 'room',
                'notes' => $notes,
                'is_active' => 1,
            );

            $existing = $this->db->query('SELECT id FROM inv_asset_locations WHERE LOWER(location_code) = ?', array(strtolower($code)))->row_array();
            if (!empty($existing)) {
                $this->db->where('id', $existing['id'])->update('inv_asset_locations', $data);
                $summary['updated']++;
            } else {
                $this->db->insert('inv_asset_locations', $data);
                $summary['imported']++;
            }
        }

        return $summary;
    }

    private function importAssetRegister($rows)
    {
        $summary = $this->newSummary();
        $validStatuses = array('in_stock', 'assigned', 'under_maintenance', 'disposed', 'lost');

        foreach ($rows as $index => $row) {
            $assetTag = $this->getCsvValue($row, array('asset_tag'));
            $assetName = $this->getCsvValue($row, array('asset_name', 'name'));

            if ($assetTag === '' || $assetName === '') {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': asset_tag and asset_name are required.';
                continue;
            }

            $itemId = null;
            $itemName = $this->getCsvValue($row, array('item_name', 'item'));
            $categoryName = $this->getCsvValue($row, array('item_category', 'category_name'));
            if ($itemName !== '' && $categoryName !== '') {
                $category = $this->findCategoryByName($categoryName);
                if (!empty($category)) {
                    $item = $this->findItemByNameAndCategory($itemName, $category['id']);
                    if (!empty($item)) {
                        $itemId = $item['id'];
                    }
                }
            }

            $supplierId = null;
            $supplierName = $this->getCsvValue($row, array('supplier_name', 'supplier'));
            if ($supplierName !== '') {
                $supplier = $this->findSupplierByName($supplierName);
                if (!empty($supplier)) {
                    $supplierId = $supplier['id'];
                } else {
                    $summary['errors'][] = 'Row ' . ($index + 2) . ': supplier not found - ' . $supplierName . '.';
                    continue;
                }
            }

            $locationId = null;
            $locationCode = $this->getCsvValue($row, array('location_code', 'location'));
            if ($locationCode !== '') {
                $location = $this->findLocationByCode($locationCode);
                if (!empty($location)) {
                    $locationId = $location['id'];
                } else {
                    $summary['errors'][] = 'Row ' . ($index + 2) . ': location not found - ' . $locationCode . '.';
                    continue;
                }
            }

            $status = $this->getCsvValue($row, array('current_status', 'status'));
            if ($status !== '' && !in_array(strtolower($status), $validStatuses)) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': current_status must be one of: ' . implode(', ', $validStatuses) . '.';
                continue;
            }

            $purchaseCost = $this->getCsvValue($row, array('purchase_cost', 'cost'));
            if ($purchaseCost !== '' && !is_numeric($purchaseCost)) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': purchase_cost must be numeric.';
                continue;
            }

            $data = array(
                'asset_tag'      => $assetTag,
                'asset_name'     => $assetName,
                'serial_no'      => $this->getCsvValue($row, array('serial_no', 'serial', 'serial_number')),
                'model_no'       => $this->getCsvValue($row, array('model_no', 'model', 'model_number')),
                'brand_name'     => $this->getCsvValue($row, array('brand_name', 'brand')),
                'purchase_cost'  => $purchaseCost !== '' ? (float) $purchaseCost : 0.00,
                'current_status' => $status !== '' ? strtolower($status) : 'in_stock',
                'remarks'        => $this->getCsvValue($row, array('remarks', 'notes')),
            );

            if ($itemId !== null)     { $data['item_id']             = $itemId; }
            if ($supplierId !== null) { $data['supplier_id']         = $supplierId; }
            if ($locationId !== null) { $data['current_location_id'] = $locationId; }

            foreach (array(
                'purchase_date'      => array('purchase_date'),
                'capitalization_date' => array('capitalization_date', 'cap_date'),
                'warranty_start'     => array('warranty_start'),
                'warranty_end'       => array('warranty_end'),
            ) as $field => $keys) {
                $raw = $this->getCsvValue($row, $keys);
                if ($raw !== '') {
                    $d = $this->normalizeDate($raw);
                    if ($d !== null) { $data[$field] = $d; }
                }
            }

            $existing = $this->db->query('SELECT id FROM inv_assets WHERE LOWER(asset_tag) = ?', array(strtolower($assetTag)))->row_array();
            if (!empty($existing)) {
                $this->db->where('id', $existing['id'])->update('inv_assets', $data);
                $summary['updated']++;
            } else {
                $this->db->insert('inv_assets', $data);
                $summary['imported']++;
            }
        }

        return $summary;
    }

    private function importAssetAssignments($rows)
    {
        $summary = $this->newSummary();

        foreach ($rows as $index => $row) {
            $assetTag     = $this->getCsvValue($row, array('asset_tag'));
            $assigneeType = strtolower($this->getCsvValue($row, array('assignee_type', 'holder_type')));
            $assignedOn   = $this->getCsvValue($row, array('assigned_on', 'assigned_date'));

            if ($assetTag === '' || $assigneeType === '' || $assignedOn === '') {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': asset_tag, assignee_type, and assigned_on are required.';
                continue;
            }

            if (!in_array($assigneeType, array('staff', 'place'))) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': assignee_type must be staff or place.';
                continue;
            }

            $normalizedDate = $this->normalizeDate($assignedOn);
            if ($normalizedDate === null) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': assigned_on must be a valid date (YYYY-MM-DD).';
                continue;
            }

            $asset = $this->db->query('SELECT id FROM inv_assets WHERE LOWER(asset_tag) = ?', array(strtolower($assetTag)))->row_array();
            if (empty($asset)) {
                $summary['errors'][] = 'Row ' . ($index + 2) . ': asset not found with tag - ' . $assetTag . '.';
                continue;
            }

            $assigneeId = null;
            $staffId    = null;
            $locationId = null;

            if ($assigneeType === 'staff') {
                $employeeId = $this->getCsvValue($row, array('employee_id', 'staff_id'));
                if ($employeeId === '') {
                    $summary['errors'][] = 'Row ' . ($index + 2) . ': employee_id is required for assignee_type=staff.';
                    continue;
                }
                $staffRow = $this->db->query('SELECT id FROM staff WHERE LOWER(employee_id) = ? AND is_active = 1 LIMIT 1', array(strtolower($employeeId)))->row_array();
                if (empty($staffRow)) {
                    $summary['errors'][] = 'Row ' . ($index + 2) . ': active staff not found with employee_id - ' . $employeeId . '.';
                    continue;
                }
                $assigneeId = $staffRow['id'];
                $staffId    = $staffRow['id'];
            } else {
                $locationCode = $this->getCsvValue($row, array('location_code', 'place_code'));
                if ($locationCode === '') {
                    $summary['errors'][] = 'Row ' . ($index + 2) . ': location_code is required for assignee_type=place.';
                    continue;
                }
                $locRow = $this->findLocationByCode($locationCode);
                if (empty($locRow)) {
                    $summary['errors'][] = 'Row ' . ($index + 2) . ': location not found with code - ' . $locationCode . '.';
                    continue;
                }
                $assigneeId = $locRow['id'];
                $locationId = $locRow['id'];
            }

            // Close any existing open assignment for this asset before creating the new one
            $this->db->where('asset_id', $asset['id'])
                     ->where('status', 'assigned')
                     ->where('returned_on IS NULL', null, false)
                     ->update('inv_asset_assignments', array(
                         'returned_on' => $normalizedDate,
                         'return_note' => 'Superseded by bulk assignment import on ' . date('Y-m-d'),
                         'status'      => 'returned',
                     ));

            $this->db->insert('inv_asset_assignments', array(
                'asset_id'      => $asset['id'],
                'assignee_type' => $assigneeType,
                'assignee_id'   => $assigneeId,
                'assigned_on'   => $normalizedDate,
                'status'        => 'assigned',
            ));

            $assetUpdate = array(
                'current_status'      => 'assigned',
                'assigned_to_type'    => $assigneeType,
                'assigned_to_staff_id' => $staffId,
            );
            if ($locationId !== null) {
                $assetUpdate['current_location_id'] = $locationId;
            }
            $this->db->where('id', $asset['id'])->update('inv_assets', $assetUpdate);

            $summary['imported']++;
        }

        return $summary;
    }

    private function buildSummaryMessage($summary)
    {
        $message = '<div class="alert ' . (empty($summary['errors']) ? 'alert-success' : 'alert-warning') . ' text-left">';
        $message .= '<strong>Import completed.</strong><br>';
        $message .= 'Imported: ' . (int) $summary['imported'] . '<br>';
        $message .= 'Updated: ' . (int) $summary['updated'] . '<br>';
        $message .= 'Skipped: ' . (int) $summary['skipped'];

        if (!empty($summary['errors'])) {
            $message .= '<hr style="margin:8px 0;">';
            $message .= '<strong>Issues:</strong><ul style="margin-bottom:0; padding-left:18px;">';
            foreach (array_slice($summary['errors'], 0, 8) as $error) {
                $message .= '<li>' . html_escape($error) . '</li>';
            }
            if (count($summary['errors']) > 8) {
                $message .= '<li>More rows had issues. Fix the CSV and re-upload the remaining entries.</li>';
            }
            $message .= '</ul>';
        }

        $message .= '</div>';
        return $message;
    }

    private function newSummary()
    {
        return array(
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => array(),
        );
    }

    private function getCsvValue($row, $keys)
    {
        foreach ($keys as $expectedKey) {
            foreach ($row as $actualKey => $value) {
                if (strtolower(trim($actualKey)) === strtolower(trim($expectedKey))) {
                    return trim((string) $value);
                }
            }
        }

        return '';
    }

    private function normalizeBoolean($value)
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, array('1', 'yes', 'y', 'true'), true) ? 1 : 0;
    }

    private function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function normalizeNumberString($value)
    {
        $number = (float) $value;
        if ((int) $number == $number) {
            return (string) (int) $number;
        }

        return rtrim(rtrim(number_format($number, 4, '.', ''), '0'), '.');
    }

    private function findCategoryByName($name)
    {
        return $this->db->query('SELECT * FROM item_category WHERE LOWER(item_category) = ? LIMIT 1', array(strtolower($name)))->row_array();
    }

    private function findSupplierByName($name)
    {
        return $this->db->query('SELECT * FROM item_supplier WHERE LOWER(item_supplier) = ? LIMIT 1', array(strtolower($name)))->row_array();
    }

    private function findStoreByNameOrCode($value)
    {
        return $this->db->query('SELECT * FROM item_store WHERE LOWER(item_store) = ? OR LOWER(code) = ? LIMIT 1', array(strtolower($value), strtolower($value)))->row_array();
    }

    private function findItemByNameAndCategory($name, $categoryId)
    {
        return $this->db->query('SELECT * FROM item WHERE LOWER(name) = ? AND item_category_id = ? LIMIT 1', array(strtolower($name), $categoryId))->row_array();
    }

    private function findLocationByCode($code)
    {
        return $this->db->query('SELECT * FROM inv_asset_locations WHERE LOWER(location_code) = ? AND is_active = 1 LIMIT 1', array(strtolower($code)))->row_array();
    }

    private function getOnboardingSteps()
    {
        return array(
            '1. Upload Item Categories first. This defines the master classification of all items and, where supported, asset categories.',
            '2. Upload Item Stores next. These are the physical or virtual stock locations used by stock inward, GRN, and issue flows.',
            '3. Upload Item Suppliers after stores. Purchase Orders and opening stock imports depend on supplier masters.',
            '4. Upload Items only after categories exist, because each item must map to one valid category.',
            '5. Upload Opening Stock last. Opening stock rows depend on existing items, suppliers, and optionally store names/codes.',
            '6. After masters are ready, move to Indents, Purchase Orders, Approval Matrix Rules, and GRNs for live procurement operations.',
            '7. To onboard existing physical assets: upload Asset Locations (labs, rooms, departments) first so location codes are ready.',
            '8. Then upload the Asset Register Snapshot — one CSV row per physical unit with serial number, brand, warranty, and cost.',
            '9. Finally, upload the Assignment Snapshot to record who currently has each asset. This creates the live "who has what now" state.',
        );
    }

    private function getModuleConfig($module)
    {
        $configs = array(
            'itemcategory' => array(
                'module' => 'itemcategory',
                'module_label' => 'Item Categories',
                'title' => 'Bulk Upload Item Categories',
                'privilege' => 'item_category',
                'sub_menu' => 'itemcategory/index',
                'back_url' => 'admin/itemcategory',
                'page_url' => 'admin/inventoryimport/itemcategory',
                'sample_file' => './backend/import/inventory_itemcategory_sample.csv',
                'headers' => array('item_category', 'description', 'is_asset', 'asset_tracking_mode'),
                'sample_row' => array('IT Equipment', 'Laptops and related fixed assets', '1', 'individual'),
                'instructions' => array(
                    'Use one row per category. item_category is required.',
                    'If your database supports asset tracking columns, set is_asset to 1 for asset categories and optionally set asset_tracking_mode.',
                    'Re-uploading the same category name updates the existing category instead of inserting a duplicate.',
                ),
            ),
            'itemstore' => array(
                'module' => 'itemstore',
                'module_label' => 'Item Stores',
                'title' => 'Bulk Upload Item Stores',
                'privilege' => 'store',
                'sub_menu' => 'itemstore/index',
                'back_url' => 'admin/itemstore',
                'page_url' => 'admin/inventoryimport/itemstore',
                'sample_file' => './backend/import/inventory_itemstore_sample.csv',
                'headers' => array('item_store', 'code', 'description'),
                'sample_row' => array('Main Store', 'MAIN', 'Central stock room for the institution'),
                'instructions' => array(
                    'Use a unique store code where possible. The importer updates by code first, then by store name.',
                    'Stores are optional on some transactions, but defining them early gives better stock location control.',
                ),
            ),
            'itemsupplier' => array(
                'module' => 'itemsupplier',
                'module_label' => 'Item Suppliers',
                'title' => 'Bulk Upload Item Suppliers',
                'privilege' => 'supplier',
                'sub_menu' => 'itemsupplier/index',
                'back_url' => 'admin/itemsupplier',
                'page_url' => 'admin/inventoryimport/itemsupplier',
                'sample_file' => './backend/import/inventory_itemsupplier_sample.csv',
                'headers' => array('item_supplier', 'phone', 'email', 'address', 'contact_person_name', 'contact_person_phone', 'contact_person_email', 'description'),
                'sample_row' => array('ABC Traders', '9876543210', 'abc@example.com', 'Chennai', 'Arun', '9876543211', 'arun@example.com', 'Primary vendor'),
                'instructions' => array(
                    'item_supplier is required. Name-based re-uploads update the existing supplier record.',
                    'Use email and contact person details if Purchase Orders will be shared externally or tracked formally.',
                ),
            ),
            'item' => array(
                'module' => 'item',
                'module_label' => 'Items',
                'title' => 'Bulk Upload Items',
                'privilege' => 'item',
                'sub_menu' => 'Item/index',
                'back_url' => 'admin/item',
                'page_url' => 'admin/inventoryimport/item',
                'sample_file' => './backend/import/inventory_item_sample.csv',
                'headers' => array('item_name', 'item_category', 'unit', 'description'),
                'sample_row' => array('Dell Latitude Laptop', 'IT Equipment', 'Nos', '14 inch staff laptop'),
                'instructions' => array(
                    'item_name, item_category, and unit are required.',
                    'The category name must already exist in Item Categories.',
                    'Re-uploading the same item name within the same category updates the item instead of duplicating it.',
                ),
            ),
            'itemstock' => array(
                'module' => 'itemstock',
                'module_label' => 'Opening Stock / Item Stock',
                'title' => 'Bulk Upload Opening Stock',
                'privilege' => 'item_stock',
                'sub_menu' => 'Itemstock/index',
                'back_url' => 'admin/itemstock',
                'page_url' => 'admin/inventoryimport/itemstock',
                'sample_file' => './backend/import/inventory_itemstock_sample.csv',
                'headers' => array('item_name', 'item_category', 'supplier_name', 'store_name', 'quantity', 'purchase_price', 'date', 'symbol', 'description'),
                'sample_row' => array('Dell Latitude Laptop', 'IT Equipment', 'ABC Traders', 'IT Store', '5', '55000', '2026-03-01', '+', 'Opening balance'),
                'instructions' => array(
                    'Use this for first-time opening balances or controlled bulk stock loads.',
                    'item_name, item_category, supplier_name, quantity, purchase_price, and date are required.',
                    'Recommended date format is YYYY-MM-DD. symbol can be + or -. If blank, + is assumed.',
                    'This import appends stock ledger entries. It does not update prior stock rows.',
                ),
            ),
        );

        $configs['assetlocation'] = array(
            'module'       => 'assetlocation',
            'module_label' => 'Asset Locations',
            'title'        => 'Bulk Upload Asset Locations',
            'privilege'    => 'inv_assets',
            'sub_menu'     => 'Assetmanagement/register',
            'back_url'     => 'admin/assetmanagement/register',
            'page_url'     => 'admin/inventoryimport/assetlocation',
            'sample_file'  => './backend/import/inventory_assetlocation_sample.csv',
            'headers'      => array('location_code', 'location_name', 'location_type', 'notes'),
            'sample_row'   => array('CSE-LAB-1', 'CSE Lab 1', 'lab', 'CSE Dept ground floor computer lab'),
            'instructions' => array(
                'location_code and location_name are required. Codes must be unique across the institution.',
                'location_type can be: room, lab, department, office, store, corridor, or other. Defaults to room if blank.',
                'Re-uploading the same location_code updates the existing record instead of creating a duplicate.',
                'Upload locations before the Asset Register, as asset rows reference location codes.',
            ),
        );

        $configs['assetregister'] = array(
            'module'       => 'assetregister',
            'module_label' => 'Asset Register',
            'title'        => 'Bulk Upload Asset Register Snapshot',
            'privilege'    => 'inv_assets',
            'sub_menu'     => 'Assetmanagement/register',
            'back_url'     => 'admin/assetmanagement/register',
            'page_url'     => 'admin/inventoryimport/assetregister',
            'sample_file'  => './backend/import/inventory_assetregister_sample.csv',
            'headers'      => array('asset_tag', 'asset_name', 'item_name', 'item_category', 'serial_no', 'model_no', 'brand_name', 'supplier_name', 'purchase_date', 'purchase_cost', 'capitalization_date', 'warranty_start', 'warranty_end', 'current_status', 'location_code', 'remarks'),
            'sample_row'   => array('MECE-PC-0001', 'Dell OptiPlex 3080', 'Desktop Computer', 'IT Equipment', 'SN3080001', 'OptiPlex 3080', 'Dell', 'ABC Traders', '2022-04-01', '38000', '2022-04-01', '2022-04-01', '2025-04-01', 'in_stock', 'CSE-LAB-1', 'CSE PC Lab Row A'),
            'instructions' => array(
                'asset_tag and asset_name are required. Each asset_tag must be globally unique (e.g. MECE-PC-0001).',
                'item_name and item_category are optional but strongly recommended for linking assets to the item master.',
                'current_status values: in_stock (default), assigned, under_maintenance, disposed, lost.',
                'location_code must match an existing Asset Location. Upload Asset Locations first.',
                'supplier_name must match an existing Supplier record. Leave blank if unknown.',
                'All dates must be YYYY-MM-DD. warranty_end drives expiry alerts in the Asset Register.',
                'Re-uploading the same asset_tag updates the existing asset without duplication.',
            ),
        );

        $configs['assetassignment'] = array(
            'module'       => 'assetassignment',
            'module_label' => 'Asset Assignments',
            'title'        => 'Bulk Upload Current Asset Assignments',
            'privilege'    => 'inv_assets',
            'sub_menu'     => 'Assetmanagement/register',
            'back_url'     => 'admin/assetmanagement/register',
            'page_url'     => 'admin/inventoryimport/assetassignment',
            'sample_file'  => './backend/import/inventory_assetassignment_sample.csv',
            'headers'      => array('asset_tag', 'assignee_type', 'employee_id', 'location_code', 'assigned_on'),
            'sample_row'   => array('MECE-PC-0001', 'staff', 'EMP001', '', '2022-04-15'),
            'instructions' => array(
                'asset_tag, assignee_type (staff or place), and assigned_on (YYYY-MM-DD) are required.',
                'For assignee_type=staff: employee_id is required and must match an active staff record.',
                'For assignee_type=place: location_code is required and must match an existing Asset Location.',
                'Each row automatically closes any existing open assignment for that asset before inserting the new one.',
                'Upload the Asset Register first — this import only works on assets that already exist.',
                'This creates the live "who has what now" state visible in the Asset Register screen.',
            ),
        );

        return isset($configs[$module]) ? $configs[$module] : null;
    }
}