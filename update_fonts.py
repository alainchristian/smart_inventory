import os
import re

directory = r"c:\Users\Christian\Desktop\projects\smart-inventory\resources\views"
file_paths = []
for root, dirs, files in os.walk(directory):
    for str_file in files:
        if str_file.endswith(".blade.php") and "transfer" in os.path.join(root, str_file).lower():
            file_paths.append(os.path.join(root, str_file))

def process_font_size(match):
    prefix = match.group(1)
    val = int(match.group(2))
    new_val = round(val * 1.2)
    return f"{prefix}{new_val}px"

tw_map = {
    'text-xs': 'text-sm',
    'text-sm': 'text-base',
    'text-base': 'text-lg',
    'text-lg': 'text-xl',
    'text-xl': 'text-2xl'
}

def process_tw(match):
    return tw_map.get(match.group(0), match.group(0))

report = []
total_count = 0

for path in file_paths:
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()

    new_content, count1 = re.subn(r'(font-size:\s*)(\d+)px', process_font_size, content)
    new_content, count2 = re.subn(r'\btext-(xs|sm|base|lg|xl)\b', process_tw, new_content)
    
    count = count1 + count2
    if count > 0:
        with open(path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        report.append(f"- `{os.path.relpath(path, directory).replace(os.sep, '/')}`: {count} values updated (fonts: {count1}, tailwind: {count2})")
        total_count += count

with open(r"c:\Users\Christian\.gemini\antigravity\brain\47698b0b-11d0-44e4-8d7e-f3e169d25b2f\mission-1-fonts.md", "w", encoding='utf-8') as f:
    f.write("# Mission 1: Font Sizes\n\n")
    f.write(f"Total values updated: {total_count}\n\n")
    f.write("\n".join(report))
