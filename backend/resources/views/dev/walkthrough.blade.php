<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Sprint 4 Walkthrough</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=source-serif-4:400,400i,600,700&family=jetbrains-mono:400,500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #fafaf9;
            --text: #1c1917;
            --text-secondary: #78716c;
            --text-tertiary: #a8a29e;
            --border: #e7e5e4;
            --surface: #ffffff;
            --surface-code: #f5f5f4;
            --accent: #059669;
            --accent-light: #d1fae5;
            --font-body: 'Source Serif 4', Georgia, serif;
            --font-mono: 'JetBrains Mono', ui-monospace, monospace;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0c0a09;
                --text: #e7e5e4;
                --text-secondary: #a8a29e;
                --text-tertiary: #57534e;
                --border: #292524;
                --surface: #1c1917;
                --surface-code: #1c1917;
                --accent: #34d399;
                --accent-light: #064e3b;
            }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html {
            font-size: 18px;
            -webkit-text-size-adjust: 100%;
            /* overflow-x: hidden; */
        }

        body {
            font-family: var(--font-body);
            background: var(--bg);
            color: var(--text);
            line-height: 1.7;
            padding: 0 0 env(safe-area-inset-bottom) 0;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            overscroll-behavior: none;
            -webkit-overflow-scrolling: touch;
        }

        .container {
            max-width: 680px;
            margin: 0 auto;
            padding: 2rem 1.25rem 6rem;
            overflow-x: hidden;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Headings */
        h1 {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.02em;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
            color: var(--text);
        }

        h1 + hr { margin-top: 0.75rem; }

        h2 {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: -0.01em;
            margin-top: 2.5rem;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        h3 {
            font-size: 1.15rem;
            font-weight: 600;
            line-height: 1.4;
            margin-top: 2rem;
            margin-bottom: 0.5rem;
            color: var(--text);
        }

        /* Paragraphs */
        p {
            margin-bottom: 1.1rem;
            color: var(--text);
        }

        /* Links (just in case) */
        a { color: var(--accent); text-decoration: none; }

        /* Bold / Italic */
        strong { font-weight: 600; }

        /* Inline code */
        code:not(pre code) {
            font-family: var(--font-mono);
            font-size: 0.82em;
            font-weight: 500;
            background: var(--surface-code);
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 0.15em 0.4em;
            color: var(--accent);
        }

        /* Code blocks */
        pre {
            background: var(--surface-code);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            max-width: 100%;
        }

        pre code {
            font-family: var(--font-mono);
            font-size: 0.72rem;
            line-height: 1.65;
            color: var(--text);
            background: none;
            border: none;
            padding: 0;
            white-space: pre;
        }

        /* Blockquotes */
        blockquote {
            border-left: 3px solid var(--accent);
            padding: 0.25rem 0 0.25rem 1.25rem;
            margin-bottom: 1.25rem;
            color: var(--text-secondary);
            font-style: italic;
        }

        blockquote p { margin-bottom: 0; color: inherit; }

        /* Horizontal rules */
        hr {
            border: none;
            height: 1px;
            background: var(--border);
            margin: 2rem 0;
        }

        /* Lists */
        ul, ol {
            padding-left: 1.5rem;
            margin-bottom: 1.1rem;
        }

        li {
            margin-bottom: 0.35rem;
            color: var(--text);
        }

        li::marker {
            color: var(--text-tertiary);
        }

        /* Tables */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1.25rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            max-width: 100%;
        }

        table {
            min-width: 100%;
            border-collapse: collapse;
            font-size: 0.78rem;
            line-height: 1.5;
        }

        thead {
            background: var(--surface-code);
        }

        th {
            font-weight: 600;
            text-align: left;
            padding: 0.5rem 0.65rem;
            color: var(--text-secondary);
            white-space: nowrap;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 0.45rem 0.65rem;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        tr:last-child td { border-bottom: none; }

        /* Images/media don't overflow */
        img, video, svg { max-width: 100%; height: auto; }

        /* Emphasis for the closing italic line */
        .article > p:last-child em {
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="container">
        <article class="article">
            {!! $html !!}
        </article>
    </div>
</body>
</html>
