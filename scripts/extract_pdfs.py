"""
Extract text content from PDF rulebooks and save to structured format.
"""
import os
import json
from pathlib import Path
import fitz  # PyMuPDF

def extract_pdf_text(pdf_path: str) -> dict:
    """
    Extract text from a PDF file.
    
    Args:
        pdf_path: Path to the PDF file
        
    Returns:
        Dictionary with metadata and extracted text
    """
    print(f"Processing: {os.path.basename(pdf_path)}")
    
    try:
        doc = fitz.open(pdf_path)
        
        # Extract metadata
        metadata = {
            'filename': os.path.basename(pdf_path),
            'filepath': pdf_path,
            'page_count': len(doc),
            'title': doc.metadata.get('title', ''),
            'author': doc.metadata.get('author', ''),
            'subject': doc.metadata.get('subject', ''),
        }
        
        # Extract text from each page
        pages = []
        full_text = []
        
        for page_num in range(len(doc)):
            page = doc[page_num]
            text = page.get_text()
            
            if text.strip():  # Only include pages with content
                pages.append({
                    'page_number': page_num + 1,
                    'text': text
                })
                full_text.append(text)
        
        doc.close()
        
        result = {
            'metadata': metadata,
            'pages': pages,
            'full_text': '\n\n'.join(full_text)
        }
        
        print(f"  [OK] Extracted {len(pages)} pages")
        return result
        
    except Exception as e:
        print(f"  [ERROR] {str(e)}")
        return None

def main():
    """Extract all PDFs from reference/Books directory."""
    
    # Setup paths
    project_root = Path(__file__).parent.parent
    books_dir = project_root / 'reference' / 'Books'
    output_dir = project_root / 'data' / 'extracted_rulebooks'
    
    # Create output directory
    output_dir.mkdir(parents=True, exist_ok=True)
    
    # Find all PDF files
    pdf_files = list(books_dir.glob('*.pdf'))
    
    print(f"\nFound {len(pdf_files)} PDF files")
    print("=" * 60)
    
    # Extract each PDF
    extracted_data = []
    
    for pdf_path in pdf_files:
        result = extract_pdf_text(str(pdf_path))
        
        if result:
            # Save individual JSON file
            output_filename = pdf_path.stem + '.json'
            output_path = output_dir / output_filename
            
            with open(output_path, 'w', encoding='utf-8') as f:
                json.dump(result, f, indent=2, ensure_ascii=False)
            
            # Also save plain text version
            text_filename = pdf_path.stem + '.txt'
            text_path = output_dir / text_filename
            
            with open(text_path, 'w', encoding='utf-8') as f:
                f.write(result['full_text'])
            
            # Add to summary
            extracted_data.append({
                'filename': result['metadata']['filename'],
                'page_count': result['metadata']['page_count'],
                'output_json': str(output_path),
                'output_text': str(text_path)
            })
    
    # Save extraction summary
    summary_path = output_dir / '_extraction_summary.json'
    with open(summary_path, 'w', encoding='utf-8') as f:
        json.dump({
            'total_files': len(pdf_files),
            'successful': len(extracted_data),
            'files': extracted_data
        }, f, indent=2)
    
    print("=" * 60)
    print(f"\n[SUCCESS] Extraction complete!")
    print(f"  Processed: {len(extracted_data)} files")
    print(f"  Output directory: {output_dir}")
    print(f"  Summary: {summary_path}")

if __name__ == '__main__':
    main()

