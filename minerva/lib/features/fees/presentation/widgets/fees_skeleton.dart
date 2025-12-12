import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';

class FeesSkeleton extends StatelessWidget {
  const FeesSkeleton({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: Colors.grey[300]!,
      highlightColor: Colors.grey[100]!,
      child: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(8.0),
          child: Column(
            children: [
              _buildHeaderSkeleton(),
              const SizedBox(height: 16),
              _buildActionButtonsSkeleton(),
              const SizedBox(height: 16),
              _buildGrandTotalCardSkeleton(),
              const SizedBox(height: 16),
              _buildFeesListSkeleton(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeaderSkeleton() {
    return Card(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(20.0),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Container(
              width: 100,
              height: 24,
              color: Colors.white,
            ),
            Container(
              width: 100,
              height: 100,
              color: Colors.white,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionButtonsSkeleton() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
      children: [
        Container(
          width: 100,
          height: 36,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
          ),
        ),
        Container(
          width: 120,
          height: 36,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
          ),
        ),
        Container(
          width: 120,
          height: 36,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
          ),
        ),
      ],
    );
  }

  Widget _buildGrandTotalCardSkeleton() {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(15.0),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 120,
              height: 20,
              color: Colors.white,
            ),
            const SizedBox(height: 16),
            _buildSummaryRowSkeleton(),
            _buildSummaryRowSkeleton(),
            _buildSummaryRowSkeleton(),
            _buildSummaryRowSkeleton(),
            _buildSummaryRowSkeleton(),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryRowSkeleton() {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Container(
            width: 80,
            height: 16,
            color: Colors.white,
          ),
          Container(
            width: 60,
            height: 16,
            color: Colors.white,
          ),
        ],
      ),
    );
  }

  Widget _buildFeesListSkeleton() {
    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: 5,
      itemBuilder: (context, index) {
        return _buildFeeListItemSkeleton();
      },
    );
  }

  Widget _buildFeeListItemSkeleton() {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 8.0),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(15.0),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  width: 150,
                  height: 20,
                  color: Colors.white,
                ),
                Container(
                  width: 50,
                  height: 20,
                  color: Colors.white,
                ),
              ],
            ),
            const SizedBox(height: 8.0),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 80,
                      height: 16,
                      color: Colors.white,
                      margin: const EdgeInsets.only(bottom: 4),
                    ),
                    Container(
                      width: 80,
                      height: 16,
                      color: Colors.white,
                      margin: const EdgeInsets.only(bottom: 4),
                    ),
                    Container(
                      width: 80,
                      height: 16,
                      color: Colors.white,
                    ),
                  ],
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Container(
                      width: 100,
                      height: 16,
                      color: Colors.white,
                      margin: const EdgeInsets.only(bottom: 4),
                    ),
                    Container(
                      width: 80,
                      height: 16,
                      color: Colors.white,
                      margin: const EdgeInsets.only(bottom: 4),
                    ),
                    Container(
                      width: 60,
                      height: 16,
                      color: Colors.white,
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 8.0),
            Align(
              alignment: Alignment.bottomRight,
              child: Container(
                width: 60,
                height: 36,
                color: Colors.white,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
