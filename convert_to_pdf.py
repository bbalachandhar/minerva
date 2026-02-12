#!/usr/bin/env python3
"""
Convert MINERVA Sales Presentation HTML to PDF
Uses weasyprint for high-quality PDF generation
"""

import sys
import subprocess

def check_and_install_weasyprint():
    """Check if weasyprint is installed, if not, install it"""
    try:
        import weasyprint
        return True
    except ImportError:
        print("WeasyPrint not found. Installing...")
        try:
            subprocess.check_call([sys.executable, '-m', 'pip', 'install', 'weasyprint'])
            import weasyprint
            return True
        except Exception as e:
            print(f"Error installing WeasyPrint: {e}")
            print("\nAlternatively, you can install it manually with:")
            print("  pip3 install weasyprint")
            return False

def convert_html_to_pdf(html_file, pdf_file):
    """Convert HTML file to PDF"""
    from weasyprint import HTML, CSS
    
    print(f"Converting {html_file} to {pdf_file}...")
    
    # Custom CSS for better PDF output
    custom_css = CSS(string='''
        @page {
            size: A4;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
        }
        @media print {
            .hero, section, .stats, .cta-section, footer {
                page-break-inside: avoid;
            }
        }
    ''')
    
    try:
        HTML(filename=html_file).write_pdf(
            pdf_file,
            stylesheets=[custom_css]
        )
        print(f"✓ PDF successfully created: {pdf_file}")
        return True
    except Exception as e:
        print(f"✗ Error converting to PDF: {e}")
        return False

if __name__ == "__main__":
    if not check_and_install_weasyprint():
        sys.exit(1)
    
    html_file = "minerva_sales_presentation.html"
    pdf_file = "MINERVA_Sales_Presentation.pdf"
    
    if convert_html_to_pdf(html_file, pdf_file):
        print("\n✓ Conversion complete!")
        print(f"  PDF saved as: {pdf_file}")
    else:
        print("\n✗ Conversion failed")
        sys.exit(1)
