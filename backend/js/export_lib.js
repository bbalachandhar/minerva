
var fnExcelReport = function(fileName){

    var uri = 'data:application/vnd.ms-excel;base64,'
      , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head><body><table  border="2px">{table}</table></body></html>'
      , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
      , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }

      var resolvedName = (fileName || window.excelExportFileName || 'export') + '.xls';
      table = document.getElementById('headerTable')
      var ctx = {worksheet: 'Worksheet', table: table.innerHTML}
      var a = document.createElement('a');
      a.href = uri + base64(format(template, ctx));
      a.download = resolvedName;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);

}