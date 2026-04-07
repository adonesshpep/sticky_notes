<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sticky Wall</title>
    <style>
        :root {
            --wall-base: #efe1c3;
            --wall-shadow: #dbc39b;
            --wall-dark: #b89467;
            --panel: rgba(255, 247, 229, 0.94);
            --text-dark: #4c321d;
            --note-shadow: 0 18px 32px rgba(88, 58, 30, 0.22);
            --accent: #d4935e;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            position: relative;
            font-family: "Trebuchet MS", "Avenir Next", sans-serif;
            color: var(--text-dark);
            background:
                radial-gradient(1300px 760px at 8% 10%, rgba(255, 244, 196, 0.72) 0%, rgba(255, 244, 196, 0) 64%),
                radial-gradient(980px 620px at 78% 26%, rgba(250, 220, 162, 0.34) 0%, rgba(250, 220, 162, 0) 70%),
                radial-gradient(1450px 920px at 92% 95%, rgba(130, 90, 48, 0.16) 0%, rgba(130, 90, 48, 0) 66%),
                repeating-linear-gradient(25deg, rgba(255, 255, 255, 0.028) 0px, rgba(255, 255, 255, 0.028) 3px, rgba(108, 80, 46, 0.018) 3px, rgba(108, 80, 46, 0.018) 8px),
                linear-gradient(138deg, #f2e4c6 0%, #e7d2ac 45%, #d8bc8f 100%);
            background-blend-mode: normal, normal, normal, normal, normal;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(240px 180px at 22% 18%, rgba(173, 130, 78, 0.10) 0%, rgba(173, 130, 78, 0) 82%),
                radial-gradient(300px 220px at 72% 66%, rgba(154, 112, 68, 0.09) 0%, rgba(154, 112, 68, 0) 86%),
                radial-gradient(180px 140px at 38% 82%, rgba(126, 93, 56, 0.08) 0%, rgba(126, 93, 56, 0) 88%);
            mix-blend-mode: multiply;
            opacity: 0.45;
            z-index: 0;
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 50% 50%, rgba(0, 0, 0, 0) 58%, rgba(74, 50, 28, 0.16) 100%);
            z-index: 0;
        }

        #viewport,
        .hud,
        .status {
            z-index: 2;
        }

        .hud {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 999;
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
            max-width: calc(100vw - 2rem);
            background: rgba(255, 248, 235, 0.96);
            color: var(--text-dark);
            border-radius: 14px;
            padding: 0.75rem 0.85rem;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.14);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(212, 148, 94, 0.2);
        }

        .hud h1 {
            margin: 0;
            font-size: 1rem;
            letter-spacing: 0.03em;
            color: #5d3d1f;
        }

        .hud p {
            margin: 0;
            font-size: 0.8rem;
            opacity: 0.65;
            color: #7a5c3d;
        }

        .hud .meta {
            display: flex;
            flex-direction: column;
            margin-right: 0.4rem;
        }

        .hud button {
            border: 0;
            border-radius: 10px;
            padding: 0.6rem 0.85rem;
            background: #c9b299;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            transition: transform 120ms ease, background 120ms ease;
        }

        .hud button:hover {
            background: #b89a7d;
            transform: translateY(-1px);
        }

        .hud .accent {
            background: var(--accent);
            color: #fff;
        }

        .hud .accent:hover {
            background: #c27d48;
        }

        #viewport {
            position: relative;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            cursor: grab;
        }

        #viewport.panning {
            cursor: grabbing;
        }

        #world {
            position: absolute;
            width: 1px;
            height: 1px;
            transform-origin: 0 0;
        }

        #grid {
            position: absolute;
            inset: -60000px;
            background-image:
                linear-gradient(rgba(255, 242, 210, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 242, 210, 0.02) 1px, transparent 1px),
                linear-gradient(rgba(92, 68, 41, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(92, 68, 41, 0.03) 1px, transparent 1px);
            background-size: 40px 40px, 40px 40px, 200px 200px, 200px 200px;
            pointer-events: none;
        }

        .note {
            position: absolute;
            width: 220px;
            min-height: 210px;
            border-radius: 8px;
            box-shadow: var(--note-shadow), 0 2px 8px rgba(61, 40, 23, 0.12);
            transform-origin: top left;
            user-select: none;
            touch-action: none;
            animation: pop-in 170ms ease;
            border: 1px solid rgba(212, 196, 168, 0.18);
        }

        .note::before {
            content: "";
            position: absolute;
            width: 34px;
            height: 12px;
            background: rgba(212, 148, 94, 0.35);
            top: 8px;
            left: calc(50% - 17px);
            border-radius: 2px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
        }

        .note textarea {
            margin-top: 24px;
            width: 100%;
            min-height: 186px;
            border: 0;
            outline: 0;
            resize: vertical;
            background: transparent;
            font: 700 1.02rem/1.35 "Comic Sans MS", "Marker Felt", "Bradley Hand", cursive;
            color: #3d2817;
            padding: 0.8rem;
        }

        .note textarea:read-only {
            cursor: default;
            opacity: 0.85;
        }

        .note .trash {
            position: absolute;
            top: 8px;
            right: 8px;
            border: 0;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: rgba(212, 148, 94, 0.3);
            color: #5d3d1f;
            font-size: 0.8rem;
            cursor: pointer;
            opacity: 0.72;
            transition: opacity 100ms ease;
        }

        .note .trash:hover:not(:disabled) {
            opacity: 1;
            background: rgba(212, 148, 94, 0.5);
        }

        .note .trash:disabled {
            opacity: 0;
            pointer-events: none;
        }

        .note .pin {
            position: absolute;
            top: 8px;
            left: 8px;
            border: 0;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: rgba(212, 148, 94, 0.3);
            color: #5d3d1f;
            font-size: 0.75rem;
            cursor: pointer;
            opacity: 0.72;
            transition: opacity 100ms ease, background 100ms ease, visibility 100ms ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .note .pin:hover:not(:disabled) {
            opacity: 1;
            background: rgba(212, 148, 94, 0.5);
        }

        .note .pin:disabled {
            opacity: 0;
            pointer-events: none;
            visibility: hidden;
        }

        .note.pinned .pin {
            opacity: 1;
            background: rgba(212, 148, 94, 0.6);
        }

        .status {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            z-index: 999;
            background: rgba(212, 148, 94, 0.2);
            border: 1px solid rgba(212, 148, 94, 0.4);
            border-radius: 10px;
            padding: 0.45rem 0.65rem;
            font-size: 0.74rem;
            letter-spacing: 0.03em;
            opacity: 0.88;
            color: #5d3d1f;
        }

        @keyframes pop-in {
            from {
                transform: translateY(14px) scale(0.92);
                opacity: 0;
            }

            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        @media (max-width: 760px) {
            .hud {
                gap: 0.55rem;
                padding: 0.65rem 0.7rem;
            }

            .hud h1 {
                font-size: 0.9rem;
            }

            .hud p {
                font-size: 0.72rem;
            }

            .hud button {
                padding: 0.55rem 0.7rem;
                font-size: 0.8rem;
            }

            .note {
                width: 190px;
                min-height: 180px;
            }

            .note textarea {
                min-height: 160px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
<div class="hud">
    <div class="meta">
        <h1>Infinite Sticky Wall</h1>
        <p>Drag canvas to pan, wheel to zoom, drag notes to move.</p>
    </div>
    <button id="new-note" class="accent">New note</button>
    <button id="center-wall">Center view</button>
</div>

<div id="viewport" aria-label="Sticky notes wall">
    <div id="world">
        <div id="grid"></div>
    </div>
</div>

<div class="status" id="status">Saved</div>

<script>
    const initialNotes = @json($notes);
    const viewport = document.getElementById('viewport');
    const world = document.getElementById('world');
    const statusNode = document.getElementById('status');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const notePalette = ['#fffef9', '#fffcf0', '#fffbeb', '#fffadf', '#fff8ce', '#fff6b8'];

    const camera = {
        x: window.innerWidth / 2,
        y: window.innerHeight / 2,
        scale: 1,
    };

    const noteEls = new Map();
    let activePan = null;
    let topZ = initialNotes.reduce((max, note) => Math.max(max, Number(note.z_index) || 0), 1);

    function setStatus(text) {
        statusNode.textContent = text;
    }

    function applyCamera() {
        world.style.transform = `translate(${camera.x}px, ${camera.y}px) scale(${camera.scale})`;
    }

    function worldPointFromClient(clientX, clientY) {
        return {
            x: Math.round((clientX - camera.x) / camera.scale),
            y: Math.round((clientY - camera.y) / camera.scale),
        };
    }

    function toPayload(note) {
        return {
            content: note.content,
            x: Math.round(note.x),
            y: Math.round(note.y),
            color: note.color,
            rotation: Number(note.rotation || 0),
            z_index: Number(note.z_index || 0),
            pinned: Boolean(note.pinned || false),
        };
    }

    async function request(url, method, body) {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: body ? JSON.stringify(body) : undefined,
        });

        if (!response.ok) {
            throw new Error(`Request failed with ${response.status}`);
        }

        if (response.status === 204) {
            return null;
        }

        return response.json();
    }

    function setNoteTransform(el, note) {
        el.style.left = `${note.x}px`;
        el.style.top = `${note.y}px`;
        el.style.zIndex = String(note.z_index || 1);
        el.style.transform = `rotate(${note.rotation || 0}deg)`;
        el.style.background = note.color;
    }

    function markDirty(el) {
        clearTimeout(el._saveTimer);
        el._saveTimer = setTimeout(async () => {
            try {
                setStatus('Saving...');
                const note = el._note;
                await request(`/notes/${note.id}`, 'PATCH', toPayload(note));
                setStatus('Saved');
            } catch (error) {
                console.error(error);
                setStatus('Save failed');
            }
        }, 260);
    }

    function focusNote(el) {
        topZ += 1;
        el._note.z_index = topZ;
        el.style.zIndex = String(topZ);
        markDirty(el);
    }

    function mountNote(note) {
        const el = document.createElement('div');
        el.className = 'note';
        el.dataset.id = String(note.id);
        el._note = {
            id: note.id,
            content: note.content || '',
            x: Number(note.x) || 0,
            y: Number(note.y) || 0,
            color: note.color || notePalette[0],
            rotation: Number(note.rotation) || 0,
            z_index: Number(note.z_index) || 1,
            pinned: Boolean(note.pinned) || false,
        };

        if (el._note.pinned) {
            el.classList.add('pinned');
        }

        const text = document.createElement('textarea');
        text.value = el._note.content;
        text.readOnly = true;

        const trash = document.createElement('button');
        trash.className = 'trash';
        trash.type = 'button';
        trash.title = 'Delete note';
        trash.textContent = 'x';
        trash.disabled = true;

        const pin = document.createElement('button');
        pin.className = 'pin';
        pin.type = 'button';
        pin.title = 'Pin this note';
        pin.textContent = '📌';
        
        if (el._note.pinned) {
            pin.disabled = true;
            pin.style.visibility = 'hidden';
        }

        text.addEventListener('focus', (event) => {
            event.stopPropagation();
            focusNote(el);
            if (!el._note.pinned) {
                text.readOnly = false;
                trash.disabled = false;
            }
        });

        text.addEventListener('blur', () => {
            text.readOnly = true;
            trash.disabled = true;
        });

        text.addEventListener('pointerdown', (event) => {
            event.stopPropagation();
        });

        text.addEventListener('input', () => {
            el._note.content = text.value;
            markDirty(el);
        });

        pin.addEventListener('click', async (event) => {
            event.stopPropagation();
            try {
                el._note.pinned = true;
                el.classList.add('pinned');
                pin.disabled = true;
                pin.style.visibility = 'hidden';
                trash.disabled = true;
                text.readOnly = true;
                await request(`/notes/${el._note.id}`, 'PATCH', toPayload(el._note));
                setStatus('Note pinned');
            } catch (error) {
                console.error(error);
                setStatus('Pin failed');
                pin.disabled = false;
                pin.style.visibility = 'visible';
            }
        });

        trash.addEventListener('click', async (event) => {
            event.stopPropagation();
            try {
                await request(`/notes/${el._note.id}`, 'DELETE');
                noteEls.delete(el._note.id);
                el.remove();
                setStatus('Deleted');
            } catch (error) {
                console.error(error);
                setStatus('Delete failed');
            }
        });

        el.addEventListener('pointerdown', (event) => {
            if (event.target === text || el._note.pinned) {
                return;
            }

            event.preventDefault();
            focusNote(el);

            const note = el._note;
            const start = worldPointFromClient(event.clientX, event.clientY);
            const originX = note.x;
            const originY = note.y;

            function onMove(moveEvent) {
                const current = worldPointFromClient(moveEvent.clientX, moveEvent.clientY);
                note.x = originX + (current.x - start.x);
                note.y = originY + (current.y - start.y);
                setNoteTransform(el, note);
            }

            function onUp() {
                window.removeEventListener('pointermove', onMove);
                window.removeEventListener('pointerup', onUp);
                markDirty(el);
            }

            window.addEventListener('pointermove', onMove);
            window.addEventListener('pointerup', onUp);
        });

        el.appendChild(pin);
        el.appendChild(trash);
        el.appendChild(text);

        setNoteTransform(el, el._note);
        world.appendChild(el);
        noteEls.set(el._note.id, el);
    }

    async function createNoteAtCenter() {
        const center = worldPointFromClient(window.innerWidth / 2, window.innerHeight / 2);
        topZ += 1;

        const draft = {
            content: '',
            x: center.x - 100,
            y: center.y - 100,
            color: notePalette[Math.floor(Math.random() * notePalette.length)],
            rotation: Math.round((Math.random() * 12 - 6) * 10) / 10,
            z_index: topZ,
            pinned: false,
        };

        try {
            setStatus('Saving...');
            const persisted = await request('/notes', 'POST', draft);
            mountNote(persisted);
            const created = noteEls.get(persisted.id);
            if (created) {
                const textarea = created.querySelector('textarea');
                textarea.focus();
            }
            setStatus('Saved');
        } catch (error) {
            console.error(error);
            setStatus('Create failed');
        }
    }

    viewport.addEventListener('pointerdown', (event) => {
        if (event.target !== viewport && event.target !== world && event.target.id !== 'grid') {
            return;
        }

        activePan = {
            startClientX: event.clientX,
            startClientY: event.clientY,
            startX: camera.x,
            startY: camera.y,
        };

        viewport.classList.add('panning');
    });

    window.addEventListener('pointermove', (event) => {
        if (!activePan) {
            return;
        }

        camera.x = activePan.startX + (event.clientX - activePan.startClientX);
        camera.y = activePan.startY + (event.clientY - activePan.startClientY);
        applyCamera();
    });

    window.addEventListener('pointerup', () => {
        activePan = null;
        viewport.classList.remove('panning');
    });

    viewport.addEventListener('wheel', (event) => {
        event.preventDefault();

        const zoomFactor = event.deltaY > 0 ? 0.92 : 1.08;
        const newScale = Math.min(2.2, Math.max(0.35, camera.scale * zoomFactor));
        const before = worldPointFromClient(event.clientX, event.clientY);

        camera.scale = newScale;

        camera.x = event.clientX - before.x * camera.scale;
        camera.y = event.clientY - before.y * camera.scale;
        applyCamera();
    }, { passive: false });

    document.getElementById('new-note').addEventListener('click', createNoteAtCenter);

    document.getElementById('center-wall').addEventListener('click', () => {
        camera.x = window.innerWidth / 2;
        camera.y = window.innerHeight / 2;
        camera.scale = 1;
        applyCamera();
    });

    window.addEventListener('resize', () => {
        applyCamera();
    });

    for (const note of initialNotes) {
        mountNote(note);
    }

    applyCamera();
</script>
</body>
</html>
