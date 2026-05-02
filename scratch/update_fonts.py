import os
import re
import math
from pathlib import Path

# Font size mapping
font_map = {
    9: 11,
    10: 12,
    11: 13,
    12: 14,
    13: 16,
    14: 17,
    15: 18,
    16: 19,
    18: 22,
    20: 24,
    22: 26,
    24: 29,
    26: 31,
    28: 34,
}

# Tailwind mapping
tailwind_map = {
    'text-xs': 'text-sm',
    'text-sm': 'text-base',
    'text-base': 'text-lg',
    'text-lg': 'text-xl',
    'text-xl': 'text-2xl',
}

base_dir = Path("C:/Users/Christian/Desktop/projects/smart-inventory/resources/views")
files_changed_fonts = {}
files_changed_media = {}

def process_file(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
    except Exception as e:
        print(f"Error reading {filepath}: {e}")
        return

    original_content = content
    font_changes_count = 0
    
    # 1. Update font-size in style attributes (e.g. style="font-size:13px")
    # Matches font-size: 13px, font-size:13px, font-size: 13px;
    def font_replacer(match):
        nonlocal font_changes_count
        val_str = match.group(1)
        val = int(val_str)
        if val in font_map:
            new_val = font_map[val]
        else:
            new_val = round(val * 1.2)
        font_changes_count += 1
        return f"font-size:{new_val}px"
    
    content = re.sub(r'font-size\s*:\s*(\d+)px', font_replacer, content)

    # 2. Update Tailwind classes
    for old_cls, new_cls in tailwind_map.items():
        # Using word boundaries to avoid matching parts of other words
        # but in HTML classes we can just check word boundaries or class="... text-xs ..."
        # A simple re.sub with \b
        def class_replacer(match):
            nonlocal font_changes_count
            font_changes_count += 1
            return new_cls
        content = re.sub(rf'\b{old_cls}\b', class_replacer, content)

    # 3. Add @media rules if they are missing
    media_added = False
    
    # 2A - Transfer List
    fix_2a = """
/* 2A - Transfer List Fixes */
@media(max-width:900px) {
    .tl-pipeline { grid-template-columns: repeat(3, 1fr); }
}
@media(max-width:600px) {
    .tl-pipeline { grid-template-columns: repeat(2, 1fr); gap:0; }
    .tl-pipeline-step { padding:10px 12px; }
    .tl-step-num  { font-size:20px; }
    .tl-step-sub  { display:none; }
    .tl-card-top    { flex-direction:column; padding:0 14px; }
    .tl-card-stats  { border-left:none; border-top:1px solid var(--border); margin:0 0 8px; flex-wrap:wrap; }
    .tl-stat        { padding:8px 14px; flex:1; min-width:80px; }
    .tl-bar         { gap:4px; padding:8px 10px; }
    .tl-chip        { padding:4px 10px; font-size:11px; }
    .tl-search      { width:100%; margin-left:0; margin-top:6px; }
    .tl-search input{ width:100%; }
    .tl-route-dash-line { width:20px; }
    .tl-card-foot   { flex-wrap:wrap; gap:6px; }
    .tl-action      { flex:1; justify-content:center; }
    .tl-foot-time   { width:100%; text-align:center; margin-left:0; }
    .tl-page-header         { flex-direction:column; align-items:flex-start; }
    .tl-page-header-left h1 { font-size:20px; }
    .tl-new-btn             { width:100%; justify-content:center; }
}
"""

    # 2B - Request Form
    fix_2b = """
/* 2B - Request Form Fixes */
@media(max-width:860px) {
    .rf-layout { grid-template-columns:1fr; }
    .rf-summary { position:static; }
}
@media(max-width:600px) {
    .rf-row2 { grid-template-columns:1fr; }
    .rf-prod-row    { flex-wrap:wrap; gap:8px; }
    .rf-prod-info   { width:100%; }
    .rf-stock       { align-items:flex-start; }
    .rf-add-btn     { width:100%; justify-content:center; }
    .rf-item-top    { flex-wrap:wrap; }
    .rf-qty-ctrl    { width:100%; justify-content:space-between; }
}
"""

    # 2C - General Responsive base
    fix_2c = """
/* Responsive base — applied to all transfer pages */
@media(max-width:600px) {
    /* Cards */
    .tl-card, .rf-card {
        border-radius:var(--rsm, 8px);
    }
    /* Tables inside cards — make them scroll horizontally */
    table {
        display:block;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        white-space:nowrap;
    }
    /* Prevent text overflow on narrow screens */
    .tl-num, .rf-prod-name, .tl-route-node {
        max-width:140px;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    /* Badges wrap instead of overflow */
    .tl-card-meta, .tl-dates {
        flex-wrap:wrap;
        gap:4px;
    }
}
"""

    # Append to <style> block if <style> exists and it's not already there
    # First check if the file contains a <style> block
    style_end_match = re.search(r'</style>', content)
    if style_end_match:
        to_inject = ""
        breakpoints_added = []
        if '/* 2C' not in content and '/* Responsive base' not in content:
            to_inject += fix_2c
            breakpoints_added.append('600px (base)')
            
        if ('tl-pipeline' in content or 'tl-card' in content) and ('/* 2A' not in content):
            to_inject += fix_2a
            breakpoints_added.append('900px, 600px (list)')
            
        if ('rf-layout' in content or 'rf-row2' in content) and ('/* 2B' not in content):
            to_inject += fix_2b
            breakpoints_added.append('860px, 600px (form)')
            
        if to_inject:
            content = content.replace('</style>', to_inject + '</style>')
            media_added = True
            files_changed_media[str(filepath)] = ", ".join(breakpoints_added)

    # Save if modified
    if content != original_content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
            
    if font_changes_count > 0:
        files_changed_fonts[str(filepath)] = font_changes_count

# Find all files
for root, dirs, files in os.walk(base_dir):
    for name in files:
        if name.endswith('.blade.php') and 'transfer' in str(Path(root) / name).lower():
            process_file(Path(root) / name)

# Write results
import json
with open('update_results.json', 'w') as f:
    json.dump({"fonts": files_changed_fonts, "media": files_changed_media}, f)
print("Done!")
