<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    public function index()
    {
        return Auth::user()->skills;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $skill = Skill::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'level' => $request->level,
            'description' => $request->description,
        ]);

        return response()->json($skill, 201);
    }

    public function show(Skill $skill)
    {
        return $skill;
    }

    public function update(Request $request, Skill $skill)
    {
        $this->authorize('update', $skill);

        $skill->update($request->only(['name', 'level', 'description']));

        return $skill;
    }

    public function destroy(Skill $skill)
    {
        $this->authorize('delete', $skill);

        $skill->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
