import os
import re

files_to_check = [
    r"resources\views\components\inventory\transfers\⚡receive-transfer.blade.php",
    r"resources\views\components\inventory\transfers\⚡request-transfer.blade.php",
    r"resources\views\components\inventory\transfers\⚡transfer-list.blade.php",
    r"resources\views\components\shop\transfers\request.blade.php",
    r"resources\views\livewire\dashboard\transfer-status.blade.php",
    r"resources\views\livewire\inventory\transfers\pack-transfer.blade.php",
    r"resources\views\livewire\inventory\transfers\request-transfer.blade.php",
    r"resources\views\livewire\owner\reports\transfer-performance.blade.php",
    r"resources\views\livewire\shop\transfers\receive-transfer.blade.php",
    r"resources\views\livewire\shop\transfers\transfers-list.blade.php",
    r"resources\views\livewire\shop\transfers\view-transfer.blade.php",
    r"resources\views\livewire\warehouse-manager\transfers\pack-transfer.blade.php",
    r"resources\views\livewire\warehouse-manager\transfers\review-transfer.blade.php",
    r"resources\views\livewire\warehouse-manager\transfers\transfers-list.blade.php",
    r"resources\views\owner\reports\transfers.blade.php",
    r"resources\views\shop\transfers\index.blade.php",
    r"resources\views\shop\transfers\receive.blade.php",
    r"resources\views\shop\transfers\request.blade.php",
    r"resources\views\shop\transfers\show.blade.php",
    r"resources\views\warehouse\transfers\index.blade.php",
    r"resources\views\warehouse\transfers\pack.blade.php",
    r"resources\views\warehouse\transfers\show.blade.php"
]

base_dir = r"c:\Users\Christian\Desktop\projects\smart-inventory"

font_map = {
    '9px': '11px',
    '10px': '12px',
    '11px': '13px',
    '12px': '14px',
    '13px': '16px',
    '14px': '17px',
    '15px': '18px',
    '16px': '19px',
    '18px': '22px',
    '20px': '24px',
    '22px': '26px',
    '24px': '29px',
    '26px': '31px',
    '28px': '34px'
}

def replace_font_sizes(match):
    original = match.group(0)
    num_match = re.search(r'(\d+px)', original)
    if num_match:
        px_val = num_match.group(1)
        if px_val in font_map:
            return original.replace(px_val, font_map[px_val])
        else:
            val = int(px_val.replace('px', ''))
            new_val = round(val * 1.2)
            return original.replace(px_val, f"{new_val}px")
    return original

tw_map = {
    'text-xs': 'text-sm',
    'text-sm': 'text-base',
    'text-base': 'text-lg',
    'text-lg': 'text-xl',
    'text-xl': 'text-2xl'
}

def replace_tw(match):
    cls = match.group(0)
    return tw_map.get(cls, cls)

report_lines = ["# Mission 1: Font Sizes Changed Files"]

total_files_changed = 0

for file_path in files_to_check:
    full_path = os.path.join(base_dir, file_path)
    if not os.path.exists(full_path):
        continue
    
    with open(full_path, 'r', encoding='utf-8') as f:
        content = f.read()

    changes = 0

    new_content, n_sizes = re.subn(r'font-size\s*:\s*\d+px', replace_font_sizes, content)
    changes += n_sizes

    # Replace classes in a single pass to avoid double-replacing
    # Look for the exact class names with boundaries
    pattern = r'(?<![a-zA-Z0-9_-])(?:' + '|'.join(re.escape(k) for k in tw_map.keys()) + r')(?![a-zA-Z0-9_-])'
    new_content, n_tw = re.subn(pattern, replace_tw, new_content)
    changes += n_tw

    if changes > 0:
        with open(full_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        total_files_changed += 1
        basename = os.path.basename(file_path)
        report_lines.append(f"- `{basename}`: {changes} values updated")

artifact_path = r"C:\Users\Christian\.gemini\antigravity\brain\72e4f73c-4b66-49e7-9d26-dc646555bdc4\mission-1-fonts.md"
with open(artifact_path, 'w', encoding='utf-8') as rf:
    rf.write("\n".join(report_lines))

print(f"Updated {total_files_changed} files. Wrote artifact.")
