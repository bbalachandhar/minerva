part of 'url_bloc.dart';

abstract class UrlState extends Equatable {
  const UrlState();

  @override
  List<Object> get props => [];
}

class UrlInitial extends UrlState {}

class UrlValidationLoading extends UrlState {}

class UrlValidationSuccess extends UrlState {}

class UrlValidationFailure extends UrlState {
  final String error;

  const UrlValidationFailure(this.error);

  @override
  List<Object> get props => [error];
}
