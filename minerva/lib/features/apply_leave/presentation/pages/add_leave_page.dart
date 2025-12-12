// minerva/lib/features/apply_leave/presentation/pages/add_leave_page.dart
import 'dart:io';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:minerva_flutter/features/apply_leave/presentation/bloc/apply_leave_bloc.dart';
import 'package:minerva_flutter/features/apply_leave/presentation/bloc/apply_leave_event.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AddLeavePage extends StatefulWidget {
  const AddLeavePage({Key? key}) : super(key: key);

  @override
  State<AddLeavePage> createState() => _AddLeavePageState();
}

class _AddLeavePageState extends State<AddLeavePage> {
  final _formKey = GlobalKey<FormState>();
  final TextEditingController _applyDateController = TextEditingController();
  final TextEditingController _fromDateController = TextEditingController();
  final TextEditingController _toDateController = TextEditingController();
  final TextEditingController _reasonController = TextEditingController();

  File? _selectedFile;
  String _selectedFileName = '';

  Color primaryColour = Colors.blue;
  Color secondaryColour = Colors.blue;
  String dateFormat = 'yyyy-MM-dd'; // Default, will be loaded from prefs

  @override
  void initState() {
    super.initState();
    _loadConfig();
    _setInitialDates();
  }

  Future<void> _loadConfig() async {
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        primaryColour = Color(int.parse((prefs.getString('primaryColour') ?? Constants.defaultPrimaryColour).replaceAll('#', '0xff')));
        secondaryColour = Color(int.parse((prefs.getString('secondaryColour') ?? Constants.defaultSecondaryColour).replaceAll('#', '0xff')));
        dateFormat = prefs.getString("dateFormat") ?? 'yyyy-MM-dd';
      });
    }
  }

  void _setInitialDates() {
    final DateTime now = DateTime.now();
    _applyDateController.text = DateFormat(dateFormat).format(now);
    // fromDate and toDate are left empty for user selection
  }

  Future<void> _selectDate(BuildContext context, TextEditingController controller) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime(2000),
      lastDate: DateTime(2101),
    );
    if (picked != null) {
      setState(() {
        controller.text = DateFormat(dateFormat).format(picked);
      });
    }
  }

  Future<void> _pickFile() async {
    FilePickerResult? result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'txt', 'zip'],
    );

    if (result != null) {
      setState(() {
        _selectedFile = File(result.files.single.path!);
        _selectedFileName = result.files.single.name;
      });
    }
  }

  void _submitForm() {
    if (_formKey.currentState!.validate()) {
      context.read<ApplyLeaveBloc>().add(SubmitLeaveApplication(
            fromDate: _fromDateController.text,
            toDate: _toDateController.text,
            reason: _reasonController.text,
            applyDate: _applyDateController.text,
            documentPath: _selectedFile?.path,
          ));
      // Pop the screen after submission, BLoC listener will handle messages
      context.pop();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Apply Leave'),
        backgroundColor: secondaryColour,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () {
            context.pop();
          },
        ),
      ),
      body: Container(
        decoration: const BoxDecoration(
          image: DecorationImage(
            image: AssetImage("assets/img_background_main.jpg"),
            fit: BoxFit.cover,
          ),
        ),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16.0),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildHeader(),
                const SizedBox(height: 20),
                _buildDateField(
                  _applyDateController,
                  'Applied Date',
                  readOnly: true, // Display only
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: _buildDateField(
                        _fromDateController,
                        'From Date',
                        onTap: () => _selectDate(context, _fromDateController),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: _buildDateField(
                        _toDateController,
                        'To Date',
                        onTap: () => _selectDate(context, _toDateController),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                _buildReasonField(),
                const SizedBox(height: 10),
                _buildFilePicker(),
                const SizedBox(height: 30),
                _buildSubmitButton(),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                'Add Leave Application', // Matches @string/addleaveheading
                style: TextStyle(
                  fontSize: 22.0,
                  fontWeight: FontWeight.bold,
                  color: primaryColour,
                ),
              ),
            ),
            Image.asset(
              'assets/leavepage.jpg', // @drawable/leavepage
              width: 110,
              height: 110,
              fit: BoxFit.contain,
            ),
          ],
        ),
        const SizedBox(height: 10),
      ],
    );
  }

  Widget _buildDateField(TextEditingController controller, String label, {bool readOnly = false, VoidCallback? onTap}) {
    return TextFormField(
      controller: controller,
      readOnly: readOnly,
      onTap: onTap,
      decoration: InputDecoration(
        labelText: label,
        hintText: label,
        border: const OutlineInputBorder(),
        suffixIcon: readOnly ? null : const Icon(Icons.calendar_today),
      ),
      validator: (value) {
        if (value == null || value.isEmpty) {
          return 'Please select a $label';
        }
        return null;
      },
    );
  }

  Widget _buildReasonField() {
    return TextFormField(
      controller: _reasonController,
      decoration: const InputDecoration(
        labelText: 'Reason', // Matches @string/Reason
        hintText: 'Enter reason for leave',
        border: OutlineInputBorder(),
      ),
      maxLines: 3,
      validator: (value) {
        if (value == null || value.isEmpty) {
          return 'Please enter a reason';
        }
        return null;
      },
    );
  }

  Widget _buildFilePicker() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        GestureDetector(
          onTap: _pickFile,
          child: Card(
            elevation: 3,
            child: Padding(
              padding: const EdgeInsets.all(10.0),
              child: Row(
                children: [
                  Icon(Icons.attach_file, color: primaryColour),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      _selectedFileName.isEmpty ? 'Choose File' : _selectedFileName, // Matches @string/choosefile
                      style: TextStyle(color: _selectedFileName.isEmpty ? Colors.grey : Colors.black),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  Icon(Icons.folder_open, color: primaryColour),
                ],
              ),
            ),
          ),
        ),
        if (_selectedFile != null)
          Padding(
            padding: const EdgeInsets.only(top: 8.0),
            child: Row(
              children: [
                Text(
                  'Selected: $_selectedFileName',
                  style: const TextStyle(fontSize: 12, color: Colors.green),
                ),
                const Spacer(),
                IconButton(
                  icon: const Icon(Icons.clear, size: 20),
                  onPressed: () {
                    setState(() {
                      _selectedFile = null;
                      _selectedFileName = '';
                    });
                  },
                ),
              ],
            ),
          ),
      ],
    );
  }

  Widget _buildSubmitButton() {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton(
        onPressed: _submitForm,
        style: ElevatedButton.styleFrom(
          backgroundColor: primaryColour, // Dynamic color
          padding: const EdgeInsets.symmetric(vertical: 15),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10.0),
          ),
        ),
        child: const Text(
          'Submit', // Matches @string/submit
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _applyDateController.dispose();
    _fromDateController.dispose();
    _toDateController.dispose();
    _reasonController.dispose();
    super.dispose();
  }
}
