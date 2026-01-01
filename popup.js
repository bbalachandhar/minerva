document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('exportExcel').addEventListener('click', () => {
    // Get the current tab
    chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
      const tab = tabs[0];

      // Execute a script in the current tab
      chrome.tabs.executeScript(tab.id, {
        code: `
          (() => {
            // Convert the table to a workbook
            const wb = XLSX.utils.table_to_book(document.querySelector('table'));

            // Write the workbook to a binary string
            const wbout = XLSX.write(wb, { bookType: 'xlsx', bookSST: true, type: 'binary' });

            // Convert the binary string to an ArrayBuffer
            const buf = new ArrayBuffer(wbout.length);
            const view = new Uint8Array(buf);
            for (let i = 0; i < wbout.length; i++) {
              view[i] = wbout.charCodeAt(i) & 0xFF;
            }

            // Create a Blob from the ArrayBuffer
            const blob = new Blob([buf], { type: 'application/octet-stream' });

            // Create a download link and click it
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'webpage-data.xlsx';
            a.click();
          })();
        `,
      });
    });
  });

  document.getElementById('exportPdf').addEventListener('click', () => {
    // Get the current tab
    chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
      const tab = tabs[0];

      // Execute a script in the current tab
      chrome.tabs.executeScript(tab.id, {
        code: `
          (() => {
            // Create a new jsPDF instance
            const doc = new jsPDF();

            // Add HTML content to the PDF
            doc.html(document.querySelector('table').outerHTML, {
              callback: () => {
                // Save the generated PDF
                doc.save('webpage-data.pdf');
              },
            });
          })();
        `,
      });
    });
  });
});