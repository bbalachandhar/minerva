# Admission Enquiry Form - Complete Guide

## Overview
The "Add" button on the `/admin/enquiry` page displays a pop-up modal for creating a new **Admission Enquiry** record. This form captures inquiry information from prospective students/parents.

---

## URL & Location
- **URL**: `https://mce.beebasoft.com/admin/enquiry`
- **Button Location**: Box header, right side (pull-right) - visible only if user has RBAC permission: `admission_enquiry` → `can_add`
- **Controller**: [application/controllers/admin/Enquiry.php](application/controllers/admin/Enquiry.php)
- **View**: [application/views/admin/frontoffice/enquiryeditmodalview.php](application/views/admin/frontoffice/enquiryeditmodalview.php)

---

## Form Fields

### Basic Information (3 columns)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| **Name** | Text Input | ✓ Yes | Inquiry person's full name |
| **Phone** | Text Input (numeric) | ✓ Yes | Contact phone number |
| **Email** | Email Input | ✗ No | Email address |

### Location & Details (3 columns)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| **Address** | Textarea | ✗ No | Residential address |
| **Description** | Textarea | ✗ No | Additional information about the inquiry |
| **Note** | Textarea | ✗ No | Internal notes for staff |

### Classification & Dates (4 columns)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| **Date** | Date Picker | ✓ Yes | Inquiry received date (auto-readonly, set to today) |
| **Next Follow-up Date** | Date Picker | ✓ Yes | Scheduled follow-up date (auto-readonly) |
| **Assigned** | Dropdown | ✗ No | Staff member to assign inquiry handling |
| **Reference** | Dropdown | ✗ No | Reference source (dropdown list from `reference` table) |

### Source & Classification (4 columns)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| **Source** | Dropdown | ✓ Yes | Where inquiry came from (e.g., "Website", "Referral", "Walk-in") |
| **Class** | Dropdown | ✗ No | Interested class/grade level |

---

## HTML Structure

```html
<form action="<?php echo site_url('admin/enquiry') ?>" id="myForm1" method="post" class="ptt10">
    <div class="row">
        <!-- Row 1: Name, Phone, Email -->
        <div class="col-sm-4">
            <label>Name *</label>
            <input type="text" class="form-control" name="name" required>
        </div>
        <div class="col-sm-4">
            <label>Phone *</label>
            <input type="text" class="form-control" name="contact" required>
        </div>
        <div class="col-sm-4">
            <label>Email</label>
            <input type="text" class="form-control" name="email">
        </div>

        <!-- Row 2: Address, Description, Note -->
        <div class="col-sm-4">
            <label>Address</label>
            <textarea name="address" class="form-control"></textarea>
        </div>
        <div class="col-sm-4">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="col-sm-4">
            <label>Note</label>
            <textarea name="note" class="form-control"></textarea>
        </div>

        <!-- Row 3: Dates and Assignment -->
        <div class="col-sm-4">
            <label>Date *</label>
            <input type="text" name="date" class="form-control date" readonly>
        </div>
        <div class="col-sm-4">
            <label>Next Follow-up Date *</label>
            <input type="text" name="follow_up_date" class="form-control date" readonly>
        </div>
        <div class="col-sm-4">
            <label>Assigned</label>
            <select name="assigned" class="form-control">
                <option value="">Select</option>
                <!-- Staff list populated here -->
            </select>
        </div>

        <!-- Row 4: Reference, Source, Class -->
        <div class="col-sm-3">
            <label>Reference</label>
            <select name="reference" class="form-control">
                <!-- Reference list -->
            </select>
        </div>
        <div class="col-sm-3">
            <label>Source *</label>
            <select name="source" class="form-control">
                <!-- Source list -->
            </select>
        </div>
        <div class="col-sm-3">
            <label>Class</label>
            <select name="class" class="form-control">
                <!-- Class list -->
            </select>
        </div>
    </div>

    <!-- Footer -->
    <div class="box-footer row">
        <a onclick="postRecord(...)" class="btn btn-info pull-right">Save</a>
    </div>
</form>
```

---

## Form Validation Rules (Backend)

Server-side validation in the `add()` method:

```php
$this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
$this->form_validation->set_rules('contact', 'Phone', 'trim|required|xss_clean');
$this->form_validation->set_rules('source', 'Source', 'trim|required|xss_clean');
$this->form_validation->set_rules('date', 'Date', 'trim|required|xss_clean');
$this->form_validation->set_rules('follow_up_date', 'Next Follow-up Date', 'trim|required|xss_clean');
```

**Required Fields**: Name, Phone, Source, Date, Next Follow-up Date

---

## Data Flow

### 1. **Form Submission**
- User fills form and clicks "Save"
- JavaScript calls `postRecord()` function
- Form data sent via AJAX to: `admin/enquiry/add`

### 2. **Backend Processing**
**File**: [application/controllers/admin/Enquiry.php](application/controllers/admin/Enquiry.php) → `add()` method

