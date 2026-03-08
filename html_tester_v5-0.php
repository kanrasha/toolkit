
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Live HTML Previewer</title>
  <style>
    :root {
      --bg: #0a0a0f;
      --bg-elevated: #12121a;
      --fg: #e4e4e7;
      --muted: #71717a;
      --accent: #22d3ee;
      --accent-dim: rgba(34, 211, 238, 0.15);
      --border: #27272a;
      --success: #4ade80;
      --phone-frame: #1f1f28;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    html, body { height: 100%; overflow: hidden; }

    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: var(--bg);
      color: var(--fg);
    }

    #app { display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 16px;
      border-bottom: 1px solid var(--border);
      background: var(--bg-elevated);
      flex-shrink: 0;
      gap: 12px;
      height: 52px;
    }

    .header-left { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
    .logo { display: flex; align-items: center; gap: 8px; }
    .logo svg { color: var(--accent); }
    .logo-text { font-weight: 600; font-size: 14px; letter-spacing: -0.02em; }

    .status { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--muted); }
    .status-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--success); animation: pulse 2s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

    .header-right { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 6px 10px;
      font-size: 12px;
      font-weight: 500;
      color: var(--fg);
      background: transparent;
      border: 1px solid var(--border);
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.15s ease;
      white-space: nowrap;
    }
    .btn:hover { background: var(--accent-dim); border-color: var(--accent); color: var(--accent); }
    .btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
    .btn.active { background: var(--accent); border-color: var(--accent); color: var(--bg); }
    .btn svg { width: 14px; height: 14px; flex-shrink: 0; }

    .btn-text { display: none; }
    @media (min-width: 900px) { .btn-text { display: inline; } }

    .divider { width: 1px; height: 20px; background: var(--border); margin: 0 4px; }

    /* RESTORED: Dynamic Layout Logic */
    main { display: flex; flex: 1; min-height: 0; overflow: hidden; }
    main.vertical { flex-direction: row; }
    main.horizontal { flex-direction: column; }

    .editor-panel, .preview-panel { display: flex; flex-direction: column; min-width: 0; min-height: 0; overflow: hidden; }

    /* Border logic based on layout */
    main.vertical .editor-panel { border-right: 1px solid var(--border); }
    main.horizontal .editor-panel { border-bottom: 1px solid var(--border); }

    .panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 12px;
      border-bottom: 1px solid var(--border);
      background: var(--bg-elevated);
      flex-shrink: 0;
      height: 36px;
    }
    .panel-title { font-size: 11px; font-weight: 600; color: var(--muted); letter-spacing: 0.05em; text-transform: uppercase; }
    .char-count { font-size: 11px; color: var(--muted); }

    .editor-toolbar {
      display: flex;
      align-items: center;
      gap: 4px;
      padding: 6px 12px;
      background: var(--bg);
      border-bottom: 1px solid var(--border);
    }

    .tool-btn {
      background: transparent;
      border: 1px solid transparent;
      color: var(--muted);
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 11px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;
      transition: all 0.15s;
    }
    .tool-btn:hover { background: rgba(255,255,255,0.1); color: var(--fg); }
    .tool-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .tool-btn svg { width: 12px; height: 12px; }
    .tool-btn.active { background: var(--accent); color: var(--bg); }
    .tool-btn.offline { color: #f97316; border-color: #f97316; background: transparent; }

    .editor-content {
      flex: 1;
      min-height: 0;
      overflow: hidden;
      position: relative;
      background: var(--bg);
      font-family: "SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, monospace;
      font-size: 13px;
      line-height: 1.6;
      tab-size: 4;
      white-space: pre-wrap;
      word-wrap: break-word;
    }

    .api-input {
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: 4px;
      padding: 4px 8px;
      font-size: 11px;
      color: var(--fg);
      outline: none;
      width: 120px;
      transition: border-color 0.2s;
    }
    .api-input:focus { border-color: var(--accent); }

    .code-input-area {
      position: absolute;
      top: 0; left: 0; width: 100%; height: 100%;
      padding: 16px;
      margin: 0; border: none;
      font-family: inherit;
      font-size: inherit;
      line-height: 1.6;
      tab-size: inherit;
      white-space: pre;
      overflow: auto;
      overscroll-behavior: contain;
    }

    .highlight-layer {
      z-index: 2;
      color: var(--fg);
      pointer-events: none;
      word-wrap: normal;
      display: block;
      overflow: hidden;
    }

    .code-editor {
      z-index: 3;
      color: transparent;
      background: transparent;
      caret-color: var(--accent);
      resize: none;
      outline: none;
      -webkit-text-fill-color: transparent;
    }

    .code-editor::selection {
      background: rgba(34, 211, 238, 0.3);
      -webkit-text-fill-color: var(--fg);
    }

    /* Syntax Colors */
    .hl-tag { color: #569cd6; }
    .hl-attr { color: #9cdcfe; }
    .hl-string { color: #ce9178; }
    .hl-comment { color: #6a9955; font-style: italic; opacity: 0.8; }
    .hl-keyword { color: #c586c0; }
    .hl-punctuation { color: #808080; }
    .hl-css-prop { color: #dcdcaa; }

    /* RESTORED: Dynamic Resizer Logic */
    .resizer {
      flex-shrink: 0;
      background: var(--border);
      transition: background 0.15s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    main.vertical .resizer {
      width: 6px;
      height: 36px;
      align-self: flex-start;
      cursor: col-resize;
      border-radius: 0 0 3px 3px;
    }
    main.horizontal .resizer {
      height: 4px;
      width: 100%;
      cursor: row-resize;
    }
    .resizer:hover, .resizer.dragging { background: var(--accent); }

    .preview-panel { flex: 1; }

    .preview-content {
      flex: 1; min-height: 0; overflow: hidden; position: relative; background: var(--bg);
      touch-action: pan-x pan-y;
      overscroll-behavior: contain;
    }

    .preview-single { position: absolute; top: 0; left: 0; right: 0; bottom: 0; }
    .preview-single iframe {
      width: 100%; height: 100%; border: none; background: white;
      overscroll-behavior: contain;
    }

    .open-tab-btn { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: var(--accent); background: none; border: none; cursor: pointer; }
    .open-tab-btn:hover { text-decoration: underline; }
    .open-tab-btn svg { width: 12px; height: 12px; }

    /* AI Panel Styles */
    #ai-panel {
      position: fixed;
      top: 0;
      right: 0;
      width: 33%;
      min-width: 300px;
      height: 100%;
      background: var(--bg-elevated);
      border-left: 1px solid var(--border);
      z-index: 9999;
      display: flex;
      flex-direction: column;
      transform: translateX(100%);
      transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: -4px 0 15px rgba(0,0,0,0.3);
    }

    #ai-panel.open { transform: translateX(0); }

    .ai-header {
      display: flex;
      flex-direction: column;
      padding: 12px 16px;
      border-bottom: 1px solid var(--border);
      background: var(--bg);
      flex-shrink: 0;
      gap: 12px;
    }

    .ai-header-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
    }

    .ai-title {
      font-size: 12px;
      font-weight: 600;
      color: var(--fg);
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .ai-title svg { color: var(--accent); }

    .ai-close {
      background: none;  /*testing-delete*/
      border: none;
      color: var(--muted);
      cursor: pointer;
      padding: 4px;
      display: flex;
    }
    .ai-close:hover { color: var(--fg); }

    .ai-settings {
      padding: 8px 12px;
      background: var(--bg);
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .setting-input {
      background: var(--bg-elevated);
      border: 1px solid var(--border);
      border-radius: 4px;
      padding: 4px 8px;
      font-size: 11px;
      color: var(--fg);
      outline: none;
      flex: 1 1 auto;
      width: 24ch;
      min-width: 6ch;
    }

    .ai-output {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      font-family: "SF Mono", Monaco, "Cascadia Code", monospace;
      font-size: 12px;
      color: var(--muted);
      white-space: pre-wrap;
      background: var(--bg);
    }

    .ai-output strong { color: var(--accent); font-weight: 600; }
    .ai-output .code-snippet {
      margin-top: 8px;
      padding: 8px;
      background: rgba(0,0,0,0.2);
      border-radius: 4px;
      border-left: 2px solid var(--accent);
    }

    .ai-input-area {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
      border-top: 1px solid var(--border);
      background: var(--bg);
      padding: 12px;
      min-height:100px;
      max-height: 10rem;
      overflow-y: auto;
    }

    .ai-input-area textarea {
      width: 100%;
      height: 100%;
      flex: 1;
      min-height: 24px;
      background: transparent;
      border: none;
      color: var(--fg);
      font-size: 12px;
      font-family: inherit;
      resize: none;
      outline: none;
    }

    .ai-input-cfg {
      top:0;
      background:transparent;
      font-size: 0.8rem;
      display: flex;
      /* align-items: left; */
      justify-content:space-between;
      margin:1px;
      margin-left:5px;
    }

    .context-toggle {
      display: flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      font-size: 11px;
      color: var(--muted);
      user-select: none;
    }
    .context-toggle input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }
    .checkmark {
      height: 14px;
      width: 14px;
      background-color: var(--bg);
      border: 1px solid var(--border);
      border-radius: 50%;
      transition: all 0.15s;
    }
    .context-toggle:hover .checkmark { border-color: var(--accent); }
    .context-toggle input:checked + .checkmark { background-color: var(--accent); border-color: var(--accent); }
    .context-toggle input:checked ~ span:last-child { color: var(--fg); }

    .ai-input-area textarea::placeholder { color: var(--muted); }
    .ai-send-btn { flex-shrink: 0; white-space: nowrap; }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
    }
  </style>
</head>
<body>
  <div id="app">
    <header>
      <div class="header-left">
        <div class="logo">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="16 18 22 12 16 6"></polyline>
            <polyline points="8 6 2 12 8 18"></polyline>
          </svg>
          <span class="logo-text">HTML Live</span>
        </div>
        <div class="status">
          <div class="status-dot"></div>
          <span id="status-text">Ready</span>
        </div>
      </div>

      <div class="header-right">
        <button id="btn-run" class="btn active" title="Auto-run enabled">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
          <span class="btn-text">Auto</span>
        </button>
        <button id="btn-refresh" class="btn" title="Run preview">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
          <span class="btn-text">Run</span>
        </button>

        <!-- MERGED: Layout Button from {A} -->
        <button id="btn-layout" class="btn" title="Toggle layout orientation">
          <svg id="icon-layout-h" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="12" y1="3" x2="12" y2="21"></line></svg>
          <svg id="icon-layout-v" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="12" x2="21" y2="12"></line></svg>
          <span class="btn-text">Layout</span>
        </button>

        <button id="btn-clear" class="btn" title="Clear code">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
        </button>
        <button id="btn-reset" class="btn" title="Reset All Settings & Code">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.5 2v6h6M21.5 22v-6h-6"/><path d="M22 11.5A10 10 0 0 0 3.2 7.2M2 12.5a10 10 0 0 0 18.8 4.2"/></svg>
          <span class="btn-text">Reset</span>
        </button>

        <button id="btn-export" class="btn" title="Export HTML">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
          <span class="btn-text">Export</span>
        </button>
      </div>
    </header>

    <!-- MERGED: Default class set to vertical -->
    <main id="main-content" class="vertical">
      <section class="editor-panel">
        <div class="panel-header">
          <span class="panel-title">Editor</span>
          <span id="char-count" class="char-count">0 chars</span>
        </div>
        <div class="editor-toolbar">
            <button id="btn-undo" class="tool-btn" title="Undo" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v6h6"></path><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"></path></svg>
                Undo
            </button>
            <button id="btn-redo" class="tool-btn" title="Redo" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 7v6h-6"></path><path d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3l3 2.7"></path></svg>
                Redo
            </button>
            <div class="divider"></div>
            <button id="btn-copy" class="tool-btn" title="Copy Source">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy
            </button>
            <button id="btn-format" class="tool-btn" title="Format Code">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="10" x2="3" y2="10"></line><line x1="21" y1="6" x2="3" y2="6"></line><line x1="21" y1="14" x2="3" y2="14"></line><line x1="21" y1="18" x2="3" y2="18"></line></svg>
                Format
            </button>
             <button id="btn-ai" class="tool-btn" title="Toggle AI Pane">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2M7.5 13A2.5 2.5 0 0 0 5 15.5 2.5 2.5 0 0 0 7.5 18a2.5 2.5 0 0 0 2.5-2.5A2.5 2.5 0 0 0 7.5 13m9 0a2.5 2.5 0 0 0-2.5 2.5 2.5 2.5 0 0 0 2.5 2.5 2.5 2.5 0 0 0 2.5-2.5 2.5 2.5 0 0 0-2.5-2.5Z"/></svg>
                <span id="btn-ai-text">AI</span>
            </button>
        </div>
        <div class="editor-content">
          <pre class="code-input-area highlight-layer" id="highlight-layer"></pre>
          <textarea id="code-editor" class="code-input-area code-editor" placeholder="<!-- Start typing HTML here... -->" spellcheck="false" aria-label="HTML code editor"></textarea>
        </div>
      </section>

      <div id="resizer" class="resizer"></div>

      <section class="preview-panel">
        <div class="panel-header">
          <span class="panel-title">Preview</span>
          <button id="btn-new-tab" class="open-tab-btn">
            Open in tab
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
          </button>
        </div>
        <div class="preview-content">
          <div id="preview-single" class="preview-single">
            <iframe id="main-preview" sandbox="allow-scripts" title="HTML preview"></iframe>
          </div>
        </div>
      </section>
    </main>

    <!-- AI Panel -->
    <div id="ai-panel">
      <div class="ai-header">
        <div class="ai-header-row">
          <div class="ai-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2l2.4 7.2H22l-6 4.8 2.4 7.2L12 16.4 5.6 21.2 8 14 2 9.2h7.6z"/>
            </svg>
              AI Assistant
          </div>
          <button id="ai-settings-toggle" class="ai-close" title="Settings">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
          </button>
          <button id="ai-close" class="ai-close" title="Close Panel">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </button>
        </div>
          <div id="ai-settings" class="ai-settings" style="display:none;">
            <input id="api-model-input" class="setting-input" type="text" placeholder="Model Name">
            <input id="api-key-input" class="setting-input" type="password" placeholder="API Key">
            <input id="api-endpoint-input" class="setting-input" type="text" placeholder="Endpoint">
          </div>
      </div>
      <div class="ai-output" id="ai-output"></div>
      <div class="ai-input-cfg">
        <!-- <button class="cfg-btn" id="cfg-tokens" title="Token Settings">test</button> -->
        <label class="context-toggle" title="Send full code for context">
          <input type="checkbox" id="context-toggle">
          <span class="checkmark"></span>
            Context
        </label>
        <button id="ai-clear-chat" class="ai-close" title="Clear Chat History">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
        </button>
      </div>
      <div class="ai-input-area">
        <textarea id="ai-input" placeholder="Ask AI about selection..." rows="1"></textarea>
        <button id="ai-send" class="btn ai-send-btn" title="Send prompt">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
        </button>
        <!-- <textarea id="ai-input" placeholder="Ask AI about selection..." rows="1"></textarea> -->
      </div>
    </div>
  </div>

  <script>
    // State
    let autoRun = localStorage.getItem('html-live-auto') !== 'false';
    let blobUrl = null;
    let aiPanelOpen = false;

    // MERGED: State for layout
    let isvertical = true;

    // Performance Timers
    let previewDebounceTimer = null;
    let highlightRAF = null;
    let saveTimer = null;

    // Undo/Redo State
    let history = [];
    let historyIndex = -1;
    const MAX_HISTORY = 50;
    let isUndoRedo = false;

    // DOM Elements
    const codeEditor = document.getElementById('code-editor');
    const highlightLayer = document.getElementById('highlight-layer');
    const mainPreview = document.getElementById('main-preview');
    const previewSingle = document.getElementById('preview-single');
    const charCount = document.getElementById('char-count');
    const statusText = document.getElementById('status-text');
    const resizer = document.getElementById('resizer');
    const mainContent = document.getElementById('main-content');
    const editorPanel = document.querySelector('.editor-panel');

    // MERGED: Layout Icons
    const iconLayoutH = document.getElementById('icon-layout-h');
    const iconLayoutV = document.getElementById('icon-layout-v');

    // AI Elements
    const btnAI = document.getElementById('btn-ai');
    const aiPanel = document.getElementById('ai-panel');
    const aiClose = document.getElementById('ai-close');
    const aiOutput = document.getElementById('ai-output');
    const aiInput = document.getElementById('ai-input');
    const apiKeyInput = document.getElementById('api-key-input');
    const apiEndpointInput = document.getElementById('api-endpoint-input');
    const apiModelInput = document.getElementById('api-model-input');
    const contextToggle = document.getElementById('context-toggle');
    const aiSettingsToggle = document.getElementById('ai-settings-toggle');
    const aiSettings = document.getElementById('ai-settings');

    // Buttons
    const btnRun = document.getElementById('btn-run');
    const btnRefresh = document.getElementById('btn-refresh');
    const btnLayout = document.getElementById('btn-layout'); // MERGED
    const btnClear = document.getElementById('btn-clear');
    const btnReset = document.getElementById('btn-reset');
    const btnExport = document.getElementById('btn-export');
    const btnNewTab = document.getElementById('btn-new-tab');

    // Toggle Settings
    aiSettingsToggle.addEventListener('click', () => {
        const isVisible = aiSettings.style.display === 'flex';
        aiSettings.style.display = isVisible ? 'none' : 'flex';
    });

    // Load Saved API Settings
    apiKeyInput.value = localStorage.getItem('ai-api-key') || '';
    apiEndpointInput.value = localStorage.getItem('ai-api-endpoint') || '';
    apiModelInput.value = localStorage.getItem('ai-api-model') || 'zai-org/GLM-5';

    // Save on change
    apiKeyInput.addEventListener('change', () => localStorage.setItem('ai-api-key', apiKeyInput.value));
    apiEndpointInput.addEventListener('change', () => localStorage.setItem('ai-api-endpoint', apiEndpointInput.value));
    apiModelInput.addEventListener('change', () => localStorage.setItem('ai-api-model', apiModelInput.value));

    // Editor Toolbar Buttons
    const btnCopy = document.getElementById('btn-copy');
    const btnFormat = document.getElementById('btn-format');
    const btnUndo = document.getElementById('btn-undo');
    const btnRedo = document.getElementById('btn-redo');

    // --- AI Logic ---
    let isOfflineMode = false;

    window.addEventListener('online', () => {
      if (isOfflineMode) {
        isOfflineMode = false;
        document.getElementById('btn-ai-text').textContent = 'AI';
        btnAI.classList.remove('offline');
        statusText.textContent = 'Back Online';
        setTimeout(() => statusText.textContent = 'Ready', 1500);
      }
    });

    async function checkConnectivity() {
      if (!navigator.onLine) return false;
      try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 1500);
        await fetch('https://www.google.com/generate_204', {
          method: 'HEAD', mode: 'no-cors', cache: 'no-store', signal: controller.signal
        });
        clearTimeout(timeoutId);
        return true;
      } catch (error) {
        return false;
      }
    }

    function toggleAIPanel() {
      if (aiPanelOpen) {
        aiPanelOpen = false;
        aiPanel.classList.remove('open');
        return;
      }
      if (isOfflineMode) {
         isOfflineMode = false;
         btnAI.classList.remove('offline');
         document.getElementById('btn-ai-text').textContent = 'AI';
         statusText.textContent = 'Ready';
       }
      let determinedOnline = false;
      const bufferTimeout = setTimeout(() => {
        if (!determinedOnline) {
           isOfflineMode = true;
           document.getElementById('btn-ai-text').textContent = 'Offline';
           btnAI.classList.add('offline');
           statusText.textContent = 'No Connection';
        }
      }, 150);

      checkConnectivity().then(isOnline => {
        determinedOnline = true;
        clearTimeout(bufferTimeout);
        if (isOnline) {
          aiPanelOpen = !aiPanelOpen;
          if (aiPanelOpen) {
            aiPanel.classList.add('open');
            updateAIContext();
          } else {
            aiPanel.classList.remove('open');
          }
        }
      });
    }

    function updateAIContext() {
      if (!aiPanelOpen) return;
        const start = codeEditor.selectionStart;
        const end = codeEditor.selectionEnd;
        const val = codeEditor.value;
        const startLine = val.substring(0, start).split('\n').length;
        const endLine = val.substring(0, end).split('\n').length;
        const selectedText = val.substring(start, end);

      if (selectedText.length === 0) {
        aiOutput.innerHTML = `<div style="color: var(--muted); font-style: italic;">Select code in the editor to see context here.</div>`;
        return;
      }

      const limit = 100;
      let snippet = "";
      if (selectedText.length <= (limit * 2)) {
        snippet = selectedText;
      } else {
        const first = selectedText.substring(0, limit);
        const last = selectedText.substring(selectedText.length - limit);
        snippet = `${first}\n\n... [${selectedText.length - (limit * 2)} chars omitted] ...\n\n${last}`;
      }

      aiOutput.innerHTML = `
        <div>
          <strong>You have selected lines ${startLine}-${endLine}:</strong>
          <div class="code-snippet">${escapeHtml(snippet)}</div>
        </div>
      `;
    }

    function escapeHtml(text) {
      return text.replace(/[&<>"']/g, function(m) {
        switch (m) {
          case '&': return '&amp;';
          case '<': return '&lt;';
          case '>': return '&gt;';
          case '"': return '&quot;';
          case "'": return '&#039;';
        }
      });
    }

    btnAI.addEventListener('click', toggleAIPanel);
    aiClose.addEventListener('click', toggleAIPanel);

    const btnAiSend = document.getElementById('ai-send');

    // Clear Chat History Button
    document.getElementById('ai-clear-chat').addEventListener('click', () => {
        conversationHistory = [];
        aiOutput.innerHTML = `<div style="color: var(--muted); font-style: italic;">Chat cleared. Context reset.</div>`;
        statusText.textContent = 'Context Cleared';
        setTimeout(() => statusText.textContent = 'Ready', 1500);
    });

    async function sendAIRequest() {
        const apiKey = apiKeyInput.value.trim();
        const userMessage = aiInput.value.trim();
        // Default to OpenRouter/OpenAI endpoint if empty
        const endpoint = apiEndpointInput.value.trim() || 'https://openrouter.ai/api/v1/chat/completions';

        if (!apiKey) {
            aiOutput.innerHTML = `<div style="color:#f97316;">Error: API Key missing. Enter it in the toolbar.</div>`;
            return;
        }
        if (!userMessage) return;

        // --- 1. Prepare Content ---
        const selStart = codeEditor.selectionStart;
        const selEnd = codeEditor.selectionEnd;
        const selection = codeEditor.value.substring(selStart, selEnd);

        let content = userMessage;
        if (selection) content += `\n\nUser code reference selection:\n${selection}`;
        if (contextToggle.checked) content += `\n\nFull Code Context:\n${codeEditor.value}`;

        // --- 2. Determine Body Format ---
        // LOGIC UPDATE: Treat 'router.huggingface.co' as OpenAI Compatible
        const isLegacyHuggingFace = endpoint.includes('api-inference.huggingface.co');
        let body;

        if (isLegacyHuggingFace) {
            // Old Hugging Face Legacy Format
            body = JSON.stringify({ inputs: content, parameters: { max_new_tokens: 500, return_full_text: false } });
        } else {
            // OpenAI / OpenRouter / New HF Router Format
            const model = apiModelInput.value.trim() || 'default-model';
            body = JSON.stringify({
                model: model,
                messages: [
                    { role: 'system', content: 'You are a helpful coding assistant.' },
                    { role: 'user', content: content }
                ]
            });
        }

        // --- 3. Update UI (Show "Thinking...") ---
        aiOutput.innerHTML += `<div style="margin-top:8px;"><strong style="color:var(--fg);">You:</strong> ${escapeHtml(userMessage)}</div>`;
        aiInput.value = '';
        const loadingId = 'load-' + Date.now();
        aiOutput.innerHTML += `<div id="${loadingId}" style="color:var(--muted); font-style:italic;">Thinking...</div>`;
        aiOutput.scrollTop = aiOutput.scrollHeight;

        // --- 4. Send Request ---
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${apiKey}`
                },
                body: body
            });

            const data = await response.json();

            // Handle Model Loading State (Common in HF)
            if (data.error && data.error === 'Model is currently loading') {
                 document.getElementById(loadingId).remove();
                 aiOutput.innerHTML += `<div style="color:#f97316;">Model is loading... wait ${data.estimated_time || 20}s and try again.</div>`;
                 return;
            }

            // --- 5. Process Response ---
            // We can now safely remove the loader since we have a response
            const loader = document.getElementById(loadingId);
            if (loader) loader.remove();

            let reply = "";

            // Parse OpenAI/OpenRouter format
            if (data.choices && data.choices[0]) {
                reply = data.choices[0].message.content;
            }
            // Parse Legacy HF format (array)
            else if (Array.isArray(data) && data[0] && data[0].generated_text) {
                reply = data[0].generated_text;
            }
            // Fallback (Raw JSON or Error)
            else {
                reply = JSON.stringify(data);
            }

            if (reply) {
                aiOutput.innerHTML += `<div style="margin-top:8px;"><strong style="color:var(--accent);">AI:</strong> ${escapeHtml(reply)}</div>`;
            } else {
                aiOutput.innerHTML += `<div style="color:#ef4444;">Error: ${JSON.stringify(data.error || data)}</div>`;
            }
        } catch (err) {
            // Safety check: only remove if it exists
            const loader = document.getElementById(loadingId);
            if (loader) loader.remove();

            console.error("AI Request Error:", err);
            let errMsg = err.message;
            if (err.message === 'Failed to fetch') {
                errMsg = "Network Error. Check your Endpoint URL and Internet connection.";
            }
            aiOutput.innerHTML += `<div style="color:#ef4444;">Request Failed: ${errMsg}</div>`;
        }
        aiOutput.scrollTop = aiOutput.scrollHeight;
    }

    btnAiSend.addEventListener('click', sendAIRequest);
    aiInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendAIRequest();
        }
    });

    codeEditor.addEventListener('mouseup', updateAIContext);
    codeEditor.addEventListener('keyup', updateAIContext);

    // --- Undo/Redo Logic ---
    function pushHistory() {
      if (isUndoRedo) return;
      const val = codeEditor.value;
      const pos = codeEditor.selectionStart;
      if (historyIndex < history.length - 1) history = history.slice(0, historyIndex + 1);
      history.push({ val, pos });
      if (history.length > MAX_HISTORY) history.shift();
      else historyIndex++;
      updateUndoRedoButtons();
    }

    function updateUndoRedoButtons() {
      btnUndo.disabled = historyIndex <= 0;
      btnRedo.disabled = historyIndex >= history.length - 1;
    }

    function undo() {
      if (historyIndex > 0) {
        isUndoRedo = true;
        historyIndex--;
        const state = history[historyIndex];
        codeEditor.value = state.val;
        codeEditor.selectionStart = codeEditor.selectionEnd = state.pos;
        triggerUpdate();
        isUndoRedo = false;
        updateUndoRedoButtons();
      }
    }

    function redo() {
      if (historyIndex < history.length - 1) {
        isUndoRedo = true;
        historyIndex++;
        const state = history[historyIndex];
        codeEditor.value = state.val;
        codeEditor.selectionStart = codeEditor.selectionEnd = state.pos;
        triggerUpdate();
        isUndoRedo = false;
        updateUndoRedoButtons();
      }
    }

    // --- Code Editor Logic ---
    function applySyntaxHighlighting(code) {
      let html = escapeHtml(code);
      const strings = [];
      const comments = [];
      const declarations = [];

      html = html.replace(/(&lt;!--[\s\S]*?--&gt;)/g, (match) => `___COMM${comments.push(match) - 1}___`);
      html = html.replace(/(&lt;![\s\S]*?&gt;)/g, (match) => `___DECL${declarations.push(match) - 1}___`);
      html = html.replace(/(\/\*[\s\S]*?\*\/)/g, (match) => `___COMM${comments.push(match) - 1}___`);
      html = html.replace(/(\n\s*\/\/.*$)/gm, (match) => `___COMM${comments.push(match) - 1}___`);
      html = html.replace(/(&quot;[\s\S]*?&quot;|&#039;[\s\S]*?&#039;)/g, (match) => `___STR${strings.push(match) - 1}___`);  // resists catching '<' and '>' from & catch

      html = html.replace(/(\s)([\w-]+)(=)/g, '$1<span class="hl-attr">$2</span>$3');
      html = html.replace(/(&lt;\/?)([\w-]+)/g, '<span class="hl-tag">$1$2</span>');
      html = html.replace(/(\/?&gt;)/g, '<span class="hl-tag">$1</span>');
      html = html.replace(/([\w-]+)(?=\s*:)/g, '<span class="hl-css-prop">$1</span>');

      const keywords = ['function', 'const', 'let', 'var', 'return', 'if', 'else', 'for', 'while', 'new', 'document', 'window', 'console', 'log'];
      keywords.forEach(kw => {
        const regex = new RegExp(`\\b(${kw})\\b`, 'g');
        html = html.replace(regex, '<span class="hl-keyword">$1</span>');
      });

      declarations.forEach((decl, i) => html = html.replace(`___DECL${i}___`, decl));
      strings.forEach((str, i) => html = html.replace(`___STR${i}___`, `<span class="hl-string">${str}</span>`));
      comments.forEach((comm, i) => html = html.replace(`___COMM${i}___`, `<span class="hl-comment">${comm}</span>`));

      return html + '\n';
    }

    function updateHighlight() {
      const code = codeEditor.value;
      highlightLayer.innerHTML = applySyntaxHighlighting(code);
    }

    function syncScroll() {
      highlightLayer.scrollTop = codeEditor.scrollTop;
      highlightLayer.scrollLeft = codeEditor.scrollLeft;
    }

    // --- Editor Tools ---
    // ============================================
    // FORMATTING CONFIG & DEFINITIONS
    // ============================================
    const VOID_ELEMENTS = new Set([
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
        'link', 'meta', 'param', 'source', 'track', 'wbr'
    ]);

    const INLINE_TEXT_TAGS = new Set([
        'title', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'label', 'span', 'a', 'button', 'strong', 'em', 'b', 'i'
    ]);

    // ============================================
    // formatCss
    // ============================================
    function formatCss(css, baseIndentLevel = 0) {
        if (!css || typeof css !== 'string') return '';
        const indentSize = 2;
        const baseIndent = ' '.repeat(indentSize).repeat(Math.max(0, baseIndentLevel));
        const indent = ' '.repeat(indentSize);

        let formatted = css.replace(/\s+/g, ' ').trim();
        formatted = formatted.replace(/\s*{\s*/g, ' {\n');
        formatted = formatted.replace(/;\s*/g, ';\n');
        formatted = formatted.replace(/\s*}\s*/g, '\n}\n');

        const lines = formatted.split('\n');
        let output = [];
        let currentIndent = baseIndentLevel;

        for (let line of lines) {
            const trimmed = line.trim();
            if (!trimmed) continue;

            if (trimmed === '}') {
                currentIndent = Math.max(baseIndentLevel, currentIndent - 1);
            }

            const relativeIndent = Math.max(0, currentIndent - baseIndentLevel);
            output.push(baseIndent + indent.repeat(relativeIndent) + trimmed);

            if (trimmed.endsWith('{')) {
                currentIndent++;
            }
        }
        return output.join('\n');
    }

    // ============================================
    // formatJs
    // ============================================
    function formatJs(js, baseIndentLevel = 0) {
        if (!js || typeof js !== 'string') return '';
        const indentSize = 2;
        const baseIndent = ' '.repeat(indentSize).repeat(Math.max(0, baseIndentLevel));
        const indent = ' '.repeat(indentSize);

        let formatted = js.replace(/\s+/g, ' ').trim();

        // Protect strings
        const strings = [];
        formatted = formatted.replace(/(["'`])(?:\\.|(?!\1)[^\\])*?\1/g, (match) => {
            const id = strings.length; strings.push(match); return `___STR_${id}___`;
        });

        formatted = formatted.replace(/\s*{\s*/g, ' {\n');
        formatted = formatted.replace(/;\s*/g, ';\n');
        formatted = formatted.replace(/\s*}\s*/g, '\n}\n');

        // Restore strings
        strings.forEach((str, id) => {
            formatted = formatted.split(`___STR_${id}___`).join(str);
        });

        const lines = formatted.split('\n');
        let output = [];
        let currentIndent = baseIndentLevel;

        for (let line of lines) {
            const trimmed = line.trim();
            if (!trimmed) continue;

            if (trimmed.startsWith('}')) {
                currentIndent = Math.max(baseIndentLevel, currentIndent - 1);
            }

            const relativeIndent = Math.max(0, currentIndent - baseIndentLevel);
            output.push(baseIndent + indent.repeat(relativeIndent) + trimmed);

            if (trimmed.endsWith('{')) {
                currentIndent++;
            }
        }
        return output.join('\n');
    }

    // ============================================
    // UNIFIED HTML FORMATTER
    // ============================================
    function formatHtml(html) {
        if (!html || typeof html !== 'string') return '';

        const cfg = { indent_size: 2, indent_style: ' ', lowercase_tags: true, lowercase_attributes: true };
        const indent = cfg.indent_style.repeat(cfg.indent_size);

        // Normalize
        let normalizedHtml = html.split('\n').map(line => line.trim()).join('').replace(/>\s+</g, '><');

        const tokens = normalizedHtml.split(/(<[^>]+>)/g).filter(t => t.length > 0);
        let output = [];
        let currentIndent = 0;

        for (let i = 0; i < tokens.length; i++) {
            let token = tokens[i];
            const trimmed = token.trim();
            if (!trimmed) continue;

            const isTag = trimmed.startsWith('<');
            const isClosing = trimmed.startsWith('</');

            let tagName = '';
            if (isTag) {
                const match = trimmed.match(/^<\/?([\w-]+)/i);
                tagName = match ? match[1].toLowerCase() : '';
            }

            // --- Special Handling for Scripts & Styles ---
            if (tagName === 'script' || tagName === 'style') {
                if (isClosing) {
                    // This block is technically unreachable for valid HTML because we handle close tags inside the 'else' block below.
                    // But we keep it for safety.
                    currentIndent = Math.max(0, currentIndent - 1);
                    output.push(indent.repeat(currentIndent) + trimmed);
                } else {
                    // Opening Tag
                    output.push(indent.repeat(currentIndent) + trimmed);

                    const closeTag = `</${tagName}>`;
                    let content = '';
                    let endIndex = i + 1;

                    // Scan forward for closing tag
                    while (endIndex < tokens.length) {
                         if (tokens[endIndex].trim().toLowerCase() === closeTag) break;
                         content += tokens[endIndex];
                         endIndex++;
                    }

                    // Format Content
                    if (content.trim()) {
                        const formatter = tagName === 'script' ? formatJs : formatCss;
                        const formattedContent = formatter(content, currentIndent + 1);
                        output.push(formattedContent);
                    }

                    // Add Closing Tag
                    // Note: We do not increment indent here because the block is closed immediately
                    output.push(indent.repeat(currentIndent) + closeTag);

                    // Skip processed tokens
                    i = endIndex;
                }
                continue;
            }

            // --- Standard Tags ---
            if (trimmed.toLowerCase().startsWith('<!doctype')) {
                output.push('<!DOCTYPE html>');
                continue;
            }

            const isSelfClosing = trimmed.endsWith('/>') || VOID_ELEMENTS.has(tagName);

            if (isTag) {
                let formattedTag = trimmed;
                if (cfg.lowercase_tags) formattedTag = formattedTag.replace(/^<\/?[\w-]+/i, m => m.toLowerCase());
                if (cfg.lowercase_attributes) formattedTag = formattedTag.replace(/\s+[\w-]+(?=\s*[=/>])/g, m => m.toLowerCase());

                if (isClosing && !isSelfClosing) {
                    currentIndent = Math.max(0, currentIndent - 1);
                }

                const isInlineTextTag = INLINE_TEXT_TAGS.has(tagName);

                if (isClosing && isInlineTextTag) {
                    output[output.length - 1] += formattedTag;
                } else {
                    output.push(indent.repeat(currentIndent) + formattedTag);
                }

                if (isTag && !isClosing && !isSelfClosing) {
                    currentIndent++;
                }
            } else {
                // Text Content
                const text = trimmed;
                const prevToken = i > 0 ? tokens[i-1].trim() : '';
                const prevTagMatch = prevToken.match(/^<([\w-]+)/i);
                const isInsideInlineTag = prevTagMatch && INLINE_TEXT_TAGS.has(prevTagMatch[1].toLowerCase()) && !prevToken.startsWith('</');

                if (isInsideInlineTag) {
                    output[output.length - 1] += text;
                } else if (text.length < 40 && !prevToken.startsWith('</')) {
                     output[output.length - 1] += text;
                } else {
                    output.push(indent.repeat(currentIndent) + text);
                }
            }
        }

        return output.join('\n');
    }

    btnFormat.addEventListener('click', () => {
        codeEditor.value = formatHtml(codeEditor.value);
        pushHistory();
        triggerUpdate();
    });

    btnCopy.addEventListener('click', () => {
        navigator.clipboard.writeText(codeEditor.value).then(() => {
            statusText.textContent = 'Copied!';
            setTimeout(() => statusText.textContent = 'Ready', 1000);
        });
    });

    btnUndo.addEventListener('click', undo);
    btnRedo.addEventListener('click', redo);

    // Keyboard Shortcuts
    codeEditor.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
        e.preventDefault(); e.shiftKey ? redo() : undo(); return;
      }
      if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); redo(); return; }

      if ((e.ctrlKey || e.metaKey) && e.key === '/') {
        e.preventDefault();
        const start = codeEditor.selectionStart; const end = codeEditor.selectionEnd; const val = codeEditor.value;
        const firstLineStart = val.lastIndexOf('\n', start - 1) + 1;
        let lastLineEnd = val.indexOf('\n', end); if (lastLineEnd === -1) lastLineEnd = val.length;
        const block = val.substring(firstLineStart, lastLineEnd);
        const lines = block.split('\n');
        const allCommented = lines.every(line => line.trim().startsWith('<!--') || line.trim().endsWith('-->'));
        let newBlock;
        if (allCommented) newBlock = lines.map(line => line.replace('<!--', '').replace('-->', '')).join('\n');
        else newBlock = lines.map(line => { const ind = line.match(/^(\s*)/)[1]; return ind + '<!--' + line.substring(ind.length) + '-->'; }).join('\n');
        codeEditor.setRangeText(newBlock, firstLineStart, lastLineEnd, 'start');
        pushHistory(); triggerUpdate(); return;
      }

      const start = codeEditor.selectionStart; const end = codeEditor.selectionEnd; const val = codeEditor.value;
      if (e.key === 'Tab') {
        e.preventDefault(); const spaces = '    ';
        const firstLineStart = val.lastIndexOf('\n', start - 1) + 1; let lastLineEnd = val.indexOf('\n', end); if (lastLineEnd === -1) lastLineEnd = val.length;
        const block = val.substring(firstLineStart, lastLineEnd); const lines = block.split('\n'); let newBlock;
        if (e.shiftKey) newBlock = lines.map(line => line.replace(/^(\t| {1,4})/, '')).join('\n');
        else { if (start === end) { codeEditor.setRangeText(spaces, start, end, 'end'); pushHistory(); triggerUpdate(); return; } newBlock = lines.map(line => spaces + line).join('\n'); }
        codeEditor.setRangeText(newBlock, firstLineStart, lastLineEnd, 'start'); pushHistory(); triggerUpdate(); return;
      }

      if (e.key === 'Enter') {
        e.preventDefault();
        const lineStart = val.lastIndexOf('\n', start - 1) + 1;
        const currentIndent = (val.substring(lineStart, start).match(/^\s*/) || [''])[0];
        let insertText = '\n' + currentIndent;
        const charBefore = val.substring(start - 1, start);
        const charAfter = val.substring(end, end + 1);

        // 1. Handle Curly Braces { | }
        if (charBefore === '{' && charAfter === '}') {
          insertText = '\n' + currentIndent + '    \n' + currentIndent;
          codeEditor.setRangeText(insertText, start, end, 'end');
          codeEditor.selectionStart = codeEditor.selectionEnd = start + currentIndent.length + 5;
        }
        // 2. Handle Tags <tag>|</tag>  (New Logic)
        else if (charBefore === '>' && charAfter === '<' && val.substring(start, start + 2) === '</') {
          // Insert: Newline + Indent + 4 spaces (inner indent) + Newline + Indent (closing tag indent)
          insertText = '\n' + currentIndent + '    \n' + currentIndent;
          codeEditor.setRangeText(insertText, start, end, 'end');
          // Place cursor on the middle line (after the indentation)
          codeEditor.selectionStart = codeEditor.selectionEnd = start + currentIndent.length + 5;
        }
        // 3. Standard Enter
        else {
           if (charBefore === '{') insertText += '    ';
           codeEditor.setRangeText(insertText, start, end, 'end');
        }
        pushHistory(); triggerUpdate(); return;
      }

      const pairs = { '{': '}', '[': ']', '(': ')', '"': '"', "'": "'", '`': '`' };
      if (pairs[e.key]) {
        if (start !== end) { e.preventDefault(); const wrapped = e.key + val.substring(start, end) + pairs[e.key]; codeEditor.setRangeText(wrapped, start, end, 'end'); codeEditor.selectionStart = codeEditor.selectionEnd = start + 1; }
        else { const nextChar = val.substring(start, start + 1); if (nextChar === e.key) { e.preventDefault(); codeEditor.selectionStart = codeEditor.selectionEnd = start + 1; } else { e.preventDefault(); codeEditor.setRangeText(e.key + pairs[e.key], start, end, 'start'); codeEditor.selectionStart = codeEditor.selectionEnd = start + 1; } }
        pushHistory(); triggerUpdate(); return;
      }

      if (e.key === '>') {
        const textBefore = val.substring(0, start); const tagStart = textBefore.lastIndexOf('<');
        if (tagStart !== -1 && tagStart > textBefore.lastIndexOf('>')) {
          const tagContent = val.substring(tagStart + 1, start);
          const tagMatch = tagContent.match(/^\/?([a-zA-Z][a-zA-Z0-9-]*)/);
          if (tagMatch && !tagMatch[0].startsWith('/')) {
            const tagName = tagMatch[1].toLowerCase();
            if (!VOID_ELEMENTS.has(tagName)) { e.preventDefault(); codeEditor.setRangeText(`></${tagName}>`, start, end, 'end'); codeEditor.selectionStart = codeEditor.selectionEnd = start + 1; pushHistory(); triggerUpdate(); return; }
          }
        }
      }
    });

    codeEditor.addEventListener('input', () => { pushHistory(); triggerUpdate(); });
    codeEditor.addEventListener('scroll', syncScroll, { passive: true });

    function triggerUpdate() { updateCharCount(); scheduleHighlight(); schedulePreview(); scheduleSave(); }

    function scheduleHighlight() {
      if (highlightRAF) cancelAnimationFrame(highlightRAF);
      highlightRAF = requestAnimationFrame(() => { updateHighlight(); syncScroll(); highlightRAF = null; });
    }

    function schedulePreview() {
      const lines = (codeEditor.value.match(/\n/g) || []).length + 1;
      if (lines > 500) { if (autoRun) { autoRun = false; btnRun.classList.remove('active'); statusText.textContent = 'Manual Mode (>500 lines)'; } clearTimeout(previewDebounceTimer); return; }
      let delay = 0; if (lines > 300) delay = 200; else if (lines > 100) delay = 50;
      clearTimeout(previewDebounceTimer);
      if (autoRun) { if (delay === 0) updatePreview(); else previewDebounceTimer = setTimeout(updatePreview, delay); }
    }

    function scheduleSave() { clearTimeout(saveTimer); saveTimer = setTimeout(() => { localStorage.setItem('html-live-code', codeEditor.value); }, 500); }

    // --- App Logic ---
    const fallbackHTML =
      `<!DOCTYPE html>
      <html lang="en">
        <head>
          <meta charset="UTF-8">
          <title>Fallback HTML</title>
          <style>
            body {
              font-family: sans-serif;
              background: #1a1a2e;
              color: #eee;
              padding: 2rem;
            }
            h1 { color: #22d3ee; }
          </style>
        </head>
        <body>
          <h1>Fallback HTML</h1>
          <p>Drag the center bar to resize.</p>
        </body>
      </html>`;

    const defaultHTML = <?php
        $file = 'features_v3_starting.html';
        if (file_exists($file)) { echo json_encode(file_get_contents($file)); } else { echo 'fallbackHTML'; }
    ?>;

    function init() {
      const savedCode = localStorage.getItem('html-live-code');
      codeEditor.value = savedCode !== null ? savedCode : defaultHTML;

      history = [{ val: codeEditor.value, pos: 0 }]; historyIndex = 0;
      updateUndoRedoButtons();
      updateHighlight();

      // FIX: Explicitly default to 'vertical' to fix pane refresh issues
      // const savedLayout = localStorage.getItem('html-live-layout');
      const savedLayout = 'horizontal';

      // We treat anything other than 'horizontal' as vertical.
      // This ensures a clean slate if localStorage is empty or corrupted.
      if (savedLayout === 'horizontal') {
          mainContent.classList.add('horizontal');
          mainContent.classList.remove('vertical');
          isvertical = false;
          iconLayoutH.style.display = 'none';
          iconLayoutV.style.display = 'block';
      } else {
          // DEFAULT: vertical
          // Ensure we save this default so next refresh is consistent
          localStorage.setItem('html-live-layout', 'vertical');

          mainContent.classList.add('vertical');
          mainContent.classList.remove('horizontal');
          isvertical = true;
          iconLayoutH.style.display = 'block';
          iconLayoutV.style.display = 'none';
      }

      // FIX: Robust Style Reset
      // Always clear both dimensions first to prevent "stuck" sizes
      editorPanel.style.width = '';
      editorPanel.style.height = '';
      editorPanel.style.flex = 'none';

      // Now apply the specific dimension based on orientation
      if (isvertical) {
        editorPanel.style.width = '50%';
      } else {
        editorPanel.style.height = '50%'; //while these seem backwards, fixing it breaks it.  make that make sense.  somehow, it does.
      }

      updateCharCount();
      updatePreview();
      setupResizer();
    }

    // MERGED: Layout Toggle Function
    function setLayout(vertical) {
      isvertical = vertical;
      editorPanel.style.flex = 'none'; // Ensure flex is always set

      if (vertical) {
        mainContent.classList.remove('horizontal');
        mainContent.classList.add('vertical');
        iconLayoutH.style.display = 'block';
        iconLayoutV.style.display = 'none';

        // Convert Height -> Width (with safety clamp)
        let newWidth = editorPanel.offsetHeight;
        const maxWidth = mainContent.offsetWidth * 0.9;
        if (newWidth > maxWidth) newWidth = maxWidth;

        editorPanel.style.width = Math.max(200, newWidth) + 'px';
        editorPanel.style.height = ''; // Clear height

      } else {
        mainContent.classList.remove('vertical');
        mainContent.classList.add('horizontal');
        iconLayoutH.style.display = 'none';
        iconLayoutV.style.display = 'block';

        // Convert Width -> Height (with safety clamp)
        let newHeight = editorPanel.offsetWidth;
        const maxHeight = mainContent.offsetHeight * 0.9;
        if (newHeight > maxHeight) newHeight = maxHeight;

        editorPanel.style.height = Math.max(100, newHeight) + 'px';
        editorPanel.style.width = ''; // Clear width
      }

      localStorage.setItem('html-live-layout', vertical ? 'vertical' : 'horizontal');
    }

    function updatePreview() {
      const code = codeEditor.value;
      try {
        if (blobUrl) URL.revokeObjectURL(blobUrl);
        blobUrl = URL.createObjectURL(new Blob([code], { type: 'text/html' }));
        mainPreview.src = blobUrl;
        if (autoRun && codeEditor.value.split('\n').length <= 500) { statusText.textContent = 'Updated'; setTimeout(() => { statusText.textContent = 'Ready'; }, 1000); }
      } catch (e) { statusText.textContent = 'Error'; }
    }

    function updateCharCount() { charCount.textContent = `${codeEditor.value.length.toLocaleString()} chars`; }

    // MERGED: Enhanced Resizer
    function setupResizer() {
      resizer.addEventListener('mousedown', (e) => {
        let isResizing = true;
        let startX = 0, startY = 0, startSize = 0;

        if (isvertical) { startX = e.clientX; startSize = editorPanel.offsetWidth; }
        else { startY = e.clientY; startSize = editorPanel.offsetHeight; }

        resizer.classList.add('dragging');
        document.body.style.cursor = isvertical ? 'col-resize' : 'row-resize';
        document.body.style.userSelect = 'none';
        e.preventDefault();

        const onMouseMove = (e) => {
          if (!isResizing) return;
          if (isvertical) {
            const newWidth = Math.max(200, startSize + e.clientX - startX);
            editorPanel.style.width = `${newWidth}px`;
          } else {
            const newHeight = Math.max(100, startSize + e.clientY - startY);
            editorPanel.style.height = `${newHeight}px`;
          }
        };

        const onMouseUp = () => {
          isResizing = false;
          resizer.classList.remove('dragging');
          document.body.style.cursor = '';
          document.body.style.userSelect = '';
          document.removeEventListener('mousemove', onMouseMove);
          document.removeEventListener('mouseup', onMouseUp);
        };

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
      });
    }

    // --- Event Listeners ---
    btnRun.addEventListener('click', () => {
        autoRun = !autoRun; btnRun.classList.toggle('active', autoRun); localStorage.setItem('html-live-auto', autoRun);
        const lines = (codeEditor.value.match(/\n/g) || []).length + 1;
        if (autoRun) { if (lines > 500) statusText.textContent = 'Auto enabled (>500 lines)'; updatePreview(); }
    });

    btnRefresh.addEventListener('click', updatePreview);

    // MERGED: Layout Button Listener
    btnLayout.addEventListener('click', () => setLayout(!isvertical));

    btnClear.addEventListener('click', () => { if (confirm('Clear all code?')) { codeEditor.value = ''; pushHistory(); triggerUpdate(); } });
    btnReset.addEventListener('click', () => { if (confirm('Reset all settings and code to defaults?')) { localStorage.clear(); location.reload(); } });
    btnExport.addEventListener('click', () => { const a = document.createElement('a'); a.href = blobUrl || URL.createObjectURL(new Blob([codeEditor.value], { type: 'text/html' })); a.download = 'page.html'; a.click(); });
    btnNewTab.addEventListener('click', () => { if (blobUrl) window.open(blobUrl, '_blank'); });

    init();
  </script>
</body>
</html>
