


$(document).ready(function () {
    if (!$.fn.DataTable.isDataTable('.example')) {
        var table = $('.example').DataTable({
            "aaSorting": [],
            rowReorder: {
                selector: 'td:nth-child(2)'
            },
            //responsive: 'false',
            dom: "Bfrtip",
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: '<i class="fa fa-files-o"></i>',
                    titleAttr: 'Copy',
                    title: $('.download_label').html(),
                    exportOptions: {
                        columns: ["thead th:not(.noExport)"]
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Excel',
                    title: $('.download_label').html(),
                    action: function (e, dt, button, config) {
                        if ($('.staff-attendance-report').length) {
                            var role = $('#role').val() || '';
                            var month = $('#month').val() || '';
                            var year = $('#year').val() || '';
                            var url = baseurl + 'attendencereports/staffattendancereport_export_excel?role=' + encodeURIComponent(role) + '&month=' + encodeURIComponent(month) + '&year=' + encodeURIComponent(year);
                            window.location.href = url;
                            return;
                        }
                        $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                    },
                    customize: function (xlsx) {
                        var reportTitle = ($('.download_label').text() || '').toLowerCase();
                        var isStaffReport = $('.staff-attendance-report').length || reportTitle.indexOf('staff attendance report') !== -1;
                        if (!isStaffReport) {
                            return;
                        }

                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        var styles = xlsx.xl['styles.xml'];
                        var sharedStrings = xlsx.xl['sharedStrings.xml'];

                        var fills = $('fills', styles);
                        var cellXfs = $('cellXfs', styles);

                        var fillCount = parseInt(fills.attr('count'), 10) || 0;
                        var xfCount = parseInt(cellXfs.attr('count'), 10) || 0;

                        function addFill(rgb) {
                            fills.append('<fill><patternFill patternType="solid"><fgColor rgb="' + rgb + '"/><bgColor indexed="64"/></patternFill></fill>');
                            return fillCount++;
                        }

                        function addXf(fillId) {
                            var baseXf = $('xf', cellXfs).first().clone();
                            baseXf.attr('fillId', fillId);
                            baseXf.attr('applyFill', '1');
                            cellXfs.append(baseXf);
                            return xfCount++;
                        }

                        var presentFill = addFill('FFD4EDDA');
                        var halfDayFill = addFill('FFFFF3CD');
                        var absentFill = addFill('FFF8D7DA');
                        var weekendFill = addFill('FFF8D7DA');
                        var holidayFill = addFill('FFFFF3CD');

                        var presentXf = addXf(presentFill);
                        var halfDayXf = addXf(halfDayFill);
                        var absentXf = addXf(absentFill);
                        var weekendXf = addXf(weekendFill);
                        var holidayXf = addXf(holidayFill);

                        fills.attr('count', fillCount);
                        cellXfs.attr('count', xfCount);

                        function getCellText(cell) {
                            var type = cell.attr('t');
                            if (type === 'inlineStr') {
                                return $('is t', cell).text();
                            }

                            var v = $('v', cell).text();
                            if (!v) {
                                return '';
                            }
                            if (type === 's') {
                                if (sharedStrings && $('si', sharedStrings).length) {
                                    var si = $('si', sharedStrings).eq(parseInt(v, 10));
                                    return si.text();
                                }
                                return v;
                            }
                            return v;
                        }

                        $('row c', sheet).each(function () {
                            var cell = $(this);
                            var text = $.trim(getCellText(cell));
                            if (!text) {
                                text = $.trim(cell.text());
                            }
                            text = text.replace(/\s+/g, '').toUpperCase();
                            if (text === 'P') {
                                cell.attr('s', String(presentXf));
                            } else if (text === 'HD') {
                                cell.attr('s', String(halfDayXf));
                            } else if (text === 'A') {
                                cell.attr('s', String(absentXf));
                            } else if (text === 'W') {
                                cell.attr('s', String(weekendXf));
                            } else if (text === 'H') {
                                cell.attr('s', String(holidayXf));
                            }
                        });
                    },
                    exportOptions: {
                        columns: ["thead th:not(.noExport)"],
                        format: {
                            footer: function (data, row, column, node) {
                                // Include footer/grand total row in Excel export
                                return data;
                            }
                        }
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-text-o"></i>',
                    titleAttr: 'CSV',
                    title: $('.download_label').html(),
                    exportOptions: {
                        columns: ["thead th:not(.noExport)"]
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf-o"></i>',
                    titleAttr: 'PDF',
                    className: "btn-pdf",
                    title: $('.download_label').html(),
                    exportOptions: {
                        columns: ["thead th:not(.noExport)"]
                    },
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i>',
                    titleAttr: 'Print',
                    title: $('.download_label').html(),
                    customize: function (win) {
                        $(win.document.body).find('th').addClass('display').css('text-align', 'center');
                        $(win.document.body).find('td').addClass('display').css('text-align', 'left');
                        $(win.document.body).find('table').addClass('display').css('font-size', '14px');
                        $(win.document.body).find('h1').css('text-align', 'center');
                    },
                    exportOptions: {
                        stripHtml: false,
                        columns: ["thead th:not(.noExport)"]
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i class="fa fa-columns"></i>',
                    titleAttr: 'Columns',
                    title: $('.download_label').html(),
                    postfixButtons: ['colvisRestore']
                },
            ]
        });
    }
});

$(document).ready(function () {
    $('.gemini-datatable').DataTable({
        "destroy": true,
        "aaSorting": [],
        rowReorder: {
            selector: 'td:nth-child(2)'
        },
        dom: "Bfrtip",
        buttons: [
            {
                extend: 'copyHtml5',
                text: '<i class="fa fa-files-o"></i>',
                titleAttr: 'Copy',
                title: $('.download_label').html(),
                exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i>',
                titleAttr: 'Excel',
                title: $('.download_label').html(),
                exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-file-text-o"></i>',
                titleAttr: 'CSV',
                title: $('.download_label').html(),
                exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fa fa-file-pdf-o"></i>',
                titleAttr: 'PDF',
                className: "btn-pdf",
                title: $('.download_label').html(),
                exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                },
            },
            {
                extend: 'print',
                text: '<i class="fa fa-print"></i>',
                titleAttr: 'Print',
                title: $('.download_label').html(),
                customize: function (win) {
                    $(win.document.body).find('th').addClass('display').css('text-align', 'center');
                    $(win.document.body).find('td').addClass('display').css('text-align', 'left');
                    $(win.document.body).find('table').addClass('display').css('font-size', '14px');
                    $(win.document.body).find('h1').css('text-align', 'center');
                },
                exportOptions: {
                    stripHtml: false,
                    columns: ["thead th:not(.noExport)"]
                }
            },
            {
                extend: 'colvis',
                text: '<i class="fa fa-columns"></i>',
                titleAttr: 'Columns',
                title: $('.download_label').html(),
                postfixButtons: ['colvisRestore']
            },
        ]
    });
});

/*--dropify--*/
$(document).ready(function(){
                // Basic
                $('.filestyle').dropify();

                // Translated
                $('.dropify-fr').dropify({
                    messages: {
                        default: 'Glissez-déposez un fichier ici ou cliquez',
                        replace: 'Glissez-déposez un fichier ou cliquez pour remplacer',
                        remove:  'Supprimer',
                        error:   'Désolé, le fichier trop volumineux'
                    }
                });

                // Used events
                var drEvent = $('#input-file-events').dropify();

                drEvent.on('dropify.beforeClear', function(event, element){
                    return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
                });

                drEvent.on('dropify.afterClear', function(event, element){
                    alert('File deleted');
                });

                drEvent.on('dropify.errors', function(event, element){
                    console.log('Has Errors');
                });

                var drDestroy = $('#input-file-to-destroy').dropify();
                drDestroy = drDestroy.data('filestyle')
                $('#toggleDropify').on('click', function(e){
                    e.preventDefault();
                    if (drDestroy.isDropified()) {
                        drDestroy.destroy();
                    } else {
                        drDestroy.init();
                    }
                })
            });
/*--end dropify--*/

/*--nprogress--*/
$(document).ready(function(){
 $('body').show();
    $('.version').text(NProgress.version);
    NProgress.start();
    setTimeout(function() { NProgress.done(); $('.fade').removeClass('out'); }, 1000);
});
/*--nprogress--*/    
// _selector, // selector  class of table
// _url, // url is url of controller where data to be fetch
// params={}, is parameter of post method
// rm_export_btn=[], // var rm_export_btn = ["btn-pdf"] //"btn-copy","btn-excel","btn-csv","btn-pdf","btn-print" // btn-all
// pageLength=100, //per page data
// aoColumnDefs=[{ "bSortable": false, "aTargets": [ -1 ] ,'sClass': 'dt-body-right'}],
// searching=true,
// aaSorting=[],
// dataSrc="data" it is array source of data


   function initDatatable(_selector,_url,params={},rm_export_btn=[],pageLength=100,aoColumnDefs=[{ "bSortable": false, "aTargets": [ -1 ] ,'sClass': 'dt-body-right'}],searching=true,aaSorting=[],dataSrc="data"){
        if ($.fn.DataTable.isDataTable('.'+_selector)) { // if exist datatable it will destrory first
         $('.'+_selector).DataTable().destroy();
       }
        table= $('.'+_selector)
    .on( 'preInit.dt', function (e, settings ) {

     var api = new $.fn.dataTable.Api( settings );
     $.each(rm_export_btn, function(key, expt_select) {
     if(expt_select === "btn-all"){
       api.buttons().remove();

     }else{
       api.buttons('.'+expt_select).remove();

     }
    });

    }).DataTable({
        // "scrollX": true,

       dom: '<"top"f><Bl>r<t>ip',
         lengthMenu: [[100, -1], [100, "All"]],
          buttons: [
            {
                extend:    'copy',
                text:      '<i class="fa fa-files-o"></i>',
                titleAttr: 'Copy',
                 className: "btn-copy",
                title: $('.'+_selector).data("exportTitle"),
                  exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
            },
            {
                // Custom Excel export button
                text:      '<i class="fa fa-file-excel-o"></i>',
                titleAttr: 'Excel',
                className: "btn-excel",
                action: function ( e, dt, button, config ) {
                    var class_id = (params.class_id && params.class_id !== '') ? params.class_id : '';
                    var section_id = (params.section_id && params.section_id !== '') ? params.section_id : '';
                    window.location.href = baseurl + 'report/logindetailreport_export_excel?class_id=' + class_id + '&section_id=' + section_id;
                }
            },
            {
                // Custom CSV export button
                text:      '<i class="fa fa-file-text-o"></i>',
                titleAttr: 'CSV',
                className: "btn-csv",
                action: function ( e, dt, button, config ) {
                    var class_id = (params.class_id && params.class_id !== '') ? params.class_id : '';
                    var section_id = (params.section_id && params.section_id !== '') ? params.section_id : '';
                    window.location.href = baseurl + 'report/logindetailreport_export_csv?class_id=' + class_id + '&section_id=' + section_id;
                }
            },
            {
                // Custom PDF export button
                text:      '<i class="fa fa-file-pdf-o"></i>',
                titleAttr: 'PDF',
                className: "btn-pdf",
                action: function ( e, dt, button, config ) {
                    var class_id = (params.class_id && params.class_id !== '') ? params.class_id : '';
                    var section_id = (params.section_id && params.section_id !== '') ? params.section_id : '';
                    window.location.href = baseurl + 'report/logindetailreport_export_pdf?class_id=' + class_id + '&section_id=' + section_id;
                }
            },
            {
                extend:    'print',
                text:      '<i class="fa fa-print"></i>',
                titleAttr: 'Print',
                className: "btn-print",
                title: $('.'+_selector).data("exportTitle"),
                customize: function ( win ) {

                    $(win.document.body).find('th').addClass('display').css('text-align', 'center');
                    $(win.document.body).find('table').addClass('display').css('font-size', '14px');
                     $(win.document.body).find('td').addClass('display').css('text-align', 'left');
                    $(win.document.body).find('h1').css('text-align', 'center');
                },
                exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                    
                  }

            }
        ],
      
         // "scrollY":        "320px",
         
           "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span> ',
             sLengthMenu: "_MENU_"
        },
        "pageLength": pageLength,
        "searching": searching,
        "aaSorting": aaSorting, // default sorting [ [0,'asc'], [1,'asc'] ]
        "aoColumnDefs": aoColumnDefs, //disable sorting { "bSortable": false, "aTargets": [ 1,2 ] }
        "processing": true,
        "serverSide": true,

        "ajax":{
        "url": baseurl+_url,
        "dataSrc": dataSrc,
        "type": "POST",
        'data': params,
     }
     
    });
    }

   function emptyDatatable(_selector,dataSrc="data"){
          
        $('.'+_selector).DataTable({
        "searching": false,
        "processing": true,
        "paging":   false,
        "ordering": false,
        "info":     true,
        "ajax": {
            "url": base_url+'backend/json-files/datatable_empty.json',
            "dataSrc": dataSrc
        }
    });
    }