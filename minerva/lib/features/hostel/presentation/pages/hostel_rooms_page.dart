import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/features/hostel/domain/entities/hostel_room_entity.dart';
import 'package:minerva_flutter/features/hostel/presentation/bloc/hostel_bloc.dart';
import 'dart:developer';

class HostelRoomsPage extends StatefulWidget {
  const HostelRoomsPage({Key? key}) : super(key: key);

  @override
  State<HostelRoomsPage> createState() => _HostelRoomsPageState();
}

class _HostelRoomsPageState extends State<HostelRoomsPage> {
  @override
  void initState() {
    super.initState();
    context.read<HostelBloc>().add(FetchHostelRoomsEvent());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Hostel Rooms'),
      ),
      body: Container(
        color: const Color(0xFFF0F0F0), // A light grey background
        child: BlocBuilder<HostelBloc, HostelState>(
          builder: (context, state) {
            if (state is HostelLoading) {
              log('HostelRoomsPage - State: HostelLoading');
              return const Center(child: CircularProgressIndicator());
            } else if (state is HostelLoaded) {
              log('HostelRoomsPage - State: HostelLoaded - ${state.rooms.length} rooms');
              return Column(
                children: [
                  _buildHeader(), // Now part of the loaded state
                  Expanded(
                    child: ListView.builder(
                      itemCount: state.rooms.length,
                      itemBuilder: (context, index) {
                        return _HostelRoomListItem(room: state.rooms[index]);
                      },
                    ),
                  ),
                ],
              );
            } else if (state is HostelError) {
              log('HostelRoomsPage - State: HostelError - ${state.message}');
              return Center(child: Text(state.message));
            }
            log('HostelRoomsPage - State: Initial or unknown');
            return const Center(child: Text('Press button to load hostels (or initial state)'));
          },
        ),
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
              'Hostel',
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: Colors.black,
              ),
            ),
          ),
          Image.asset(
            'assets/hostel/hostelpage.jpg',
            height: 110,
            width: 150,
            fit: BoxFit.cover,
          ),
        ],
      ),
    );
  }
}

class _HostelRoomListItem extends StatelessWidget {
  const _HostelRoomListItem({Key? key, required this.room}) : super(key: key);

  final HostelRoomEntity room;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15.0)),
      elevation: 5.0,
      child: Column(
        children: [
          _buildCardHeader(),
          _buildCardBody(),
        ],
      ),
    );
  }

  Widget _buildCardHeader() {
    // Assuming status from API is a string like 'Assigned' or 'Vacant'
    final bool isAssigned = room.status.toLowerCase() == 'assigned';
    return Container(
      padding: const EdgeInsets.all(10.0),
      decoration: BoxDecoration(
        color: const Color(0xFFE7F1EE),
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(15.0),
          topRight: Radius.circular(15.0),
        ),
        // Add border to match native green_border / red_border if status indicates
        border: Border.all(
          color: isAssigned ? Colors.green : Colors.transparent, // Only green border for assigned
          width: 1.0,
        ),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            room.hostelName,
            style: const TextStyle(
              fontWeight: FontWeight.bold,
              fontSize: 16,
              color: Colors.black,
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8.0, vertical: 4.0),
            decoration: BoxDecoration(
              color: isAssigned ? Colors.green : Colors.red,
              borderRadius: BorderRadius.circular(5.0),
            ),
            child: Text(
              room.status, // Display actual status string from API
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCardBody() {
    return Padding(
      padding: const EdgeInsets.all(10.0),
      child: Column(
        children: [
          _buildDetailRow('Room Type', room.roomType),
          _buildDetailRow('Room No', room.roomNumber),
          _buildDetailRow('No of Beds', room.numberOfBeds),
          _buildDetailRow('Cost per Bed', room.costPerBed),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: const TextStyle(
                fontSize: 14,
                color: Colors.black,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 14,
                color: Colors.black54,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
