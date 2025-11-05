"""
Extract specific DOCX files only
Usage: python scripts/extract_specific_rulebooks.py Toreador.docx Lasombra.docx
"""
import os
import json
import sys
from pathlib import Path

try:
    from docx import Document
    DOCX_AVAILABLE = True
except ImportError:
    DOCX_AVAILABLE = False
    print("Error: python-docx not installed. Install with: pip install python-docx")
    sys.exit(1)

def extract_docx_text(docx_path: str) -> dict:
    """Extract text from a DOCX file."""
    print(f"Processing: {os.path.basename(docx_path)}")
    
    try:
        doc = Document(docx_path)
        
        pages = []
        full_text = []
        current_page_text = []
        
        word_count = 0
        page_num = 1
        
        for para in doc.paragraphs:
            text = para.text.strip()
            if not text:
                continue
            
            if 'page break' in para.style.name.lower():
                if current_page_text:
                    pages.append({
                        'page_number': page_num,
                        'text': '\n'.join(current_page_text)
                    })
                    full_text.append('\n'.join(current_page_text))
                    page_num += 1
                    current_page_text = []
            
            current_page_text.append(text)
            word_count += len(text.split())
            
            if word_count >= 500:
                pages.append({
                    'page_number': page_num,
                    'text': '\n'.join(current_page_text)
                })
                full_text.append('\n'.join(current_page_text))
                page_num += 1
                current_page_text = []
                word_count = 0
        
        if current_page_text:
            pages.append({
                'page_number': page_num,
                'text': '\n'.join(current_page_text)
            })
            full_text.append('\n'.join(current_page_text))
        
        metadata = {
            'filename': os.path.basename(docx_path),
            'filepath': docx_path,
            'page_count': len(pages),
            'title': doc.core_properties.title or '',
            'author': doc.core_properties.author or '',
            'subject': doc.core_properties.subject or '',
        }
        
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
    """Extract specific DOCX files from command line arguments."""
    if len(sys.argv) < 2:
        print("Usage: python extract_specific_rulebooks.py <file1.docx> [file2.docx] ...")
        print("Example: python extract_specific_rulebooks.py Toreador.docx Lasombra.docx")
        sys.exit(1)
    
    project_root = Path(__file__).parent.parent
    books_dir = project_root / 'reference' / 'Books'
    output_dir = project_root / 'data' / 'extracted_rulebooks'
    
    output_dir.mkdir(parents=True, exist_ok=True)
    
    # Read existing summary if it exists
    summary_path = output_dir / '_extraction_summary.json'
    extracted_data = []
    
    if summary_path.exists():
        with open(summary_path, 'r', encoding='utf-8') as f:
            existing_summary = json.load(f)
            extracted_data = existing_summary.get('files', [])
            print(f"Found {len(extracted_data)} existing entries in summary")
    
    # Process each file argument
    for filename in sys.argv[1:]:
        # Try to find the file in Clan Books folder first, then root Books folder
        docx_path = books_dir / 'Clan Books' / filename
        if not docx_path.exists():
            docx_path = books_dir / filename
        
        if not docx_path.exists():
            print(f"[WARN] File not found: {filename} (searched in Clan Books and Books folders)")
            continue
        
        result = extract_docx_text(str(docx_path))
        
        if result:
            output_filename = docx_path.stem + '.json'
            output_path = output_dir / output_filename
            
            with open(output_path, 'w', encoding='utf-8') as f:
                json.dump(result, f, indent=2, ensure_ascii=False)
            
            text_filename = docx_path.stem + '.txt'
            text_path = output_dir / text_filename
            
            with open(text_path, 'w', encoding='utf-8') as f:
                f.write(result['full_text'])
            
            # Remove existing entry if present (for update)
            extracted_data = [f for f in extracted_data if f['filename'] != result['metadata']['filename']]
            
            # Add new entry
            extracted_data.append({
                'filename': result['metadata']['filename'],
                'page_count': result['metadata']['page_count'],
                'output_json': str(output_path),
                'output_text': str(text_path)
            })
            
            print(f"  [OK] Added to summary: {result['metadata']['filename']}")
    
    # Save updated summary
    with open(summary_path, 'w', encoding='utf-8') as f:
        json.dump({
            'total_files': len(extracted_data),
            'successful': len(extracted_data),
            'files': extracted_data
        }, f, indent=2)
    
    print(f"\n[SUCCESS] Extraction complete!")
    print(f"  Processed: {len(sys.argv) - 1} file(s)")
    print(f"  Total entries in summary: {len(extracted_data)}")

if __name__ == '__main__':
    main()

