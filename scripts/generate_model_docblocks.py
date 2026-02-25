#!/usr/bin/env python3
"""
Generate PHPDoc @property annotations for Eloquent models based on migrations.
"""

import os
import re
import glob
from pathlib import Path

def extract_columns_from_migration(migration_file):
    """Extract column definitions from a migration file."""
    columns = {}
    
    with open(migration_file, 'r') as f:
        content = f.read()
    
    # Match $table->columnType('columnName') patterns
    # Common patterns: string, integer, bigInteger, float, decimal, boolean, timestamp, dateTime, date, json, etc.
    pattern = r'\$table->(\w+)\([\'"](\w+)[\'"]'
    matches = re.findall(pattern, content)
    
    type_map = {
        'id': 'int',
        'bigIncrements': 'int',
        'increments': 'int',
        'integer': 'int',
        'bigInteger': 'int',
        'smallInteger': 'int',
        'float': 'float',
        'decimal': 'float',
        'string': 'string',
        'text': 'string',
        'longText': 'string',
        'boolean': 'bool',
        'timestamp': '\\Carbon\\Carbon',
        'timestamps': '\\Carbon\\Carbon',
        'dateTime': '\\Carbon\\Carbon',
        'date': '\\Carbon\\Carbon',
        'json': 'array',
        'jsonb': 'array',
        'array': 'array',
        'softDeletes': '\\Carbon\\Carbon',
        'nullableTimestamps': '\\Carbon\\Carbon|null',
    }
    
    for col_type, col_name in matches:
        php_type = type_map.get(col_type, 'mixed')
        columns[col_name] = php_type
    
    # Handle nullable columns
    nullable_pattern = r'\$table->(\w+)\([\'"](\w+)[\'"][^)]*\)->nullable\(\)'
    nullable_matches = re.findall(nullable_pattern, content)
    
    for col_type, col_name in nullable_matches:
        php_type = type_map.get(col_type, 'mixed')
        if '|null' not in php_type:
            columns[col_name] = f'{php_type}|null'
    
    return columns

def get_migrations_for_table(table_name, migrations_dir='/workspaces/mines/database/migrations'):
    """Find migration files for a given table."""
    migrations = []
    
    for migration_file in glob.glob(os.path.join(migrations_dir, '*.php')):
        with open(migration_file, 'r') as f:
            content = f.read()
            if f"Schema::create('{table_name}'" in content or f'Schema::create("{table_name}"' in content:
                migrations.append(migration_file)
    
    return migrations

def get_table_name_from_model(model_class):
    """Extract table name from a model class."""
    # Try to read $table property
    try:
        with open(model_class, 'r') as f:
            content = f.read()
            match = re.search(r"protected\s+\$table\s*=\s*['\"](\w+)['\"]", content)
            if match:
                return match.group(1)
    except:
        pass
    
    # Default: pluralize class name
    class_name = os.path.basename(model_class).replace('.php', '')
    return class_name.lower() + 's'

def generate_docblock(model_path):
    """Generate PHPDoc docblock for a model."""
    with open(model_path, 'r') as f:
        content = f.read()
    
    # Skip if already has @property
    if '@property' in content:
        return None
    
    class_name = os.path.basename(model_path).replace('.php', '')
    table_name = get_table_name_from_model(model_path)
    
    # Try to find migrations for this table
    migrations = get_migrations_for_table(table_name)
    
    if not migrations:
        return None
    
    columns = extract_columns_from_migration(migrations[0])
    
    # Always include standard columns
    standard_columns = {
        'id': 'int',
        'created_at': '\\Carbon\\Carbon',
        'updated_at': '\\Carbon\\Carbon',
    }
    
    columns.update(standard_columns)
    
    # Generate docblock
    docblock_lines = [
        f'/**',
        f' * {class_name} Model',
        f' *'
    ]
    
    for col_name, col_type in sorted(columns.items()):
        docblock_lines.append(f' * @property {col_type} ${col_name}')
    
    # Add common @method annotations
    method_lines = [
        f' *',
        f' * @method static \\Illuminate\\Database\\Eloquent\\Builder|{class_name} where(string $column, mixed $operator = null, mixed $value = null)',
        f' * @method static \\Illuminate\\Database\\Eloquent\\Builder|{class_name} whereIn(string $column, array $values)',
        f' * @method static \\Illuminate\\Database\\Eloquent\\Builder|{class_name} orderBy(string $column, string $direction = \'asc\')',
        f' * @method static {class_name}|null find(mixed $id, array $columns = [\'*\'])',
        f' * @method static {class_name} findOrFail(mixed $id, array $columns = [\'*\'])',
        f' * @method static \\Illuminate\\Database\\Eloquent\\Collection all(array $columns = [\'*\'])',
        f' */',
    ]
    
    docblock_lines.extend(method_lines)
    docblock = '\n'.join(docblock_lines)
    
    return docblock

def update_model_with_docblock(model_path, docblock):
    """Update model file with generated docblock."""
    with open(model_path, 'r') as f:
        content = f.read()
    
    # Find the class definition line
    class_pattern = r'^class\s+\w+\s+extends'
    match = re.search(class_pattern, content, re.MULTILINE)
    
    if not match:
        return False
    
    # Insert docblock before class definition
    insert_pos = match.start()
    
    # Remove any existing docblock or namespace trailing lines
    lines = content[:insert_pos].split('\n')
    
    # Find the line to insert at (after namespace and use statements)
    insert_line = 0
    for i, line in enumerate(lines):
        if re.match(r'^(namespace|use)\s+', line) or line.strip() == '':
            insert_line = i + 1
    
    # Rebuild content
    before = '\n'.join(lines[:insert_line]) + '\n\n' if insert_line > 0 else ''
    after = '\n'.join(lines[insert_line:]).lstrip('\n') + '\n'
    
    new_content = before + docblock + '\n' + after
    
    with open(model_path, 'w') as f:
        f.write(new_content)
    
    return True

# Main execution
if __name__ == '__main__':
    models_dir = '/workspaces/mines/app/Models'
    
    for model_file in glob.glob(os.path.join(models_dir, '*.php')):
        docblock = generate_docblock(model_file)
        
        if docblock:
            print(f'Updating {os.path.basename(model_file)}...')
            update_model_with_docblock(model_file, docblock)
        else:
            print(f'Skipping {os.path.basename(model_file)} (already has docblock or no migrations found)')
