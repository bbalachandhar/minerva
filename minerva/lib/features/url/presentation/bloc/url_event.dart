part of 'url_bloc.dart';

abstract class UrlEvent extends Equatable {
  const UrlEvent();

  @override
  List<Object> get props => [];
}

class UrlSubmitted extends UrlEvent {
  final String url;

  const UrlSubmitted({required this.url});

  @override
  List<Object> get props => [url];
}
