import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/entities/calendar_todo_entity.dart';
import 'package:minerva_flutter/features/calendar_todo/presentation/bloc/calendar_todo_bloc.dart';
import 'dart:developer';

class CalendarTodoPage extends StatefulWidget {
  const CalendarTodoPage({Key? key}) : super(key: key);

  @override
  State<CalendarTodoPage> createState() => _CalendarTodoPageState();
}

class _CalendarTodoPageState extends State<CalendarTodoPage> {
  DateTime _selectedDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    _fetchTasksForSelectedDate(_selectedDate);
  }

  void _fetchTasksForSelectedDate(DateTime date) {
    final formattedDate = "${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}";
    context.read<CalendarTodoBloc>().add(FetchCalendarTodosEvent(date: formattedDate));
  }

  Future<void> _showAddTaskBottomSheet() async {
    TextEditingController taskNameController = TextEditingController();
    DateTime? pickedDate = _selectedDate; // Initialize with current selected date

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true, // Allows the bottom sheet to take full height
      builder: (BuildContext bottomSheetContext) {
        return StatefulBuilder(
          builder: (BuildContext context, StateSetter setModalState) {
            return Padding(
              padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
              child: Container(
                padding: const EdgeInsets.all(20),
                child: Column(
                  mainAxisSize: MainAxisSize.min, // Occupy minimum height
                  children: [
                    Text(
                      'Add New Task',
                      style: Theme.of(context).textTheme.headlineSmall,
                    ),
                    const SizedBox(height: 20),
                    TextField(
                      controller: taskNameController,
                      decoration: const InputDecoration(
                        labelText: 'Task Name',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 10),
                    ListTile(
                      title: Text('Date: ${pickedDate!.toLocal().toString().split(' ')[0]}'),
                      trailing: const Icon(Icons.calendar_today),
                      onTap: () async {
                        final DateTime? date = await showDatePicker(
                          context: context, // Use the context from StatefulBuilder
                          initialDate: pickedDate!,
                          firstDate: DateTime(2000),
                          lastDate: DateTime(2101),
                        );
                        if (date != null) {
                          setModalState(() { // Update state within the modal
                            pickedDate = date;
                          });
                        }
                      },
                    ),
                    const SizedBox(height: 20),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: [
                        TextButton(
                          child: const Text('Cancel'),
                          onPressed: () {
                            Navigator.of(context).pop();
                          },
                        ),
                        ElevatedButton(
                          child: const Text('Add'),
                          onPressed: () {
                            if (taskNameController.text.isNotEmpty) {
                              context.read<CalendarTodoBloc>().add(
                                    CreateCalendarTodoEvent(
                                      task: CalendarTodoEntity(
                                        id: DateTime.now().millisecondsSinceEpoch.toString(), // Temporary ID, API should provide real one
                                        taskName: taskNameController.text,
                                        taskDate: "${pickedDate!.year}-${pickedDate!.month.toString().padLeft(2, '0')}-${pickedDate!.day.toString().padLeft(2, '0')}",
                                        isCompleted: false,
                                      ),
                                    ),
                                  );
                              Navigator.of(context).pop();
                            }
                          },
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            );
          }
        );
      },
    );
  }

  Future<void> _showEditTaskBottomSheet(CalendarTodoEntity task) async {
    TextEditingController taskNameController = TextEditingController(text: task.taskName);
    DateTime? pickedDate = DateTime.parse(task.taskDate); // Parse existing date

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true, // Allows the bottom sheet to take full height
      builder: (BuildContext bottomSheetContext) {
        return StatefulBuilder(
          builder: (BuildContext context, StateSetter setModalState) {
            return Padding(
              padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
              child: Container(
                padding: const EdgeInsets.all(20),
                child: Column(
                  mainAxisSize: MainAxisSize.min, // Occupy minimum height
                  children: [
                    Text(
                      'Edit Task',
                      style: Theme.of(context).textTheme.headlineSmall,
                    ),
                    const SizedBox(height: 20),
                    TextField(
                      controller: taskNameController,
                      decoration: const InputDecoration(
                        labelText: 'Task Name',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 10),
                    ListTile(
                      title: Text('Date: ${pickedDate!.toLocal().toString().split(' ')[0]}'),
                      trailing: const Icon(Icons.calendar_today),
                      onTap: () async {
                        final DateTime? date = await showDatePicker(
                          context: context, // Use the context from StatefulBuilder
                          initialDate: pickedDate!,
                          firstDate: DateTime(2000),
                          lastDate: DateTime(2101),
                        );
                        if (date != null) {
                          setModalState(() {
                            pickedDate = date;
                          });
                        }
                      },
                    ),
                    const SizedBox(height: 20),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: [
                        TextButton(
                          child: const Text('Cancel'),
                          onPressed: () {
                            Navigator.of(context).pop();
                          },
                        ),
                        ElevatedButton(
                          child: const Text('Update'),
                          onPressed: () {
                            if (taskNameController.text.isNotEmpty) {
                              context.read<CalendarTodoBloc>().add(
                                    UpdateCalendarTodoEvent(
                                      task: CalendarTodoEntity(
                                        id: task.id,
                                        taskName: taskNameController.text,
                                        taskDate: "${pickedDate!.year}-${pickedDate!.month.toString().padLeft(2, '0')}-${pickedDate!.day.toString().padLeft(2, '0')}",
                                        isCompleted: task.isCompleted,
                                      ),
                                    ),
                                  );
                              Navigator.of(context).pop();
                            }
                          },
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            );
          }
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Calendar To Do List'),
      ),
      body: Container(
        color: const Color(0xFFF0F0F0), // A light grey background
        child: Column(
          children: [
            _buildHeader(),
            Expanded(
              child: BlocBuilder<CalendarTodoBloc, CalendarTodoState>(
                builder: (context, state) {
                  if (state is CalendarTodoLoading) {
                    log('CalendarTodoPage - State: CalendarTodoLoading');
                    return const Center(child: CircularProgressIndicator());
                  } else if (state is CalendarTodoLoaded) {
                    log('CalendarTodoPage - State: CalendarTodoLoaded - ${state.tasks.length} tasks');
                    return ListView.builder(
                      itemCount: state.tasks.length,
                      itemBuilder: (context, index) {
                        return _CalendarTodoListItem(
                          task: state.tasks[index],
                          onEdit: (editedTask) => _showEditTaskBottomSheet(editedTask),
                        );
                      },
                    );
                  } else if (state is CalendarTodoError) {
                    log('CalendarTodoPage - State: CalendarTodoError - ${state.message}');
                    return Center(child: Text(state.message));
                  } else if (state is CalendarTodoActionSuccess) {
                    log('CalendarTodoPage - State: CalendarTodoActionSuccess - ${state.message}');
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text(state.message)),
                    );
                    _fetchTasksForSelectedDate(_selectedDate);
                  }
                  log('CalendarTodoPage - State: Initial or unknown');
                  return const Center(child: Text('No tasks for this date.'));
                },
              ),
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: _showAddTaskBottomSheet,
        backgroundColor: Colors.blue,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16.0),
      child: Row(
        children: [
          const Expanded(
            child: Text(
              'Pending Tasks', // From @string/pendingtaskheading
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: Colors.black,
              ),
            ),
          ),
          Image.asset(
            'assets/calendar_todo/taskpage.jpg',
            height: 110,
            width: 150,
            fit: BoxFit.cover,
          ),
        ],
      ),
    );
  }
}

class _CalendarTodoListItem extends StatelessWidget {
  const _CalendarTodoListItem({
    Key? key,
    required this.task,
    this.onEdit,
  }) : super(key: key);

  final CalendarTodoEntity task;
  final Function(CalendarTodoEntity)? onEdit;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      elevation: 2.0,
      child: Padding(
        padding: const EdgeInsets.all(10.0),
        child: Row(
          children: [
            Image.asset(
              'assets/ic_dashboard_pandingtask.png',
              width: 25,
              height: 25,
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    task.taskName,
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      decoration: task.isCompleted ? TextDecoration.lineThrough : TextDecoration.none,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    task.taskDate,
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey.shade600,
                      decoration: task.isCompleted ? TextDecoration.lineThrough : TextDecoration.none,
                    ),
                  ),
                ],
              ),
            ),
            IconButton(
              icon: const Icon(Icons.edit, color: Colors.grey),
              onPressed: () => onEdit?.call(task),
            ),
            Checkbox(
              value: task.isCompleted,
              onChanged: (bool? newValue) {
                if (newValue != null) {
                  context.read<CalendarTodoBloc>().add(
                        MarkCalendarTodoAsCompleteEvent(
                          id: task.id,
                          isCompleted: newValue,
                        ),
                      );
                }
              },
            ),
          ],
        ),
      ),
    );
  }
}
