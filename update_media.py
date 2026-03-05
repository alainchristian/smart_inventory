import os
import re

directory = r"c:\Users\Christian\Desktop\projects\smart-inventory\resources\views"
file_paths = []
for root, dirs, files in os.walk(directory):
    for str_file in files:
        if str_file.endswith(".blade.php") and "transfer" in os.path.join(root, str_file).lower():
            file_paths.append(os.path.join(root, str_file))

rule_2a = """
/* Mission 2A */
@media(max-width:900px) {
    .tl-pipeline { grid-template-columns: repeat(3, 1fr); }
}
@media(max-width:600px) {
    .tl-pipeline { grid-template-columns: repeat(2, 1fr); gap:0; }
    .tl-pipeline-step { padding:10px 12px; }
    .tl-step-num  { font-size:24px; } /* It was 20px in instructions, but we already scaled fonts. Let's just use what instruction said and it will scale? Actually let's use exact code */
    .tl-step-sub  { display:none; }
    
    .tl-card-top    { flex-direction:column; padding:0 14px; }
    .tl-card-stats  { border-left:none; border-top:1px solid var(--border);
                      margin:0 0 8px; flex-wrap:wrap; }
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
    .tl-page-header-left h1 { font-size:24px; }
    .tl-new-btn             { width:100%; justify-content:center; }
}
"""

rule_2b = """
/* Mission 2B */
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

rule_2c = """
/* Mission 2C: Responsive base — applied to all transfer pages */
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

report = []

for path in file_paths:
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()

    to_add = []
    
    if "tl-" in content:
        to_add.append(rule_2a)
    if "rf-" in content:
        to_add.append(rule_2b)
    
    to_add.append(rule_2c)
    
    add_str = "\n".join(to_add)
    
    # Avoid duplicate additions
    if "/* Mission 2C" not in content:
        # Find </style> tag
        if "</style>" in content:
            content = content.replace("</style>", add_str + "\n</style>")
        else:
            # prepend
            content = "<style>\n" + add_str + "\n</style>\n" + content
            
        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)
        report.append(f"- `{os.path.relpath(path, directory).replace(os.sep, '/')}`: Added media rules (2A: {'Yes' if 'tl-' in content else 'No'}, 2B: {'Yes' if 'rf-' in content else 'No'}, 2C: Yes)")

with open(r"c:\Users\Christian\.gemini\antigravity\brain\47698b0b-11d0-44e4-8d7e-f3e169d25b2f\mission-2-responsive.md", "w", encoding='utf-8') as f:
    f.write("# Mission 2: Responsiveness\n\n")
    f.write("\n".join(report))
