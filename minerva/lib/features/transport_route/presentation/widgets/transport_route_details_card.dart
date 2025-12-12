// minerva/lib/features/transport_route/presentation/widgets/transport_route_details_card.dart

import 'package:flutter/material.dart';
import 'package:minerva_flutter/features/transport_route/data/models/transport_route_model.dart';

class TransportRouteDetailsCard extends StatelessWidget {
  final TransportRouteModel transportRoute;
  final String primaryColor;
  final String imagesUrl;

  const TransportRouteDetailsCard({
    Key? key,
    required this.transportRoute,
    required this.primaryColor,
    required this.imagesUrl,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.zero,
      color: Colors.transparent, // Outer Card should be transparent
      elevation: 0, // Outer Card has 0dp elevation
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(0)), // Outer Card has 0dp radius
      child: Container(
        // decoration: BoxDecoration(
        //   color: Color(int.parse(primaryColor.replaceFirst('#', '0xFF'))), // Outer card background color
        //   borderRadius: BorderRadius.circular(20), // Outer card with 20dp corner radius
        // ),
        padding: const EdgeInsets.all(10),
        child: Card(
          // elevation: 3, // Inner Card has 3dp elevation
          // shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)), // Inner Card has 20dp corner radius
          color: Colors.white, // Inner card color
          child: Padding(
            padding: const EdgeInsets.all(10.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Padding(
                        padding: const EdgeInsets.only(left: 10.0),
                        child: Text(
                          'Transport Route is here', // from @string/transportheading
                          style: TextStyle(
                            color: Colors.grey[800], // textHeading
                            fontWeight: FontWeight.bold,
                            fontSize: 22,
                          ),
                        ),
                      ),
                    ),
                    Expanded(
                      child: Image.network(
                        transportRoute.vehiclePhoto.isNotEmpty
                            ? '$imagesUrl/uploads/vehicle_photo/${transportRoute.vehiclePhoto}'
                            : 'https://via.placeholder.com/150', // Placeholder image if no photo
                        height: 110,
                        fit: BoxFit.cover,
                                                  errorBuilder: (context, error, stackTrace) => Image.asset(
                                                    'assets/transport_page.jpg', // Use the copied JPG asset
                                                    height: 110,
                                                    fit: BoxFit.cover,
                                                  ),                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                _buildInfoRow('Route Title', transportRoute.routeTitle),
                _buildInfoRow('Vehicle No', transportRoute.vehicleNo),
                _buildInfoRow('Vehicle Model', transportRoute.vehicleModel),
                _buildInfoRow('Driver Name', transportRoute.driverName),
                _buildInfoRow('Driver Contact', transportRoute.driverContact),
                _buildInfoRow('Driver Licence', transportRoute.driverLicence),
                _buildInfoRow('Manufacture Year', transportRoute.manufactureYear),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8.0, vertical: 4.0),
      child: Row(
        children: [
          Expanded(
            flex: 1,
            child: Text(
              label,
              style: TextStyle(color: Colors.grey[800]), // textHeading
            ),
          ),
          Expanded(
            flex: 1,
            child: Text(
              value.isNotEmpty ? value : '-',
              style: TextStyle(color: Colors.grey), // hintDark
            ),
          ),
        ],
      ),
    );
  }
}
