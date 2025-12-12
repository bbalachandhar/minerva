import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:minerva_flutter/features/notice/presentation/bloc/notice_bloc.dart';
import 'package:minerva_flutter/features/notice/presentation/bloc/notice_event.dart';
import 'package:minerva_flutter/features/notice/presentation/bloc/notice_state.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:shared_preferences/shared_preferences.dart'; // Needed for colors
import 'package:minerva_flutter/features/notice/presentation/widgets/notice_list_item_widget.dart';

class NoticePage extends StatefulWidget {
  const NoticePage({Key? key}) : super(key: key);

  @override
  State<NoticePage> createState() => _NoticePageState();
}

class _NoticePageState extends State<NoticePage> {
  Color primaryColour = Colors.blue; // Default color
  Color secondaryColour = Colors.blue; // Default color
  String _imagesUrl = '';
  bool _isSuperAdminRestricted = false;

  @override
  void initState() {
    super.initState();
    _loadConfig();
    context.read<NoticeBloc>().add(const FetchNotices());
  }

  Future<void> _loadConfig() async {
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        primaryColour = Color(int.parse((prefs.getString('primaryColour') ?? Constants.defaultPrimaryColour).replaceAll('#', '0xff')));
        secondaryColour = Color(int.parse((prefs.getString('secondaryColour') ?? Constants.defaultSecondaryColour).replaceAll('#', '0xff')));
        _imagesUrl = prefs.getString(Constants.imagesUrl) ?? '';
        _isSuperAdminRestricted = prefs.getString(Constants.superadminRestriction) == 'enabled';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notice Board'),
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
        child: BlocBuilder<NoticeBloc, NoticeState>(
          builder: (context, state) {
            if (state is NoticeLoading) {
              return const Center(child: CircularProgressIndicator());
            } else if (state is NoticeLoaded) {
              return ListView.builder(
                itemCount: state.notices.length,
                itemBuilder: (context, index) {
                  final notice = state.notices[index];
                  return NoticeListItemWidget(
                    notice: notice,
                    imagesUrl: _imagesUrl,
                    isSuperAdminRestricted: _isSuperAdminRestricted,
                    primaryColour: primaryColour,
                    secondaryColour: secondaryColour,
                  );
                },
              );
            } else if (state is NoticeError) {
              return Center(child: Text(state.message));
            }
            return const Center(child: Text('Press button to load notices'));
          },
        ),
      ),
    );
  }
}
