import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/features/library/domain/entities/library_book_entity.dart';
import 'package:minerva_flutter/features/library/presentation/bloc/library_bloc.dart';
import 'package:minerva_flutter/features/library/presentation/widgets/available_book_list_item.dart';
import 'package:minerva_flutter/features/library/presentation/widgets/issued_book_list_item.dart';
import 'dart:developer';

class LibraryPage extends StatefulWidget {
  const LibraryPage({Key? key}) : super(key: key);

  @override
  State<LibraryPage> createState() => _LibraryPageState();
}

class _LibraryPageState extends State<LibraryPage> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _fetchLibraryBooks();
  }

  void _fetchLibraryBooks() {
    context.read<LibraryBloc>().add(FetchAvailableBooksEvent());
    context.read<LibraryBloc>().add(FetchIssuedBooksEvent());
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Library'),
      ),
      body: Column(
        children: [
          _buildHeader(),
          TabBar(
            controller: _tabController,
            tabs: const [
              Tab(text: 'Available Books'),
              Tab(text: 'Issued Books'),
            ],
          ),
          Expanded(
            child: BlocBuilder<LibraryBloc, LibraryState>(
              builder: (context, state) {
                if (state is LibraryLoading) {
                  log('LibraryPage - State: LibraryLoading');
                  return const Center(child: CircularProgressIndicator());
                } else if (state is LibraryLoaded) {
                  log('LibraryPage - State: LibraryLoaded - Available: ${state.availableBooks.length}, Issued: ${state.issuedBooks.length}');
                  return TabBarView(
                    controller: _tabController,
                    children: [
                      _buildAvailableBookList(state.availableBooks),
                      _buildIssuedBookList(state.issuedBooks),
                    ],
                  );
                } else if (state is LibraryError) {
                  log('LibraryPage - State: LibraryError - ${state.message}');
                  return Center(child: Text(state.message));
                }
                log('LibraryPage - State: Initial or unknown');
                return const Center(child: Text('Load Library Books'));
              },
            ),
          ),
        ],
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
              'Library', // From @string/bookheading
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
          Image.asset(
            'assets/librarypage.jpg', // Make sure this asset exists
            height: 110,
            width: 150,
            fit: BoxFit.cover,
          ),
        ],
      ),
    );
  }

  Widget _buildAvailableBookList(List<LibraryBookEntity> books) {
    if (books.isEmpty) {
      return const Center(child: Text('No books available.'));
    }
    return ListView.builder(
      itemCount: books.length,
      itemBuilder: (context, index) {
        return AvailableBookListItem(book: books[index]);
      },
    );
  }

  Widget _buildIssuedBookList(List<LibraryBookEntity> books) {
    if (books.isEmpty) {
      return const Center(child: Text('No issued books.'));
    }
    return ListView.builder(
      itemCount: books.length,
      itemBuilder: (context, index) {
        return IssuedBookListItem(book: books[index]);
      },
    );
  }
}
