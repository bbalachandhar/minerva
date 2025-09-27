<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string and its
| corresponding controller class/method. The segments in a URI normally follow
| this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one in the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
|   This route indicates which controller class should be loaded if the
|   URI contains no data. In the above example, the "welcome" class
|   would be loaded.
|
|	$route['404_override'] = '';
|
|   This route will tell the Router which controller/method to use if an
|   incoming URL doesn't match any of the defined routes.
|
|	$route['translate_uri_dashes'] = FALSE;
|
|   This is not exactly a route, but it tells the URI class whether to convert
|   dashes in the URI to underscores. Typically, CodeIgniter controllers
|   and method names use underscores.
|
| <p>You can add your own custom routes here. Please read the User Guide for more info.</p>
|
|
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['admin/librarycategory'] = 'admin/librarycategory';
$route['admin/librarycategory/(:any)'] = 'admin/librarycategory/$1';
$route['admin/librarysubcategory'] = 'admin/librarysubcategory';
$route['admin/librarysubcategory/(:any)'] = 'admin/librarysubcategory/$1';
$route['admin/librarypublisher'] = 'admin/librarypublisher';
$route['admin/librarypublisher/(:any)'] = 'admin/librarypublisher/$1';
$route['admin/libraryvendor'] = 'admin/libraryvendor';
$route['admin/libraryvendor/(:any)'] = 'admin/libraryvendor/$1';
$route['admin/librarybooktype'] = 'admin/librarybooktype';
$route['admin/librarybooktype/(:any)'] = 'admin/librarybooktype/$1';
$route['admin/librarysubject'] = 'admin/librarysubject';
$route['admin/librarysubject/(:any)'] = 'admin/librarysubject/$1';
$route['admin/librarypositionrack'] = 'admin/librarypositionrack';
$route['admin/librarypositionrack/(:any)'] = 'admin/librarypositionrack/$1';
$route['admin/librarypositionshelf'] = 'admin/librarypositionshelf';
$route['admin/librarypositionshelf/(:any)'] = 'admin/librarypositionshelf/$1';