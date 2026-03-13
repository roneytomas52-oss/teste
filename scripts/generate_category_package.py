#!/usr/bin/env python3
"""Gera pacote de importação em massa de categorias/subcategorias com imagens placeholder.

Saída:
- categories_import_new.xlsx   (para 'Carregar novos dados')
- categories_update_existing.xlsx (para 'Atualizar dados existentes')
- images/category/*.png
- README_IMPORTACAO.md
"""

from __future__ import annotations

import argparse
import csv
import math
from pathlib import Path
import zipfile
from xml.sax.saxutils import escape

HEADERS_NEW = ["Name", "Image", "ParentId", "Position", "Priority", "Status"]
HEADERS_UPDATE = ["Id", "Name", "Image", "ParentId", "Position", "Priority", "Status"]

# PNG transparente 1x1
TINY_PNG = bytes([
    0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A,
    0x00, 0x00, 0x00, 0x0D, 0x49, 0x48, 0x44, 0x52,
    0x00, 0x00, 0x00, 0x01, 0x00, 0x00, 0x00, 0x01,
    0x08, 0x06, 0x00, 0x00, 0x00, 0x1F, 0x15, 0xC4,
    0x89, 0x00, 0x00, 0x00, 0x0D, 0x49, 0x44, 0x41,
    0x54, 0x78, 0x9C, 0x63, 0xF8, 0xFF, 0xFF, 0x3F,
    0x00, 0x05, 0xFE, 0x02, 0xFE, 0xDC, 0xCC, 0x59,
    0xE7, 0x00, 0x00, 0x00, 0x00, 0x49, 0x45, 0x4E,
    0x44, 0xAE, 0x42, 0x60, 0x82,
])


def make_rows(total: int, sub_per_main: int, status: str):
    main_count = math.ceil(total / (sub_per_main + 1))
    rows = []
    current_id = 1

    for i in range(1, main_count + 1):
        if len(rows) >= total:
            break

        main_id = current_id
        image = f"category/cat-{main_id:05d}.png"
        rows.append({
            "Id": main_id,
            "Name": f"Categoria {i:05d}",
            "Image": image,
            "ParentId": 0,
            "Position": 0,
            "Priority": 0,
            "Status": status,
        })
        current_id += 1

        for j in range(1, sub_per_main + 1):
            if len(rows) >= total:
                break
            sub_id = current_id
            sub_image = f"category/sub-{sub_id:05d}.png"
            rows.append({
                "Id": sub_id,
                "Name": f"Subcategoria {i:05d}-{j:02d}",
                "Image": sub_image,
                "ParentId": main_id,
                "Position": 1,
                "Priority": 0,
                "Status": status,
            })
            current_id += 1

    return rows


def write_csv(path: Path, headers: list[str], rows: list[dict]):
    with path.open("w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=headers)
        writer.writeheader()
        for row in rows:
            writer.writerow({h: row.get(h, "") for h in headers})


def _xlsx_sheet_xml(headers: list[str], rows: list[dict]) -> str:
    all_rows = [headers] + [[str(row.get(h, "")) for h in headers] for row in rows]

    xml_rows = []
    for ridx, row in enumerate(all_rows, start=1):
        cells = []
        for cidx, val in enumerate(row, start=1):
            col = ""
            n = cidx
            while n:
                n, rem = divmod(n - 1, 26)
                col = chr(65 + rem) + col
            ref = f"{col}{ridx}"
            cells.append(f'<c r="{ref}" t="inlineStr"><is><t>{escape(val)}</t></is></c>')
        xml_rows.append(f'<row r="{ridx}">{"".join(cells)}</row>')

    return (
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        '<sheetData>' + "".join(xml_rows) + '</sheetData>'
        '</worksheet>'
    )


def write_xlsx(path: Path, headers: list[str], rows: list[dict]):
    content_types = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>""".strip()

    rels = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>""".strip()

    workbook = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Categorias" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>""".strip()

    workbook_rels = """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>""".strip()

    sheet = _xlsx_sheet_xml(headers, rows)

    with zipfile.ZipFile(path, "w", compression=zipfile.ZIP_DEFLATED) as zf:
        zf.writestr("[Content_Types].xml", content_types)
        zf.writestr("_rels/.rels", rels)
        zf.writestr("xl/workbook.xml", workbook)
        zf.writestr("xl/_rels/workbook.xml.rels", workbook_rels)
        zf.writestr("xl/worksheets/sheet1.xml", sheet)


def write_readme(path: Path, total: int):
    path.write_text(
        f"""# Pacote de importação em massa (categorias)

Este pacote foi gerado para **{total} registros** (categoria + subcategoria).

## Arquivos
- `categories_import_new.xlsx`: use em **Carregar novos dados**.
- `categories_update_existing.xlsx`: use em **Atualizar dados existentes** (contém coluna `Id`).
- `categories_import_new.csv`: versão CSV equivalente.
- `categories_update_existing.csv`: versão CSV equivalente.
- `images/category/*.png`: imagens placeholder separadas.

## Como usar no painel
1. Clique em **Modelo sem dados** (opcional) para comparar colunas.
2. Envie `categories_import_new.xlsx` em **Carregar novos dados**.
3. Copie as imagens de `images/category/` para a pasta `category/` do outro projeto.
4. O campo `Image` já está no padrão `category/nome-do-arquivo.png`.

## Observações
- `Position = 0` para categoria e `Position = 1` para subcategoria.
- `ParentId = 0` para categoria pai.
- `ParentId` da subcategoria aponta para o `Id` da categoria pai na planilha de atualização.
""",
        encoding="utf-8",
    )


def main():
    parser = argparse.ArgumentParser(description="Gera pacote de categorias para importação em massa")
    parser.add_argument("--total", type=int, default=10000, help="Total de linhas (mínimo recomendado: 10000)")
    parser.add_argument("--subs-per-main", type=int, default=4, help="Subcategorias por categoria")
    parser.add_argument("--status", default="active", choices=["active", "inactive"], help="Status padrão")
    parser.add_argument("--output-dir", default="output/category_import_package", help="Diretório de saída")
    args = parser.parse_args()

    output_dir = Path(args.output_dir)
    image_dir = output_dir / "images" / "category"
    image_dir.mkdir(parents=True, exist_ok=True)

    rows = make_rows(total=args.total, sub_per_main=args.subs_per_main, status=args.status)

    for row in rows:
        image_name = Path(row["Image"]).name
        (image_dir / image_name).write_bytes(TINY_PNG)

    write_csv(output_dir / "categories_import_new.csv", HEADERS_NEW, rows)
    write_csv(output_dir / "categories_update_existing.csv", HEADERS_UPDATE, rows)

    write_xlsx(output_dir / "categories_import_new.xlsx", HEADERS_NEW, rows)
    write_xlsx(output_dir / "categories_update_existing.xlsx", HEADERS_UPDATE, rows)

    write_readme(output_dir / "README_IMPORTACAO.md", total=len(rows))

    print(f"Pacote criado em: {output_dir}")
    print(f"Total de linhas: {len(rows)}")
    print(f"Total de imagens: {len(rows)}")


if __name__ == "__main__":
    main()
