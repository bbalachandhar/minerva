#!/bin/bash
# Convert MINERVA Sales Presentation HTML to PDF using Chrome/Chromium
# This script uses Chrome's headless mode for PDF generation

HTML_FILE="minerva_sales_presentation.html"
PDF_FILE="MINERVA_Sales_Presentation.pdf"
TEMP_HTML="minerva_sales_presentation_no_break.html"

# Find Chrome/Chromium executable
if [[ -f "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" ]]; then
    CHROME="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
elif [[ -f "/Applications/Chromium.app/Contents/MacOS/Chromium" ]]; then
    CHROME="/Applications/Chromium.app/Contents/MacOS/Chromium"
elif command -v google-chrome &> /dev/null; then
    CHROME="google-chrome"
elif command -v chromium &> /dev/null; then
    CHROME="chromium"
else
    echo "❌ Chrome/Chromium not found!"
    echo ""
    echo "Please install Google Chrome or use one of these alternatives:"
    echo "  1. Install wkhtmltopdf: brew install wkhtmltopdf"
    echo "  2. Open the HTML file in a browser and print to PDF manually"
    exit 1
fi

echo "🔍 Found Chrome: $CHROME"
echo "📄 Preparing HTML for single-page PDF..."

# Create a modified HTML with CSS for single long page
cat "$HTML_FILE" | sed 's/<\/style>/@media print { * { page-break-inside: avoid !important; page-break-before: avoid !important; page-break-after: avoid !important; } body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; overflow: visible !important; } } @page { size: 210mm 15000mm; margin: 0; }<\/style>/' > "$TEMP_HTML"

echo "📄 Converting to single-page PDF: $HTML_FILE → $PDF_FILE"
echo ""

# Convert HTML to PDF using Chrome headless with custom very tall page
"$CHROME" \
    --headless=new \
    --disable-gpu \
    --run-all-compositor-stages-before-draw \
    --print-to-pdf="$PDF_FILE" \
    --no-pdf-header-footer \
    "file://$(pwd)/$TEMP_HTML" 2>/dev/null

# Clean up temporary file
rm -f "$TEMP_HTML"

if [[ -f "$PDF_FILE" ]]; then
    echo "✅ Single-page PDF successfully created!"
    echo "📁 Location: $(pwd)/$PDF_FILE"
    echo ""
    echo "File size: $(du -h "$PDF_FILE" | cut -f1)"
else
    echo "❌ PDF generation failed"
    exit 1
fi
