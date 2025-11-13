# Rulebooks Database Build Status

This document tracks which PDFs have been successfully extracted and which need attention.

**Last Updated:** January 6, 2025

---

## ✅ Successfully Extracted & Imported

These PDFs have text content extracted and are searchable in the database:

| Book | PDF Pages | Extracted Pages | Status |
|------|-----------|-----------------|--------|
| MET - VTM - Reference Guide | 22 | 22 | ✅ Complete |
| LOR - Laws of the Resurrection (5035) | 274 | ~274 | ✅ Complete |
| MET - DA - The Long Night (5008) | 194 | ~194 | ✅ Complete |
| MET - Dark Epics | 112 | ~112 | ✅ Complete |
| MET - Journal 1-8 (5401-5408) | 82 each | ~82 each | ✅ Complete |
| MET - LOE - Laws of the East (5016) | 298 | ~298 | ✅ Complete |
| MET - VTM - Anarch Guide (5040) | 186 | ~186 | ✅ Complete |
| MET - VTM - Laws of Elysium (5012) | ~100 | ~100 | ✅ Complete |
| MET - VTM - Sabbat Guide (5018) | ~100 | ~100 | ✅ Complete |
| MET - VTM - Storyteller's Guide (5021) | 184 | 184 | ✅ Complete |
| MTA - Laws of Ascension (5022) | 282 | 282 | ✅ Complete |
| MTA - Laws of Ascension Companion (5033) | 214 | 214 | ✅ Complete |
| WOD - Laws of Judgment (5099) | 298 | 298 | ✅ Complete |
| WOD - Dark Epics | 112 | 112 | ✅ Complete |
| VTM - Blood Magic - Secrets of Thaumaturgy | 162 | 162 | ✅ Complete (DOCX) |
| MET - VTM - Liber des Goules (text) (5006) | 59 | 59 | ✅ Complete (DOCX) |
| VTM - Counsel of Primogen | 151 | 151 | ✅ Complete (DOCX) |
| Wraith - MET - Oblivion (5400) | 186 | 186 | ✅ Complete (DOCX) |

---

## ⚠️ Image-Based / Unreadable PDFs

These PDFs appear to be scanned images and cannot be extracted with standard text extraction methods:

| Book | PDF Pages | Issue |
|------|-----------|-------|
| Guide to the Camarilla | 0 | No pages detected |
| MET - VTM - Camarilla Guide (5017) | 101 | Image-based scan, no extractable text |
| MET - VTM - Introductory Kit | 32 | Image-based / zlib error |
| MET - DA - The Long Night (5008) | 194 | Partially extractable (0 pages) |
| VTM - Blood Sacrifice - The Thaumaturgy Companion | 98 | Likely image-based |
| MET - VTM - Liber des Goules (5006) | 107 | Image-based (text version available as DOCX) |
| MET - WTO - Oblivion (5400) | 259 | Likely image-based |

---

## 📊 Summary Statistics

- **Total Files:** 36 (31 PDFs + 5 DOCX)
- **Successfully Extracted:** 24 books (~4,900+ pages of searchable text)
- **Needs OCR/Alternative Method:** 6 books (~900+ pages)
- **Success Rate:** 80% (up from 62%)

---

## 🔧 Solutions for Image-Based PDFs

### Option 1: OCR (Optical Character Recognition)
- Use Tesseract OCR with image preprocessing
- Convert PDF images to text using pytesseract
- Requires significant processing time

### Option 2: Find Text-Based Versions
- Look for digital/PDF versions with actual text layers
- Some older White Wolf books have been released in text format

### Option 3: Manual Transcription
- For critical books, manually transcribe sections
- Most efficient for frequently used content

### Option 4: Hybrid Approach
- Import book metadata into database (already done)
- Keep searchable books fully searchable
- Link to PDF files for manual reference for unreadable books

---

## 📝 Notes

- All books are stored in the `rulebooks` database table
- Books with extractable text have full page-by-page search
- Books without extractable text have metadata only
- Web interface at: https://vbn.talkingheads.video/admin/rulebooks_search.php
- Laws Agent at: https://vbn.talkingheads.video/agents/laws_agent/

---

## 🎯 Next Steps

1. ✅ Import extracted DOCX books to database
2. Find missing 5 official MET books (Laws of the Night Revised, Laws of the Hunt, etc.)
3. Consider OCR for remaining 6 image-based PDFs
4. Prioritize frequently used books for conversion

