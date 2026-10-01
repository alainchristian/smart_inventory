{{-- Shared print styles for transfer documents. --}}
    <style>
        /* Transfer print documents (delivery note, picking list): own palette, no app CSS variables. */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; line-height: 1.45; color: #1a1f36; background: #fff; }
        .page { max-width: 800px; margin: 0 auto; padding: 32px 36px; }

        /* Header */
        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;
                      border-bottom: 2px solid #1a1f36; padding-bottom: 14px; margin-bottom: 18px; }
        .doc-header .brand { font-size: 22px; font-weight: 800; letter-spacing: -0.3px; }
        .doc-header .brand-sub { font-size: 12px; color: #7a81a0; margin-top: 2px; }
        .doc-header .doc-type { text-align: right; }
        .doc-header .doc-type .title { font-size: 18px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        .doc-header .doc-type .number { font-size: 14px; color: #4a5372; margin-top: 2px; font-family: Consolas, monospace; font-weight: 700; }
        .doc-header .doc-type .status-badge { display: inline-block; margin-top: 6px; padding: 2px 9px; border-radius: 6px; font-size: 11px;
                      font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase; background: #e7f6f3; color: #0e9e86; }

        /* Meta row */
        .meta-row { display: flex; gap: 22px; flex-wrap: wrap; padding: 10px 14px; border: 1px solid #e2e6f3; border-radius: 8px; margin-bottom: 16px; }
        .meta-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #7a81a0; }
        .meta-value { font-size: 13px; font-weight: 600; margin-top: 2px; }

        /* Cards */
        .info-grid, .transporter-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
        .info-card { border: 1px solid #e2e6f3; border-radius: 8px; }
        .info-card .card-head { padding: 7px 12px; font-size: 10px; font-weight: 700; letter-spacing: 0.7px; text-transform: uppercase;
                                color: #7a81a0; border-bottom: 1px solid #e2e6f3; }
        .info-card .card-body { padding: 10px 12px; }
        .info-card .location-name { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
        .info-card .location-detail { font-size: 12px; color: #4a5372; }

        /* Sections + tables */
        .section-heading { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: #4a5372;
                           padding-bottom: 6px; margin: 4px 0 8px; border-bottom: 1px solid #e2e6f3; }
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; margin-bottom: 18px; }
        thead th { padding: 7px 10px; text-align: left; font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
                   color: #7a81a0; border-bottom: 2px solid #1a1f36; }
        thead th:last-child { text-align: right; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #eceff7; vertical-align: top; }
        tbody td:last-child { text-align: right; }
        tfoot td { padding: 8px 10px; font-weight: 700; border-top: 2px solid #1a1f36; }
        tfoot td:last-child { text-align: right; }
        .status-full    { color: #0e9e86; font-weight: 600; }
        .status-partial { color: #7c3aed; font-weight: 600; }
        .status-damaged { color: #e11d48; font-weight: 600; }

        /* Notes */
        .notes-box { border: 1px solid #e2e6f3; border-radius: 8px; padding: 10px 12px; min-height: 48px; font-size: 12.5px; color: #1a1f36; margin-bottom: 18px; }
        .notes-box.empty { color: #7a81a0; font-style: italic; }

        /* Signatures */
        .sig-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 22px; }
        .sig-line { border-bottom: 1.5px solid #1a1f36; height: 40px; margin-bottom: 6px; }
        .sig-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #4a5372; }
        .sig-name  { font-size: 12px; color: #7a81a0; margin-top: 2px; }

        /* Footer + print button */
        .doc-footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #e2e6f3; text-align: center; font-size: 11px; color: #7a81a0; }
        .print-btn { display: block; margin: 18px auto 0; padding: 9px 22px; background: #3b6fd4; color: #fff; border: none; border-radius: 8px;
                     font-size: 13px; font-weight: 600; cursor: pointer; }
        .print-btn:hover { opacity: .88; }

        .check-box { width:16px;height:16px;border:1.5px solid #1a1f36;border-radius:3px;display:inline-block }
        .codes { font-family:Consolas, monospace;font-size:11.5px;color:#4a5372;white-space:normal;line-height:1.6 }
        @media print {
            .page { padding: 0; }
            .no-print { display: none !important; }
        }
        @media (max-width: 600px) {
            .page { padding: 16px; }
            .doc-header { flex-direction: column; }
            .doc-header .doc-type { text-align: left; }
            .info-grid, .transporter-row, .sig-row { grid-template-columns: 1fr; }
            .table-scroll { overflow-x: auto; }
            .table-scroll table { min-width: 520px; }
        }
    </style>
