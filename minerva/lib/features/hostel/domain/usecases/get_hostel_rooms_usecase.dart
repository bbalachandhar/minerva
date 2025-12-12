import 'package:minerva_flutter/features/hostel/domain/entities/hostel_room_entity.dart';
import 'package:minerva_flutter/features/hostel/domain/repositories/hostel_repository.dart';

class GetHostelRoomsUseCase {
  final HostelRepository repository;

  GetHostelRoomsUseCase({required this.repository});

  Future<List<HostelRoomEntity>> call() async {
    return await repository.getHostelRooms();
  }
}
