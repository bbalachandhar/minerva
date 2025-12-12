// minerva/lib/features/transport_route/presentation/widgets/pickup_point_item.dart

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:minerva_flutter/features/transport_route/data/models/pickup_point_model.dart';

class PickupPointItem extends StatelessWidget {
  final PickupPointModel pickupPoint;
  final bool isPickupName;
  final String secondaryColor;

  const PickupPointItem({
    Key? key,
    required this.pickupPoint,
    required this.isPickupName,
    required this.secondaryColor,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    // Convert HH:mm:ss to hh:mm a
    String formattedTime = '';
    try {
      final inputFormat = DateFormat('HH:mm:ss');
      final outputFormat = DateFormat('hh:mm a');
      final dateTime = inputFormat.parse(pickupPoint.pickupTime);
      formattedTime = outputFormat.format(dateTime);
    } catch (e) {
      formattedTime = pickupPoint.pickupTime; // Fallback if parsing fails
    }

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Timeline/Vertical Line Section
          Padding(
            padding: const EdgeInsets.only(left: 10.0, right: 2.0),
            child: Column(
              children: [
                Expanded(
                  child: Container(
                    width: 4,
                    color: Color(int.parse(secondaryColor.replaceFirst('#', '0xFF'))),
                  ),
                ),
                Container(
                  width: 20,
                  height: 20,
                  decoration: BoxDecoration(
                    color: Colors.lightBlue, // circle_lightblue from Android
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    Icons.location_on, // Equivalent to ic_place_black
                    color: Colors.grey[800],
                    size: 10,
                  ),
                ),
                Expanded(
                  child: Container(
                    width: 4,
                    color: Color(int.parse(secondaryColor.replaceFirst('#', '0xFF'))),
                  ),
                ),
              ],
            ),
          ),
          // Horizontal Line (if needed, this was a separate view in Android)
          Container(
            width: 30, // Corresponds to the 30dp width of horizontallineView
            height: 4,
            margin: const EdgeInsets.only(top: 10), // Adjust to align with the circle
            color: Color(int.parse(secondaryColor.replaceFirst('#', '0xFF'))),
          ),
          Expanded(
            child: Card(
              margin: const EdgeInsets.only(right: 10.0, bottom: 10.0),
              elevation: 5,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
              color: isPickupName ? const Color(0xFFB0DD38) : Colors.white, // Conditional coloring
              child: Padding(
                padding: const EdgeInsets.all(10.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(5),
                      decoration: BoxDecoration(
                        color: isPickupName ? const Color(0xFFB0DD38) : Color(int.parse(secondaryColor.replaceFirst('#', '0xFF'))),
                        borderRadius: BorderRadius.circular(10), // Example, adjust as needed
                      ),
                      child: Text(
                        pickupPoint.pickupPoint,
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16, // primaryText
                          color: Colors.grey[800], // textHeading
                        ),
                      ),
                    ),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        const Icon(Icons.directions_bus, size: 20, color: Colors.grey), // distance_icon
                        const SizedBox(width: 5),
                        Text(
                          'Distance', // @string/distance
                          style: TextStyle(color: Colors.grey[800]), // textHeading
                        ),
                        const Spacer(),
                        Text(
                          pickupPoint.destinationDistance.toString(),
                          style: TextStyle(color: Colors.grey), // hintDark
                        ),
                      ],
                    ),
                    const SizedBox(height: 5),
                    Row(
                      children: [
                        const Icon(Icons.access_time, size: 20, color: Colors.grey), // ic_clock
                        const SizedBox(width: 5),
                        Text(
                          'Pickup Time', // @string/pickuptime
                          style: TextStyle(color: Colors.grey[800]), // textHeading
                        ),
                        const Spacer(),
                        Text(
                          formattedTime,
                          style: TextStyle(color: Colors.grey), // hintDark
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
