# Geração de planilha compatível (10k+) com pasta de imagens

Use o script abaixo para gerar um pacote completo para importação em massa de **categorias/subcategorias**:

```bash
python3 scripts/generate_category_package.py --total 10000 --output-dir output/category_import_package
```

## O que é gerado

- `categories_import_new.xlsx` (novo cadastro)
- `categories_update_existing.xlsx` (atualização com `Id`)
- versões `.csv`
- pasta `images/category/` com uma imagem por linha
- `README_IMPORTACAO.md` com passo a passo

## Formato compatível com o importador

Colunas principais:
- `Name`
- `Image`
- `ParentId`
- `Position`
- `Priority`
- `Status`

Regras aplicadas:
- Categoria pai: `Position=0`, `ParentId=0`
- Subcategoria: `Position=1`, `ParentId=<id da categoria pai>`
- `Image` no padrão: `category/arquivo.png`

## Ajustes úteis

- Para mais de 10k linhas:

```bash
python3 scripts/generate_category_package.py --total 15000
```

- Para alterar quantidade de subcategorias por categoria:

```bash
python3 scripts/generate_category_package.py --total 10000 --subs-per-main 6
```
