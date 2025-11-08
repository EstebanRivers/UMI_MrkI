<?php

namespace App\Http\Controllers\Cursos;

use Illuminate\Http\Request;
use App\Models\Cursos\Subtopic;
use App\Models\Cursos\Topics;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Storage;

class SubtopicsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Topics $topic)
    {
        $course = $topic->course;
        return view('course.topic.create', [
            'topic' => $topic,
            'course' => $course,
            'type' => 'subtopic',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Topics $topic)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,pptx,mp4,mov,avi,wmv|max:51200',
            'order' => 'nullable|integer',
        ]);

        if ($request->hasFile('file_path')){
            $path = $request->file('file_path')->store('subtopic', 'public');

            $validatedData['file_path']=$path;
            unset($validatedData['file']);
        }

        $subtopic = $topic->subtopics()->create($validatedData);

        return back()->with('success', 'Subtema creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subtopic $subtopic)
    {
        $subtopic->delete();

        if ($subtopic->file_path) {
            Storage::disk('public')->delete($subtopic->file_path);
        }

        return back()->with('success', '¡Subtema eliminado exitosamente!');
    }
}
