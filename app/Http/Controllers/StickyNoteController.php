<?php

namespace App\Http\Controllers;

use App\Models\StickyNote;
use Illuminate\Http\Request;

class StickyNoteController extends Controller
{
    public function index()
    {
        $notes = StickyNote::query()
            ->orderBy('z_index')
            ->orderBy('id')
            ->get(['id', 'content', 'x', 'y', 'color', 'rotation', 'z_index', 'pinned']);

        return view('wall', [
            'notes' => $notes,
        ]);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'content' => ['nullable', 'string', 'max:2000'],
            'x' => ['required', 'integer', 'min:-500000', 'max:500000'],
            'y' => ['required', 'integer', 'min:-500000', 'max:500000'],
            'color' => ['required', 'string', 'max:24'],
            'rotation' => ['nullable', 'numeric', 'min:-30', 'max:30'],
            'z_index' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'pinned' => ['nullable', 'boolean'],
        ]);

        $note = StickyNote::query()->create([
            'content' => $payload['content'] ?? '',
            'x' => $payload['x'],
            'y' => $payload['y'],
            'color' => $payload['color'],
            'rotation' => $payload['rotation'] ?? 0,
            'z_index' => $payload['z_index'] ?? 0,
            'pinned' => $payload['pinned'] ?? false,
        ]);

        return response()->json($note, 201);
    }

    public function update(Request $request, StickyNote $stickyNote)
    {
        $payload = $request->validate([
            'content' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'x' => ['sometimes', 'integer', 'min:-500000', 'max:500000'],
            'y' => ['sometimes', 'integer', 'min:-500000', 'max:500000'],
            'color' => ['sometimes', 'string', 'max:24'],
            'rotation' => ['sometimes', 'numeric', 'min:-30', 'max:30'],
            'z_index' => ['sometimes', 'integer', 'min:0', 'max:10000000'],
            'pinned' => ['sometimes', 'boolean'],
        ]);

        $stickyNote->fill($payload);
        $stickyNote->save();

        return response()->json($stickyNote);
    }

    public function destroy(StickyNote $stickyNote)
    {
        $stickyNote->delete();

        return response()->noContent();
    }
}
