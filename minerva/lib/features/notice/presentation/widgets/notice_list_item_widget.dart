// minerva/lib/features/notice/presentation/widgets/notice_list_item_widget.dart
import 'package:flutter/material.dart';
import 'package:minerva_flutter/features/notice/domain/entities/notice_entity.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:url_launcher/url_launcher.dart'; // Import url_launcher

class NoticeListItemWidget extends StatelessWidget {
  final NoticeEntity notice;
  final String imagesUrl;
  final bool isSuperAdminRestricted;
  final Color primaryColour;
  final Color secondaryColour;

  const NoticeListItemWidget({
    Key? key,
    required this.notice,
    required this.imagesUrl,
    required this.isSuperAdminRestricted,
    required this.primaryColour,
    required this.secondaryColour,
  }) : super(key: key);

  void _launchURL(String url) async {
    final uri = Uri.parse(url);
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      throw 'Could not launch $url';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container( // Outer container matching the LinearLayout in XML
      margin: const EdgeInsets.symmetric(horizontal: 5.0), // Equivalent to layout_marginStart/End="5dp"
      child: Card( // Inner CardView
        elevation: 5.0, // Equivalent to app:cardElevation="5dp"
        margin: const EdgeInsets.all(10.0), // Equivalent to android:layout_margin="10dp"
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15.0)), // app:cardCornerRadius="15dp"
        color: Colors.white,
        child: Column( // Main vertical LinearLayout inside CardView
          children: [
            // Header Section
            Container( // studentNotificationAdapter_headLayout
              padding: const EdgeInsets.all(10.0), // padding="10dp"
              decoration: BoxDecoration(color: secondaryColour), // background="#E7F1EE"
              child: Row(
                children: [
                  Expanded( // To give space for title and attachment to stretch
                    child: Text(
                      notice.title,
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 18.0, // @dimen/heading assumed to be 18
                        color: primaryColour, // @color/textHeading
                      ),
                    ),
                  ),
                  if (notice.attachment.isNotEmpty)
                    GestureDetector(
                      onTap: () {
                        if (imagesUrl.isNotEmpty && notice.attachment.isNotEmpty) {
                          final fullAttachmentUrl = '$imagesUrl${notice.attachment}';
                          _launchURL(fullAttachmentUrl);
                        } else {
                          // Optionally show a snackbar or toast that attachment is not available
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Attachment URL not available')),
                          );
                        }
                      },
                      child: Container( // attachment_layout
                        width: 100.0, // 100dp
                        height: 30.0, // 30dp
                        decoration: BoxDecoration(
                          color: Colors.transparent, // background="@color/transparent"
                          borderRadius: BorderRadius.circular(5.0), // Some border radius for clickable area
                          // Border removed as requested by the user
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.file_download, size: 15.0, color: primaryColour), // @drawable/ic_file_download, @color/hyperLink
                            const SizedBox(width: 5.0),
                            Text(
                              'Download', // @string/download
                              style: TextStyle(color: primaryColour), // @color/hyperLink
                            ),
                          ],
                        ),
                      ),
                    ),
                ],
              ),
            ),
            // Description Section
            Padding(
              padding: const EdgeInsets.all(10.0), // layout_margin="10dp"
              child: Align(
                alignment: Alignment.topLeft, // Default alignment for text
                child: Html(
                  data: notice.message,
                  style: {
                    "body": Style(
                      fontSize: FontSize(14.0), // @dimen/secondaryText assumed 14
                      color: Colors.black, // @color/textHeading
                    ),
                  },
                ),
              ),
            ),
            // Details Section (Notice Date, Publish Date, Created By)
            Padding(
              padding: const EdgeInsets.all(10.0), // Add padding for this section as well
              child: Column(
                children: [
                  _buildDetailRow(
                    icon: Icons.calendar_today, // ic_nav_attendance or ic_calender
                    label: 'Publish Date:', // @string/publishDate
                    value: notice.publishDate,
                    color: primaryColour,
                  ),
                  _buildDetailRow(
                    icon: Icons.date_range, // ic_calender
                    label: 'Notice Date:', // @string/noticeDate
                    value: notice.date,
                    color: primaryColour,
                  ),
                  // Conditionally show Created By layout based on superadmin_restriction
                  if (isSuperAdminRestricted)
                    _buildDetailRow(
                      icon: Icons.person, // ic_nav_teachers
                      label: 'Created By:', // @string/createdBy
                      value: '${notice.createdBy} (${notice.employeeId})',
                      color: primaryColour,
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow({
    required IconData icon,
    required String label,
    required String value,
    required Color color,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Icon(icon, size: 20.0, color: color), // 25dp in XML
          const SizedBox(width: 10.0), // 10dp marginStart
          Text(
            label,
            style: TextStyle(
              fontWeight: FontWeight.bold,
              fontSize: 14.0, // @dimen/secondaryText assumed 14
              color: primaryColour, // @color/textHeading
            ),
          ),
          const SizedBox(width: 5.0),
          Expanded( // Use Expanded to prevent overflow if value is long
            child: Text(
              value,
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 14.0, // @dimen/secondaryText assumed 14
                color: Colors.grey, // @color/hintDark
              ),
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }
}