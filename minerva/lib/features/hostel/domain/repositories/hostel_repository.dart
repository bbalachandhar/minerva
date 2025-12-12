import 'package:minerva_flutter/features/hostel/domain/entities/hostel_room_entity.dart';

abstract class HostelRepository {
  Future<List<HostelRoomEntity>> getHostelRooms();
}
