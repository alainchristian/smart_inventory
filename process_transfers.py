import os
import re

directory = 'resources/views'
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
    '28px': '34px',
    '29px': '35px'
}
tw_map = {
    'text-xs': 'text-sm',
    'text-sm': 'text-base',
    'text-base': 'text-lg',
    'text-lg': 'text-xl',
    'text-xl': 'text-2xl',
}

files_changed = []
font_updates_per_file = {}

mission_2c = '''
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
'''

mission_2a = '''
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
'''

mission_2b = '''
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
'''

def replace_fonts(text):
    count = 0
    # Font sizes in px
    def rep_px(m):
        nonlocal count
        val = m.group(1) + 'px'
        if val in font_map:
            count += 1
            return 'font-size:' + font_map[val]
        return m.group(0)
    
    text = re.sub(r'font-size:\s*(\d+)px', rep_px, text)
    
    # Tailwind classes
    def rep_tw(m):
        nonlocal count
        val = m.group(0)
        if val in tw_map:
            count += 1
            return tw_map[val]
        return val
        
    text = re.sub(r'\\btext-(xs|sm|base|lg|xl)\\b', rep_tw, text)
    
    return text, count

for root, _, files in os.walk(directory):
    for file in files:
        if 'transfer' in file.lower() and file.endswith('.blade.php'):
            path = os.path.join(root, file)
            with open(path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            orig_content = content
            
            # Mission 1
            content, count = replace_fonts(content)
            
            # Mission 2
            # Check if we should insert media queries
            # Find </style>
            if '</style>' in content:
                # Add 2C if not there
                if 'Responsive base — applied to all transfer pages' not in content:
                    content = content.replace('</style>', mission_2c + '\\n</style>')
                
                # Check if it has .tl- or .rf-
                if '.tl-' in content and '2A - Transfer List Fixes' not in content:
                    content = content.replace('</style>', mission_2a + '\\n</style>')
                    
                if '.rf-' in content and '2B - Request Form Fixes' not in content:
                    content = content.replace('</style>', mission_2b + '\\n</style>')
            
            if content != orig_content:
                with open(path, 'w', encoding='utf-8') as f:
                    f.write(content)
                files_changed.append(path)
                font_updates_per_file[path] = count

print(f'Processed {len(files_changed)} files.')
for f in files_changed:
    print(f, ':', font_updates_per_file[f])
