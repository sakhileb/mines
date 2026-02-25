#!/usr/bin/env python3
import os
import textwrap
from reportlab.lib.pagesizes import A4
from reportlab.pdfgen import canvas
from reportlab.lib.units import mm

IN_MD = os.path.join(os.path.dirname(__file__), '..', 'docs', 'project-specification.md')
OUT_PDF = os.path.join(os.path.dirname(__file__), '..', 'docs', 'project-specification.pdf')

def load_markdown(path):
    with open(path, 'r', encoding='utf-8') as f:
        return f.read()


def draw_text(c, text, x, y, max_width, leading=12):
    lines = []
    for paragraph in text.split('\n\n'):
        paragraph = paragraph.strip()
        if not paragraph:
            lines.append('')
            continue
        # collapse multiple spaces
        paragraph = ' '.join(paragraph.split())
        wrapped = textwrap.wrap(paragraph, width=100)
        lines.extend(wrapped)
        lines.append('')
    textobject = c.beginText()
    textobject.setTextOrigin(x, y)
    textobject.setLeading(leading)
    for line in lines:
        textobject.textLine(line)
    c.drawText(textobject)


def generate(pdf_path, md_path):
    md = load_markdown(md_path)
    os.makedirs(os.path.dirname(pdf_path), exist_ok=True)
    c = canvas.Canvas(pdf_path, pagesize=A4)
    width, height = A4
    margin = 20 * mm
    x = margin
    y = height - margin
    c.setFont('Helvetica-Bold', 16)
    title = 'Project Specification: Mines'
    c.drawString(x, y, title)
    y -= 12 * mm
    c.setFont('Helvetica', 10)
    # split into sections and paginate manually
    paragraphs = md.split('\n\n')
    text_lines = []
    for p in paragraphs:
        text_lines.extend(textwrap.wrap(p.replace('\n', ' '), 110))
        text_lines.append('')

    line_height = 12
    max_lines_per_page = int((y - margin) / line_height)
    cur_line = 0
    page_lines = []
    for line in text_lines:
        if cur_line >= max_lines_per_page:
            # flush page
            textobject = c.beginText()
            textobject.setTextOrigin(x, height - margin - 12*mm)
            textobject.setLeading(line_height)
            for pl in page_lines:
                textobject.textLine(pl)
            c.drawText(textobject)
            c.showPage()
            c.setFont('Helvetica', 10)
            page_lines = []
            cur_line = 0
        page_lines.append(line)
        cur_line += 1
    # last page
    if page_lines:
        textobject = c.beginText()
        textobject.setTextOrigin(x, height - margin - 12*mm)
        textobject.setLeading(line_height)
        for pl in page_lines:
            textobject.textLine(pl)
        c.drawText(textobject)
    c.save()

if __name__ == '__main__':
    md_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'docs', 'project-specification.md'))
    pdf_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'docs', 'project-specification.pdf'))
    generate(pdf_path, md_path)
    print('Wrote', pdf_path)