```php
public function add()
{
    // RBAC Check
    if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_add')) {
        access_denied();
    }

    // Validation
    $this->form_validation->set_rules(...);
    
    if ($this->form_validation->run() == false) {
        // Return validation errors as JSON
        $array = array('status' => 'fail', 'error' => $msg);
    } else {
        // Prepare data array
        $enquiry = array(
            'name'           => $this->input->post('name'),
            'contact'        => $this->input->post('contact'),
            'address'        => $this->input->post('address'),
            'reference'      => $this->input->post('reference'),
            'date'           => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
            'description'    => $this->input->post('description'),
            'follow_up_date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('follow_up_date'))),
            'note'           => $this->input->post('note'),
            'source'         => $this->input->post('source'),
            'email'          => $this->input->post('email'),
            'assigned'       => empty($this->input->post('assigned')) ? NULL : $this->input->post('assigned'),
            'class_id'       => empty($this->input->post('class')) ? NULL : $this->input->post('class'),
            'no_of_child'    => $this->input->post('no_of_child'),
            'status'         => 'active',
            'created_by'     => $created_by,
        );
        
        // Save to database
        $this->enquiry_model->add($enquiry);
        
        // Return success
        $array = array('status' => 'success', 'message' => 'Success Message');
    }
    
    echo json_encode($array);
}
```

### 3. **Database Storage**
- **Table**: `enquiry`
- **Fields Stored**: name, contact, email, address, description, note, date, follow_up_date, source, reference, assigned, class_id, no_of_child, status, created_by
- **Default Status**: `active`

---

## Dropdown Data Sources

| Field | Source |
|-------|--------|
| **Staff (Assigned)** | `staff` table - populated in modal |
| **Reference** | `enquiry_model->get_reference()` |
| **Source** | `enquiry_model->getComplaintSource()` |
| **Class** | `class_model->get()` |

---

## RBAC Permissions Required

| Action | Permission | Category |
|--------|-----------|----------|
| View List | `can_view` | `admission_enquiry` |
| Add | `can_add` | `admission_enquiry` |
| Edit | `can_edit` | `admission_enquiry` |
| Delete | `can_delete` | `admission_enquiry` |

---

## Modal Opening JavaScript

The "Add" button opens the form modal via JavaScript (typically):

```javascript
$('.openmodal').click(function() {
    // Opens modal with empty form
    // Loads the enquiryeditmodalview.php
});
```

---

## Related Features

### Follow-up System
After creating an inquiry, staff can add follow-ups:
- **Method**: `follow_up()`, `follow_up_insert()`
- **Captures**: Response, Follow-up Date, Notes, Assigned Staff

### Status Management
Inquiries can have statuses: `active`, `won`, `lost`, `converted`

### Inquiry List View
**File**: [application/views/admin/frontoffice/enquiryview.php](application/views/admin/frontoffice/enquiryview.php)

Displays all inquiries in a DataTable with:
- Name, Phone, Source, Inquiry Date, Last Follow-up, Next Follow-up, Status
- Action buttons: Edit, View Details, Delete, Follow-up

---

## Important Notes

1. **Date Format**: Uses school-defined date format (configured in settings)
2. **Phone Validation**: Must be numeric
3. **Email Validation**: Optional but must be valid if provided
4. **Assigned Staff**: Optional - can be NULL
5. **Class Selection**: Optional - used to track interested class/grade
6. **CSRF Protection**: Form includes CSRF token (`<?php echo $this->customlib->getCSRF(); ?>`)
7. **Timezone Handling**: Dates converted using `$this->customlib->datetostrtotime()`

---

## Error Messages

| Scenario | Message |
|----------|---------|
| Missing Required Field | Form error displayed under field |
| Invalid Phone | "Phone must be numeric" |
| Invalid Email | "Email must be valid" |
| Validation Fails | Return JSON with `status: 'fail'` |

---

## Files Involved

1. **Controller**: [application/controllers/admin/Enquiry.php](application/controllers/admin/Enquiry.php)
2. **View (Modal)**: [application/views/admin/frontoffice/enquiryeditmodalview.php](application/views/admin/frontoffice/enquiryeditmodalview.php)
3. **View (List)**: [application/views/admin/frontoffice/enquiryview.php](application/views/admin/frontoffice/enquiryview.php)
4. **Model**: `enquiry_model` (auto-loaded)
5. **Language Strings**: Loaded from language files (translations)

---

## Example JSON Response

**Success**:
```json
{
  "status": "success",
  "error": "",
  "message": "Success Message"
}
```

**Validation Error**:
```json
{
  "status": "fail",
  "error": {
    "name": "Name is required",
    "contact": "Phone is required",
    "source": "Source is required"
  },
  "message": ""
}
```

---

*This document serves as a complete reference for the Admission Enquiry form functionality.*
